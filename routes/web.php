<?php

use App\Http\Controllers\Pages\CheckoutController;
use App\Http\Controllers\Pages\HomeController;
use App\Http\Controllers\Pages\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Product details by slug
Route::get('/products/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])
    ->name('checkout.index')
    ->middleware('validate.cart');
