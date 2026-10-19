<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $productsPopular = Product::active()->popular()
            ->take(config('shop.home.popular_products'))->get();

        $productsNew = Product::active()->newest()
            ->take(config('shop.home.new_products'))->get();

        $categories = ProductCategory::cases();

        return view('welcome', [
            'productsPopular' => $productsPopular,
            'productsNew' => $productsNew,
            'categories' => $categories,
        ]);
    }
}
