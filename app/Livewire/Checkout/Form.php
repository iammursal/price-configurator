<?php

namespace App\Livewire\Checkout;

use App\Enums\UserType;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Services\CartService;
use App\Services\DiscountService;
use Cknow\Money\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Livewire Checkout Form: collects customer info and shows order pricing.
 *
 * Cart contract (session('cart')):
 * cart = [
 *   'items' => [
 *     [
 *       'product_id'  => int,
 *       'qty'         => int,                 // quantity for this line
 *       'selections'  => [attrId => optionId],
 *       'amount'      => int,                 // LINE TOTAL in minor units (e.g., fils)
 *       'config_key'  => string|null,
 *     ],
 *     ...
 *   ],
 * ]
 *
 * Pricing contract (from DiscountService::calculateDiscounts):
 * [
 *   'rules'          => [ ['rule' => DiscountRule, 'amount' => int, 'applied_to' => int], ... ],
 *   'discounts'      => same as 'rules' (alias),
 *   'total_discount' => int (minor units),
 *   'subtotal'       => int (minor units),
 *   'final_total'    => int (minor units),
 *   'cascading_steps'=> array (for debug/UX),
 * ]
 *
 * Notes:
 * - All money math uses minor units (integers) to avoid float precision issues.
 * - UI formatting uses money() helper with default currency to convert integers to display strings.
 */
class Form extends Component
{
    // Customer Information
    /** Customer first name. */
    public string $first_name = '';

    /** Customer last name. */
    public string $last_name = '';

    /** Customer email address. */
    public string $email = '';

    /** Customer phone number. */
    public string $phone = '';

    /** Billing address line 1 (required). */
    public string $address_line_1 = '';

    /** Billing address line 2 (optional). */
    public string $address_line_2 = '';

    /** City (required). */
    public string $city = '';

    /** State/Province (required). */
    public string $state = '';

    /** Postal/ZIP code (required). */
    public string $postal_code = '';

    // Customer Type
    /** normal|company (affects discount rules). */
    public string $customer_type = UserType::Normal->value; // Default to 'normal'

    // Cart and Pricing
    /** Raw cart items from session (see contract above). */
    public array $cart_items = [];

    /** DiscountService result payload (see contract above). */
    public array $discount_calculation = [];

    protected $middleware = ['validate.cart'];

    /**
     * Validation rules for the form fields.
     * Keep simple string constraints; Livewire validates on submit.
     */
    protected function rules(): array
    {
        $config = config('validation.customer');

        return [
            'first_name' => ['required', 'string', "min:{$config['first_name']['min']}", "max:{$config['first_name']['max']}"],
            'last_name' => ['required', 'string', "min:{$config['last_name']['min']}", "max:{$config['last_name']['max']}"],
            'email' => ['required', 'email', "max:{$config['email']['max']}"],
            'phone' => ['required', 'string', "min:{$config['phone']['min']}", "max:{$config['phone']['max']}"],
            'address_line_1' => ['required', 'string', "min:{$config['address_line_1']['min']}", "max:{$config['address_line_1']['max']}"],
            'address_line_2' => ['nullable', 'string', "max:{$config['address_line_2']['max']}"],
            'city' => ['required', 'string', "min:{$config['city']['min']}", "max:{$config['city']['max']}"],
            'state' => ['required', 'string', "min:{$config['state']['min']}", "max:{$config['state']['max']}"],
            'postal_code' => ['required', 'string', "min:{$config['postal_code']['min']}", "max:{$config['postal_code']['max']}"],
            'customer_type' => ['required', 'in:normal,company'],
        ];
    }

    /** Initialize cart and pricing on mount. */
    public function mount()
    {
        $this->loadCart();
        $this->calculateDiscounts();

        // Pre-fill with user data if authenticated
        if (Auth::check()) {
            $user = Auth::user();
            $this->first_name = explode(' ', $user->name)[0] ?? '';
            $this->last_name = explode(' ', $user->name, 2)[1] ?? '';
            $this->email = $user->email;
        }
    }

    /** Recalculate discounts when customer type changes (affects eligibility). */
    public function updatedCustomerType()
    {
        $this->calculateDiscounts();
    }

    /** Load cart array from session (no DB hits here). */
    public function loadCart()
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getCart();
        $this->cart_items = $cart['items'] ?? [];

        // Validate cart integrity
        if (! $cartService->validateIntegrity()) {
            $this->dispatch('notify', body: 'Cart has been updated due to invalid items.', type: 'warning');
            $this->cart_items = $cartService->getCart()['items'] ?? [];
        }
    }

    /**
     * Run the discount engine over the current cart.
     * Produces subtotal, discounts, and final_total in minor units.
     */
    public function calculateDiscounts()
    {
        if (empty($this->cart_items)) {
            // Standard empty state
            $this->discount_calculation = [
                'rules' => [],
                'total_discount' => 0,
                'subtotal' => 0,
                'final_total' => 0,
            ];

            return;
        }

        $userType = UserType::from($this->customer_type);
        $discountService = app(DiscountService::class);
        $this->discount_calculation = $discountService->calculateDiscounts(
            $this->cart_items,
            $userType
        );
    }

    /**
     * Remove a line by array index and refresh totals.
     * Keeps array indexes contiguous to match UI indices.
     */
    public function removeItem(int $index)
    {
        $cartService = app(CartService::class);
        $cartService->removeItem($index);
        $this->loadCart();
        $this->calculateDiscounts();
        $this->dispatch('cart-updated');
    }

    /**
     * Listen for external cart updates (icon, configurator, etc.) and refresh.
     */
    #[On('cart-updated')]
    public function refreshCart()
    {
        // Refresh cart data and calculations
        $this->mount(); // Re-run the mount method to refresh data
    }

    /**
     * Validate, simulate order creation, clear cart, and redirect.
     * Returns a redirect response for Livewire to navigate.
     */
    public function placeOrder()
    {
        $this->validate();

        if (empty($this->cart_items)) {
            $this->dispatch('notify', body: 'Your cart is empty.', type: 'error');

            return;
        }

        try {
            $customerData = [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city' => $this->city,
                'state' => $this->state,
                'postal_code' => $this->postal_code,
                'customer_type' => UserType::from($this->customer_type),
            ];

            // Clear cart and notify listeners
            session()->forget('cart');
            $this->dispatch('cart-updated');

            // Redirect with success message
            session()->flash('success', 'Order placed successfully! Order ID: '.uniqid('ORD-'));

            return redirect()->route('home');

        } catch (\Exception $e) {
            $this->dispatch('notify', body: 'Failed to place order. Please try again.', type: 'error');
            \Log::error('Order placement failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Build view-friendly cart items with Product and selected option names.
     * Returns an array suitable for the checkout page listing.
     */
    protected function loadCartItemsWithDetails(): array
    {
        $cartItems = [];

        foreach ($this->cart_items as $index => $item) {
            $product = Product::find($item['product_id']);
            if (! $product) {
                continue;
            }

            // Resolve selected option names for display
            $selectedOptions = [];
            foreach ($item['selections'] as $attributeId => $optionId) {
                $option = AttributeOption::find($optionId);
                if ($option) {
                    $selectedOptions[] = $option->name;
                }
            }

            $cartItems[] = [
                'index' => $index,
                'product' => $product,
                'qty' => $item['qty'],
                'amount' => $item['amount'],     // line total in minor units
                'selected_options' => $selectedOptions,
            ];
        }

        return $cartItems;
    }

    /**
     * Prepare Money instances for the view and map rules -> discounts structure the UI expects.
     * Renders the checkout Blade view.
     */
    public function render()
    {
        // Load cart items with product details
        $cartItemsWithDetails = $this->loadCartItemsWithDetails();

        // Use default currency for display (values are in minor units)
        $subtotalMoney = money($this->discount_calculation['subtotal'] ?? 0);
        $totalDiscountMoney = money($this->discount_calculation['total_discount'] ?? 0);
        $finalTotalMoney = money($this->discount_calculation['final_total'] ?? 0);

        // Transform discount rules to include formatted Money for the UI.
        // The DiscountService returns 'rules', but the view expects 'discounts'.
        $discounts = [];
        foreach ($this->discount_calculation['rules'] ?? [] as $discount) {
            $discounts[] = [
                'rule' => $discount['rule'],
                'amount' => $discount['amount'],
                'applied_to' => $discount['applied_to'],
                'phase' => $discount['phase'] ?? null, // optional label
                'money' => money($discount['amount']),
                'formatted' => money($discount['amount'])->format(),
            ];
        }

        return view('livewire.checkout.form', [
            'cartItems' => $cartItemsWithDetails,
            'discountCalculation' => [
                'rules' => $this->discount_calculation['rules'] ?? [],
                'discounts' => $discounts, // View consumes this
                'total_discount' => $this->discount_calculation['total_discount'] ?? 0,
                'subtotal' => $this->discount_calculation['subtotal'] ?? 0,
                'final_total' => $this->discount_calculation['final_total'] ?? 0,
            ],
            'subtotalMoney' => $subtotalMoney,
            'totalDiscountMoney' => $totalDiscountMoney,
            'finalTotalMoney' => $finalTotalMoney,
        ]);
    }
}
