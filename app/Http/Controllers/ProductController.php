<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::availableCases();
        $category = $request->query('category');
        $sort = $request->query('sort');

        // 公開中の商品が存在するカテゴリか確認
        if ($category !== null) {
            $availableCategories = array_map(
                fn(ProductCategory $category) => $category->value,
                $categories
            );

            if (!in_array($category, $availableCategories, true)) {
                abort(404);
            }
        }

        // 指定された並び順が有効か確認
        if ($sort !== null && $sort !== 'new') {
            abort(404);
        }

        $query = Product::active();

        // カテゴリ指定あれば条件追加
        if ($category !== null) {
            $query->where('category', $category);
        }

        // 並び順
        if ($sort === 'new') {
            $query->newest();
        } else {
            $query->orderByDesc('is_standard')->popular();
        }

        $products = $query->orderBy('id')
            ->paginate(config('shop.pagination.products'))
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
