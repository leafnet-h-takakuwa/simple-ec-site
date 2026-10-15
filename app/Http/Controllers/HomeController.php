<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $productsTop3 = Product::where('is_active', true)
            ->withSum('orderItems', 'quantity')
            ->orderByRaw('order_items_sum_quantity DESC NULLS LAST')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $productsNew = Product::where('is_active', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $categories = ProductCategory::cases();

        return view('welcome', [
            'productsTop3' => $productsTop3,
            'productsNew' => $productsNew,
            'categories' => $categories,
        ]);
    }
}
