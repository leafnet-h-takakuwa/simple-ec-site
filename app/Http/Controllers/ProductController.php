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

        // 指定されたカテゴリが有効か確認
        if ($category !== null) {
            if (ProductCategory::tryFrom($category) === null) {
                abort(404);
            }
        }

        $query = Product::query()
            ->where('is_active', true);

        // カテゴリ指定あれば条件追加
        if ($category !== null) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
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
