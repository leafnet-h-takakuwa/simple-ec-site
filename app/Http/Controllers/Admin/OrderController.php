<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\CoolType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\FaxOrderFormRow;
use App\Support\ContactRules;
use App\Support\InputLimits;
use App\Services\TaxCalculator;
use App\Services\CustomerService;
use App\Services\DeliverySlipPdf;
use App\Services\OrderMailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Enums\DeliveryTimeSlot;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(
        private TaxCalculator $taxCalculator,
        private CustomerService $customerService,
        private OrderMailService $orderMailService
    ) {}

    public function index(Request $request)
    {
        // 検索条件を取得・検証
        $conditions = $request->validate([
            'order_id' => ['nullable', 'integer', 'min:1'],
            'orderer_name' => ['nullable', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    ...array_column(OrderStatus::cases(), 'value'),
                ]),
            ],
        ]);

        $query = Order::query();

        // 注文番号（完全一致）
        if (!empty($conditions['order_id'])) {
            $query->where('id', $conditions['order_id']);
        }

        // 注文者氏名（部分一致）
        if (!empty($conditions['orderer_name'])) {
            $query->where(
                'orderer_name',
                'ilike',
                '%' . $conditions['orderer_name'] . '%'
            );
        }

        // 注文日（開始日を含む）
        if (!empty($conditions['date_from'])) {
            $query->whereDate('created_at', '>=', $conditions['date_from']);
        }

        // 注文日（終了日を含む）
        if (!empty($conditions['date_to'])) {
            $query->whereDate('created_at', '<=', $conditions['date_to']);
        }

        // ステータス
        if (!empty($conditions['status'])) {
            if ($conditions['status'] === 'active') {
                $query->whereIn('status', OrderStatus::activeValues());
            } else {
                $query->where('status', $conditions['status']);
            }
        }

        // 新しい順に設定件数ずつ取得
        $orders = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(config('shop.pagination.admin_orders'))
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'conditions' => $conditions,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show($id)
    {
        $order = Order::with([
            'orderItems',
            'orderShippings',
            'taxSummaries',
            'statusHistories' => fn($query) => $query->latest('id'),
            'statusHistories.user',
            'statusHistories.mailHistories',
        ])->findOrFail($id);

        $nextStatus = $order->status->next($order->payment_method);

        // 通常のステータス更新用
        $canSendEmail = $nextStatus !== null
            && $this->orderMailService->canSendForStatus($order, $nextStatus);

        // キャンセル用
        $canSendCancelEmail = $this->orderMailService->canSendForStatus(
            $order,
            OrderStatus::CANCELLED
        );

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => OrderStatus::cases(),
            'canSendEmail' => $canSendEmail,
            'canSendCancelEmail' => $canSendCancelEmail,
        ]);
    }

    public function create()
    {
        $products = Product::orderBy('name')
            ->get(['id', 'name', 'volume', 'price', 'tax_rate']);

        // 顧客台帳から選択するためのデータ
        $customers = Customer::orderBy('name')
            ->get([
                'id',
                'name',
                'postal_code',
                'address',
                'phone',
                'email',
                'memo',
                'is_orderer',
                'is_recipient',
            ]);

        // FAX注文書に掲載されている商品ID
        $faxProductIds = FaxOrderFormRow::query()
            ->where('type', 'product')
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->all();

        return view('admin.orders.create', [
            'products' => $products,
            'customers' => $customers,
            'faxProductIds' => $faxProductIds,
            'orderSources' => OrderSource::cases(),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function store(Request $request)
    {
        // 入力値のバリデーション
        $validated = $request->validate([
            'order_source' => ['required', Rule::enum(OrderSource::class)],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],

            ...ContactRules::forPrefix('orderer_'),
            'orderer_email' => ['nullable', 'email', 'max:' . InputLimits::SHORT_TEXT],

            ...ContactRules::forPrefix('shipping_'),

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'items.*.volume' => ['nullable', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'items.*.price' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.tax_rate' => [
                'required',
                'integer',
                Rule::in(array_keys(config('shop.tax.rates'))),
            ],
            'memo' => ['nullable', 'string'],
        ]);

        // 注文明細の小計・合計金額を計算
        $items = collect($validated['items'])->map(function ($item) {
            $item['subtotal'] = $item['price'] * $item['quantity'];

            return $item;
        });

        $totalAmount = $items->sum('subtotal');

        // 合計金額がマイナスになる注文は登録不可
        if ($totalAmount < 0) {
            throw ValidationException::withMessages([
                'items' => '合計金額がマイナスになる注文は登録できません。',
            ]);
        }

        // 注文と注文明細をまとめて登録
        [$order, $statusHistory] = DB::transaction(function () use ($validated, $items, $totalAmount) {

            $order = Order::create([
                'user_id' => null,
                'order_source' => $validated['order_source'],

                // 新規登録時は「受付」固定
                'status' => OrderStatus::RECEIVED,

                'total_amount' => $totalAmount,

                'orderer_name' => $validated['orderer_name'],
                'orderer_postal_code' => $validated['orderer_postal_code'],
                'orderer_address' => $validated['orderer_address'],
                'orderer_phone' => $validated['orderer_phone'],
                'orderer_email' => $validated['orderer_email'] ?? null,

                'shipping_name' => $validated['shipping_name'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_phone' => $validated['shipping_phone'],

                'payment_method' => $validated['payment_method'],

                'memo' => $validated['memo'] ?? null,
            ]);

            // 配送個口を初期登録
            $order->orderShippings()->create([
                'tracking_number' => null,
                'cool_type' => null,
                'item_name' => null,
            ]);

            $taxItems = [];

            foreach ($items as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'volume' => $item['volume'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'tax_rate' => $item['tax_rate'],
                ]);

                $taxItems[] = [
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $item['subtotal'],
                ];
            }

            // 税率別集計を保存
            try {
                $taxSummaries = $this->taxCalculator->calculate(
                    $taxItems,
                    shippingFee: $order->shipping_fee ?? 0,
                    codFee: $order->cod_fee ?? 0,
                );
            } catch (InvalidArgumentException $e) {
                throw ValidationException::withMessages([
                    'items' => $e->getMessage(),
                ]);
            }

            $order->taxSummaries()->createMany($taxSummaries);

            // 注文受付履歴を登録
            $statusHistory = $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => OrderStatus::RECEIVED->value,
                'contact_method' => 'none',
                'contact_note' => null,
                'user_id' => Auth::user()->id,
            ]);

            // 顧客台帳に反映（重複時は利用区分のみ更新）
            $this->customerService->registerFromOrder($order);

            return [$order, $statusHistory];
        });

        // 注文受付メールを送信
        try {
            $this->orderMailService->sendReceived($order, $statusHistory);
        } catch (\Throwable $e) {
            Log::error('注文受付メールの処理でエラーが発生しました。', [
                'order_id' => $order->id,
                'exception' => $e,
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', '注文を登録しました。');
    }

    public function editItems($id)
    {
        // 注文情報と注文明細を取得
        $order = Order::with('orderItems')->findOrFail($id);

        // FAX注文書に掲載されている商品ID
        $faxProductIds = FaxOrderFormRow::query()
            ->where('type', 'product')
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->all();

        if (!$order->isEditable()) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->withErrors([
                    'error' => '発送済み・キャンセル済みの注文は編集できません。',
                ]);
        }

        // 商品情報取得
        $products = Product::orderBy('name')
            ->get(['id', 'name', 'volume', 'price', 'tax_rate']);

        return view('admin.orders.edit-items', [
            'order' => $order,
            'products' => $products,
            'faxProductIds' => $faxProductIds,
        ]);
    }

    public function updateItems(Request $request, $id)
    {
        // 入力値の検証
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'distinct'],
            'items.*.product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'items.*.product_name' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'items.*.volume' => ['nullable', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'items.*.price' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.tax_rate' => [
                'required',
                'integer',
                Rule::in(array_keys(config('shop.tax.rates'))),
            ],
        ]);

        // 注文情報を取得
        $order = Order::findOrFail($id);

        DB::transaction(function () use ($order, $validated) {
            // 同じ注文への同時更新を防ぐ
            $order = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 編集可否を確認
            if (!$order->isEditable()) {
                throw ValidationException::withMessages([
                    'error' => '発送済み・キャンセル済みの注文は編集できません。',
                ]);
            }

            // 現在の注文明細を取得
            $existingItems = $order->orderItems()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // フォームから送信された既存明細ID
            $submittedIds = collect($validated['items'])
                ->pluck('id')
                ->filter(fn($id) => $id !== null)
                ->map(fn($id) => (int) $id);

            // 別の注文の明細IDが含まれていないか確認
            if ($submittedIds->diff($existingItems->keys())->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => '更新対象の注文明細が正しくありません。',
                ]);
            }

            $totalAmount = 0;
            $taxItems = [];

            foreach ($validated['items'] as $item) {
                // 小計をサーバー側で計算
                $subtotal = $item['price'] * $item['quantity'];

                // 合計金額を加算
                $totalAmount += $subtotal;

                $data = [
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'volume' => $item['volume'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'tax_rate' => $item['tax_rate'],
                ];

                $taxItems[] = [
                    'tax_rate' => $item['tax_rate'],
                    'subtotal' => $subtotal,
                ];

                if (!empty($item['id'])) {
                    // 既存明細を更新
                    $existingItems->get((int) $item['id'])->update($data);
                } else {
                    // 新規明細を追加
                    $order->orderItems()->create($data);
                }
            }

            if ($totalAmount < 0) {
                throw ValidationException::withMessages([
                    'items' => '注文合計金額がマイナスになるような変更はできません。',
                ]);
            }

            // 画面から削除された既存明細を削除
            $deleteIds = $existingItems->keys()->diff($submittedIds);

            if ($deleteIds->isNotEmpty()) {
                $order->orderItems()
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }

            // 注文合計を更新
            $order->total_amount = $totalAmount;
            $order->save();

            // 更新後の注文明細と送料・手数料から税額を再計算
            try {
                $taxSummaries = $this->taxCalculator->calculate(
                    $taxItems,
                    shippingFee: $order->shipping_fee ?? 0,
                    codFee: $order->cod_fee ?? 0,
                );
            } catch (InvalidArgumentException $e) {
                throw ValidationException::withMessages([
                    'items' => $e->getMessage(),
                ]);
            }

            // 古い税率別集計を削除して再作成
            $order->taxSummaries()->delete();
            $order->taxSummaries()->createMany($taxSummaries);
        });


        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', '注文明細を更新しました。');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'contact_method' => [
                'required',
                Rule::in(['email', 'phone', 'fax', 'in_person', 'none']),
            ],
            'contact_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = OrderStatus::from($validated['status']);

        // 発送済み・キャンセル済みは変更不可
        if (in_array($oldStatus, [
            OrderStatus::SHIPPED,
            OrderStatus::CANCELLED,
        ], true)) {
            return back()->withErrors([
                'error' => '発送済み・キャンセル済みの注文は変更できません。',
            ])->withInput();
        }

        // 次のステータスを取得
        $nextStatus = $oldStatus->next($order->payment_method);

        // キャンセル、または次のステータスへの変更のみ許可
        if (
            $newStatus !== OrderStatus::CANCELLED
            && $newStatus !== $nextStatus
        ) {
            return back()->withErrors([
                'error' => '指定されたステータスには変更できません。',
            ])->withInput();
        }

        // 受付から先へ進む場合は金額の確定が必要
        if (
            $oldStatus === OrderStatus::RECEIVED
            && $newStatus !== OrderStatus::CANCELLED
        ) {
            if (
                $order->shipping_fee === null
                || (
                    $order->payment_method === PaymentMethod::CASH_ON_DELIVERY
                    && $order->cod_fee === null
                )
            ) {
                return back()->withErrors([
                    'error' => '送料・手数料を確定してからステータスを変更してください。',
                ])->withInput();
            }
        }

        // 発送時はすべての送り状番号が必要
        if ($newStatus === OrderStatus::SHIPPED) {
            $shippings = $order->orderShippings;

            if (
                $shippings->isEmpty()
                || $shippings->contains(fn($shipping) => blank($shipping->tracking_number))
            ) {
                return back()->withErrors([
                    'error' => 'すべての個口に送り状番号を登録してから発送済みに変更してください。',
                ])->withInput();
            }
        }

        // メール送信可能か確認
        if (
            $validated['contact_method'] === 'email'
            && !$this->orderMailService->canSendForStatus($order, $newStatus)
        ) {
            throw ValidationException::withMessages([
                'contact_method' => 'このステータス変更ではメールを送信できません。',
            ]);
        }

        // ステータス更新と履歴登録
        $statusHistory = DB::transaction(function () use ($order, $oldStatus, $newStatus, $validated, $request) {
            // ステータスを更新
            $order->status = $newStatus;
            $order->save();

            // ステータス変更履歴を登録
            return $order->statusHistories()->create([
                'from_status' => $oldStatus->value,
                'to_status' => $newStatus->value,
                'contact_method' => $validated['contact_method'],
                'contact_note' => $validated['contact_note'] ?? null,
                'user_id' => $request->user()->id,
            ]);
        });

        // メール送信が選択されている場合のみ実行
        if ($validated['contact_method'] === 'email') {
            try {
                $this->orderMailService->sendForStatus(
                    $order,
                    $oldStatus,
                    $newStatus,
                    $statusHistory
                );
            } catch (\Throwable $e) {
                Log::error('注文メールの処理でエラーが発生しました。', [
                    'order_id' => $order->id,
                    'exception' => $e,
                ]);
            }
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', '注文ステータスを更新しました。');
    }

    public function updateMemo(Request $request, Order $order)
    {
        $validated = $request->validate([
            'memo' => ['nullable', 'string'],
        ]);

        $order->update([
            'memo' => $validated['memo'] ?? null,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function editCustomer(Order $order)
    {
        if (!$order->isEditable()) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->withErrors([
                    'error' => '発送済み・キャンセル済みの注文は編集できません。',
                ]);
        }

        return view('admin.orders.edit-customer', [
            'order' => $order,
        ]);
    }

    public function updateCustomer(Request $request, Order $order)
    {
        if (!$order->isEditable()) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->withErrors([
                    'error' => '発送済み・キャンセル済みの注文は編集できません。',
                ]);
        }

        $validated = $request->validate([
            ...ContactRules::forPrefix('orderer_'),
            'orderer_email' => ['nullable', 'email', 'max:' . InputLimits::SHORT_TEXT],

            ...ContactRules::forPrefix('shipping_'),
        ]);

        $order->update($validated);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'お客様情報を更新しました。');
    }

    public function updatePayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status !== OrderStatus::RECEIVED) {
                throw ValidationException::withMessages([
                    'error' => '支払い方法は受付中の注文のみ変更できます。',
                ]);
            }

            $newPaymentMethod = PaymentMethod::from($validated['payment_method']);

            if ($order->payment_method !== $newPaymentMethod) {
                $order->cod_fee = $newPaymentMethod === PaymentMethod::CASH_ON_DELIVERY
                    ? null
                    : 0;
            }

            $order->payment_method = $newPaymentMethod;
            $order->save();

            $this->refreshTaxSummaries($order);
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'お支払い方法を変更しました。');
    }

    public function updateFees(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping_fee' => ['nullable', 'integer', 'min:0'],
            'cod_fee' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$order->isEditable()) {
                throw ValidationException::withMessages([
                    'error' => '発送済み・キャンセル済みの注文は編集できません。',
                ]);
            }

            if ($order->payment_method !== PaymentMethod::CASH_ON_DELIVERY) {
                $validated['cod_fee'] = 0;
            }

            $order->update($validated);

            $this->refreshTaxSummaries($order);
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', '送料・代引手数料を更新しました。');
    }
    public function updateShippings(Request $request, Order $order)
    {
        $validated = $request->validate([
            'delivery_date' => ['nullable', 'date'],
            'delivery_time_slot' => ['nullable', Rule::enum(DeliveryTimeSlot::class)],
            'shippings' => ['nullable', 'array'],
            'shippings.*.id' => ['nullable', 'integer', 'distinct'],
            'shippings.*.tracking_number' => ['nullable', 'string', 'max:' . InputLimits::TRACKING_NUMBER],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // キャンセル済みは編集不可。発送済みは番号訂正を許可
            if ($order->status === OrderStatus::CANCELLED) {
                throw ValidationException::withMessages([
                    'shippings' => 'キャンセル済みの注文は送り状情報を編集できません。',
                ]);
            }

            // 配達予定日時を更新
            $order->delivery_date = $validated['delivery_date'] ?? null;
            $order->delivery_time_slot = $validated['delivery_time_slot'] ?? null;
            $order->save();

            $existingShippings = $order->orderShippings()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $shippings = $validated['shippings'] ?? [];

            $submittedIds = collect($shippings)
                ->pluck('id')
                ->filter(fn($id) => $id !== null)
                ->map(fn($id) => (int) $id);

            // 別の注文に属する配送IDを指定されていないか確認
            if ($submittedIds->diff($existingShippings->keys())->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'shippings' => '更新対象の送り状情報が正しくありません。',
                ]);
            }

            foreach ($shippings as $shipping) {
                $data = [
                    'tracking_number' => $shipping['tracking_number'] ?? null,
                ];

                if (!empty($shipping['id'])) {
                    $existingShippings->get((int) $shipping['id'])->update($data);
                } else {
                    $order->orderShippings()->create($data);
                }
            }

            // 画面から削除された配送レコードを取得
            $deleteIds = $existingShippings->keys()->diff($submittedIds);

            if ($deleteIds->isNotEmpty()) {
                // 送り状番号が登録済みの個口は削除禁止
                $hasTrackingNumber = $existingShippings
                    ->only($deleteIds->all())
                    ->contains(fn($shipping) => filled($shipping->tracking_number));

                if ($hasTrackingNumber) {
                    throw ValidationException::withMessages([
                        'shippings' => '送り状番号が登録されている個口は削除できません。',
                    ]);
                }

                $order->orderShippings()
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', '配送情報を更新しました。');
    }

    /**
     * ヤマト連携情報を更新
     */
    public function updateB2Shippings(Request $request, Order $order)
    {
        $validated = $request->validate([
            'b2_shippings' => ['nullable', 'array'],
            'b2_shippings.*.id' => ['nullable', 'integer', 'distinct'],
            'b2_shippings.*.cool_type' => ['nullable', Rule::enum(CoolType::class)],
            'b2_shippings.*.item_name' => [
                'nullable',
                'string',
                'max:' . InputLimits::B2_ITEM_NAME,
            ],
        ]);

        DB::transaction(function () use ($order, $validated) {
            // 同じ注文への同時更新を防ぐ
            $order = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 発送済み・キャンセル済みは編集不可
            if (!$order->isEditable()) {
                throw ValidationException::withMessages([
                    'b2_shippings' => '発送済み・キャンセル済みの注文はヤマト連携情報を編集できません。',
                ]);
            }

            // 現在の個口情報を取得
            $existingShippings = $order->orderShippings()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $shippings = $validated['b2_shippings'] ?? [];

            // フォームから送信された既存個口ID
            $submittedIds = collect($shippings)
                ->pluck('id')
                ->filter(fn($id) => $id !== null)
                ->map(fn($id) => (int) $id);

            // 別の注文の個口IDが含まれていないか確認
            if ($submittedIds->diff($existingShippings->keys())->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'b2_shippings' => '更新対象の個口情報が正しくありません。',
                ]);
            }

            foreach ($shippings as $shipping) {
                $data = [
                    'cool_type' => $shipping['cool_type'] ?? null,
                    'item_name' => $shipping['item_name'] ?? null,
                ];

                if (!empty($shipping['id'])) {
                    // 既存個口のヤマト連携情報のみ更新
                    $existingShippings->get((int) $shipping['id'])->update($data);
                } else {
                    // 新しい個口を追加
                    $order->orderShippings()->create($data);
                }
            }

            // フォームから削除された個口を取得
            $deleteIds = $existingShippings->keys()->diff($submittedIds);

            if ($deleteIds->isNotEmpty()) {
                // 追跡番号登録済みの個口は削除禁止
                $hasTrackingNumber = $existingShippings
                    ->only($deleteIds->all())
                    ->contains(fn($shipping) => filled($shipping->tracking_number));

                if ($hasTrackingNumber) {
                    throw ValidationException::withMessages([
                        'b2_shippings' => '送り状番号が登録されている個口は削除できません。',
                    ]);
                }

                $order->orderShippings()
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'ヤマト連携情報を更新しました。');
    }

    // 納品書PDF作成
    public function deliverySlip(Order $order, DeliverySlipPdf $deliverySlipPdf)
    {
        abort_unless($order->canIssueDeliverySlip(), 403);

        $pdf = $deliverySlipPdf->generate($order);

        $fileName = '納品書_' . str_pad((string) $order->id, 8, '0', STR_PAD_LEFT) . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"delivery-slip-{$order->id}.pdf\"; filename*=UTF-8''" . rawurlencode($fileName),
        ]);
    }

    // 税額集計(トランザクション内から呼ぶこと)
    private function refreshTaxSummaries(Order $order): void
    {
        $taxItems = $order->orderItems()
            ->get(['tax_rate', 'subtotal'])
            ->map(fn($item) => [
                'tax_rate' => $item->tax_rate,
                'subtotal' => $item->subtotal,
            ])
            ->all();

        try {
            $taxSummaries = $this->taxCalculator->calculate(
                $taxItems,
                shippingFee: $order->shipping_fee ?? 0,
                codFee: $order->cod_fee ?? 0,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'items' => $e->getMessage(),
            ]);
        }

        $order->taxSummaries()->delete();
        $order->taxSummaries()->createMany($taxSummaries);
    }
}
