<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));

        $customers = Customer::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'ilike', "%{$keyword}%")
                        ->orWhere('postal_code', 'ilike', "%{$keyword}%")
                        ->orWhere('address', 'ilike', "%{$keyword}%")
                        ->orWhere('phone', 'ilike', "%{$keyword}%")
                        ->orWhere('email', 'ilike', "%{$keyword}%")
                        ->orWhere('memo', 'ilike', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'keyword'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        $this->checkDuplicate($validated);

        Customer::create($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', '住所録に登録しました。');
    }

    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $this->validateCustomer($request);

        $this->checkDuplicate($validated, $customer->id);

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', '住所録を更新しました。');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', '住所録から削除しました。');
    }

    private function validateCustomer(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'postal_code' => ['required', 'string', 'max:' . InputLimits::POSTAL_CODE],
            'address' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'phone' => ['required', 'string', 'max:' . InputLimits::PHONE],
            'email' => ['nullable', 'email', 'max:' . InputLimits::SHORT_TEXT],
            'memo' => ['nullable', 'string', 'max:' . InputLimits::CUSTOMER_NOTE],
            'is_orderer' => ['required', 'boolean'],
            'is_recipient' => ['required', 'boolean'],
        ]);

        if (!$validated['is_orderer'] && !$validated['is_recipient']) {
            throw ValidationException::withMessages([
                'is_orderer' => '注文者または配送先のいずれかを選択してください。',
            ]);
        }

        return $validated;
    }


    // 入力内容の正規化を行い、重複チェック
    private function checkDuplicate(array $validated, ?int $exceptId = null): void
    {
        // 氏名：半角・全角スペースを除去
        $name = str_replace([' ', '　'], '', $validated['name']);

        // 電話番号・郵便番号：全角数字を半角に変換し、ハイフンを除去
        $phone = str_replace(
            ['-', '－'],
            '',
            mb_convert_kana($validated['phone'], 'n')
        );

        $postalCode = str_replace(
            ['-', '－'],
            '',
            mb_convert_kana($validated['postal_code'], 'n')
        );

        $exists = Customer::query()
            ->whereRaw(
                "REPLACE(REPLACE(name, ' ', ''), '　', '') = ?",
                [$name]
            )
            ->whereRaw(
                "REPLACE(REPLACE(
                TRANSLATE(phone, '０１２３４５６７８９', '0123456789'),
                '-', ''
            ), '－', '') = ?",
                [$phone]
            )
            ->whereRaw(
                "REPLACE(REPLACE(
                TRANSLATE(postal_code, '０１２３４５６７８９', '0123456789'),
                '-', ''
            ), '－', '') = ?",
                [$postalCode]
            )
            ->when($exceptId !== null, function ($query) use ($exceptId) {
                $query->where('id', '!=', $exceptId);
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => '同じ氏名・電話番号・郵便番号の顧客がすでに登録されています。',
            ]);
        }
    }
}
