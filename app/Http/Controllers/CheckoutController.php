<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function return(Request $request): View
    {
        $status    = $request->query('status', '');
        $reference = $request->query('reference', '');
        $amount    = $request->query('amount', '');

        $success = in_array(strtolower($status), ['success', 'paid', 'completed', '1']);

        // Record sales and clear cart only on a confirmed successful payment
        if ($success && session('onepay_cart_user_id')) {
            $userId = session('onepay_cart_user_id');

            DB::transaction(function () use ($userId) {
                $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

                foreach ($cartItems as $item) {
                    Sale::create([
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'user_id'    => $userId,
                    ]);
                }

                CartItem::where('user_id', $userId)->delete();
            });

            session()->forget('onepay_cart_user_id');
        }

        return view('checkout.return', compact('success', 'reference', 'amount', 'status'));
    }
}
