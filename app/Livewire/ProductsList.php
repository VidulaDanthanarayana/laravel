<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class ProductsList extends Component
{
    protected $listeners = ['listUpdated' => '$refresh'];

    public function addToCart($productId)
    {
        $user = Auth::user();
        $item = CartItem::firstOrCreate(
            ['user_id' => $user->id, 'product_id' => $productId],
            ['quantity' => 0]
        );
        $product = Product::find($productId);

        if($item->quantity >0){
            session()->flash('error', $product->name . ': Product already in cart!');
            return;
        }
        if($product->stock_quantity <= 0){
            session()->flash('error', $product->name . ': Not enough stock available!');
            return;
        }

        $item->increment('quantity');
        $product->decrement('stock_quantity');

        $this->dispatch('cartUpdated');
    }

    public function render()
    {
        return view('livewire.products-list', [
            'products' => Product::all()
        ]);
    }
}
