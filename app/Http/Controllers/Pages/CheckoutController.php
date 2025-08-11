<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index()
    {
        $cart = session('cart', ['items' => []]);

        // Redirect to home if cart is empty
        if (empty($cart['items'])) {
            return redirect()->route('home')->with('message', 'Your cart is empty.');
        }

        return view('pages.checkout.index');
    }
}
