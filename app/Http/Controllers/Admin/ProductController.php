<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // 検索条件を取得・検証
        $conditions = $request->validate([
            'name' => ['nullable', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'is_active' => ['nullable', 'in:0,1'],
        ], [], ['name' => '商品名']);

        $query = Product::query();

        // 商品名（部分一致）
        if (!empty($conditions['name'])) {
            $query->where(
                'name',
                'ilike',
                '%' . $conditions['name'] . '%'
            );
        }

        // 公開状態
        if (isset($conditions['is_active']) && $conditions['is_active'] !== '') {
            $query->where('is_active', $conditions['is_active']);
        }

        // 新しい順に設定件数ずつ取得
        $products = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(config('shop.pagination.admin_products'))
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'conditions' => $conditions,
        ]);
    }

    public function create()
    {
        return view('admin.products.create', [
            'categories' => ProductCategory::cases(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $product = new Product();
        $product->fill($request->productAttributes());

        // 商品画像を保存
        if ($request->hasFile('image')) {
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', '商品を登録しました。');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => ProductCategory::cases(),
        ]);
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->fill($request->productAttributes());

        $old_image_path = $product->image_path;
        $new_image_path = null;

        // 新しい画像があれば差し替えを優先
        if ($request->hasFile('image')) {
            $new_image_path = $request->file('image')->store('products', 'public');
            $product->image_path = $new_image_path;
        } elseif ($request->boolean('remove_image')) {
            $product->image_path = null;
        }

        try {
            $product->save();
        } catch (\Throwable $e) {
            // DB保存に失敗した場合は、新規アップロード画像を片付ける
            if ($new_image_path) {
                Storage::disk('public')->delete($new_image_path);
            }
            throw $e;
        }

        // DB保存成功後に、不要になった旧画像を削除
        if ($old_image_path && $old_image_path !== $product->image_path) {
            Storage::disk('public')->delete($old_image_path);
        }

        return redirect()->route('admin.products.edit', $product->id)
            ->with('success', '商品情報を更新しました。');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // 商品は論理削除。画像ファイルは残す
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', '商品を削除しました。');
    }
}
