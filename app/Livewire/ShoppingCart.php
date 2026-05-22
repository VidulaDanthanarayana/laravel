<?php

namespace App\Livewire;

use App\Jobs\SendLowStockNotification;
use App\Models\CartItem;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use OnePay\Checkout\Exceptions\OnePayException;
use OnePay\Checkout\Services\OnePayService;

class ShoppingCart extends Component
{
    protected $listeners = ['cartUpdated' => '$refresh', 'proceed-checkout' => 'processCheckout'];

    public string $phone = '';

    public function updateQuantity($itemId, $newQuantity)
    {
        $item    = CartItem::where('user_id', Auth::id())->findOrFail($itemId);
        $product = $item->product;

        if (! is_numeric($newQuantity) || $newQuantity < 1) {
            session()->flash('error', $product->name . ': Quantity must be at least 1 and numeric!');
            return;
        }

        $oldQuantity = $item->quantity;
        $diff        = $newQuantity - $oldQuantity;

        if ($diff > 0 && $product->stock_quantity < $diff) {
            session()->flash('error', $product->name . ': Not enough stock available!');
            return;
        }

        $item->update(['quantity' => $newQuantity]);

        if ($diff < 0) {
            $product->increment('stock_quantity', abs($diff));
        } else {
            $product->decrement('stock_quantity', $diff);
            if ($product->stock_quantity <= 3) {
                SendLowStockNotification::dispatch($product);
            }
        }

        $this->dispatch('listUpdated');
    }

    public function removeItem($itemId)
    {
        $item    = CartItem::where('user_id', Auth::id())->findOrFail($itemId);
        $product = $item->product;

        $product->increment('stock_quantity', $item->quantity);
        $item->delete();
        $this->dispatch('listUpdated');
    }

    public function checkout()
    {
        $this->dispatch('open-checkout-modal');
    }

    public function processCheckout(OnePayService $onePay)
    {
        $this->validate(['phone' => ['required', 'string', 'min:7', 'max:20']]);

        $cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return;
        }

        $total = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
        $user  = Auth::user();

        // Split name into first / last (fallback if single word)
        $nameParts = explode(' ', trim($user->name), 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? $nameParts[0];

        // Store cart snapshot in session so we can record sales after payment
        session(['onepay_cart_user_id' => $user->id]);

        try {
            $reference = $onePay->generateReference('ORD');

            $response = $onePay->createCheckoutLink([
                'reference'                => $reference,
                'currency'                 => config('onepay.currency', 'LKR'),
                'amount'                   => round((float) $total, 2),
                'customer_first_name'      => $firstName,
                'customer_last_name'       => $lastName,
                'customer_phone_number'    => $this->phone,
                'customer_email'           => $user->email,
                'transaction_redirect_url' => route('checkout.return'),
            ]);

            if (! $response->succeeded()) {
                session()->flash('error', 'Payment gateway error. Please try again.');
                return;
            }

            return redirect()->away($response->redirectUrl);

        } catch (OnePayException $e) {
            $msg = $e->hasRemoteErrorPayload() ? ($e->getRemoteMessage() ?? $e->getMessage()) : $e->getMessage();
            session()->flash('error', 'OnePay: ' . $msg);
        }
    }

    public function render()
    {
        return view('livewire.shopping-cart', [
            'cartItems' => CartItem::with('product')
                ->where('user_id', Auth::id())
                ->get(),
        ]);
    }
}
