<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $products = Product::where('is_active', true)->get();

        return view('pages.index', [
            'products' => $products,
        ]);
    }
}
