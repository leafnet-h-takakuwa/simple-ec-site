<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

use App\Enums\ProductCategory;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::cases();
        $category = $request->query('category');
        $sort = $request->query('sort');

        // 指定されたカテゴリが有効か確認
        if ($category !== null) {
            if (ProductCategory::tryFrom($category) === null) {
                abort(404);
            }
        }

        // 指定された並び順が有効か確認
        if ($sort !== null && $sort !== 'new') {
            abort(404);
        }

        $query = Product::query()
            ->where('is_active', true);

        // カテゴリ指定あれば条件追加
        if ($category !== null) {
            $query->where('category', $category);
        }

        // 並び順
        if ($sort === 'new') {
            $query->orderByDesc('created_at')
                ->orderByDesc('id');
        } else {
            $query->withSum('orderItems', 'quantity')
                ->orderByDesc('is_standard')
                ->orderByRaw('order_items_sum_quantity DESC NULLS LAST')
                ->orderByDesc('id');
        }

        $products = $query->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'sort' => $sort,
        ]);
    }

    public function show(string $id)
    {
        // 商品IDを指定してDBから取得
        $product = Product::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        return view('products.show', [
            'product' => $product,
        ]);
    }
}
