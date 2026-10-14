<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Enums\ProductCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // 検索条件を取得・検証
        $conditions = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'in:0,1'],
        ]);

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

        // 新しい順に10件ずつ取得
        $products = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'volume' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $product = new Product();
        $product->name = $validated['name'];
        $product->category = ProductCategory::from($validated['category']);
        $product->volume = $validated['volume'];
        $product->price = $validated['price'];
        $product->is_active = $validated['is_active'];
        $product->description = $validated['description'] ?? null;

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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'volume' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $product = Product::findOrFail($id);

        $product->name = $validated['name'];
        $product->category = ProductCategory::from($validated['category']);
        $product->volume = $validated['volume'];
        $product->price = $validated['price'];
        $product->is_active = $validated['is_active'];
        $product->description = $validated['description'] ?? null;

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
