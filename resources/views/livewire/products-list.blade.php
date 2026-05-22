<div class="mx-auto max-w-md p-4">
    <h2>Products</h2>
    <flux:separator class="mb-4" />
    <ul>
        @foreach($products as $product)
            <li class="inline-flex justify-between items-center w-full">
                <flux:label style="min-width: 50%;">
                    {{ $product->name }} - ${{ $product->price }}
                    (Stock: {{ $product->stock_quantity }})
                </flux:label>
                <flux:button type="button" class="ml-2 mb-2 px-2 py-1 bg-blue-500 text-white rounded" wire:click="addToCart({{ $product->id }})">Add to Cart</flux:button>
            </li>
        @endforeach
    </ul>
     <flux:separator class="my-4" />
    @if (session('error'))
        <div class="alert alert-error mt-4">
            {{ session('error') }}
        </div>
    @endif
</div>
