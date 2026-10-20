<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use App\Models\Product;
use App\Models\Notice;

class HomeController extends Controller
{
    public function index()
    {
        $productsPopular = Product::active()->popular()
            ->take(config('shop.home.popular_products'))->get();

        $productsNew = Product::active()->newest()
            ->take(config('shop.home.new_products'))->get();

        $categories = ProductCategory::cases();

        // 最新のお知らせ5件
        $notices = Notice::where('is_published', true)
            ->whereDate('published_at', '<=', today())
            ->latest('published_at')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('welcome', [
            'productsPopular' => $productsPopular,
            'productsNew' => $productsNew,
            'categories' => $categories,
            'notices' => $notices,
        ]);
    }
}
