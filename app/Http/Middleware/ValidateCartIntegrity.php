<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;

class ValidateCartIntegrity
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (! $this->cartService->validateIntegrity()) {
            // Clear invalid cart
            $this->cartService->clear();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cart was cleared due to invalid items.',
                ], 422);
            }

            return redirect()->back()->with('warning', 'Cart was cleared due to invalid items.');
        }

        return $next($request);
    }
}
