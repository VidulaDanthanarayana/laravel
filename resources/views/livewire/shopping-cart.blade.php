<div class="mx-auto max-w-md p-4">
    <h2>Your Cart</h2>
    <flux:separator class="mb-4" />

    <ul>
        @foreach($cartItems as $item)
            <li>
                <flux:input.group class="mb-2">
                    <flux:label style="min-width: 50%;">
                        {{ $item->product->name }} - ${{ $item->product->price }}
                    </flux:label>
                    <flux:input type="number" min="1"
                        wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                        value="{{ $item->quantity }}" />
                    <flux:button type="button" wire:click="removeItem({{ $item->id }})">Remove</flux:button>
                </flux:input.group>
            </li>
        @endforeach
    </ul>

    @if ($cartItems->isEmpty())
        <p>Your cart is empty.</p>
    @else
        <flux:separator class="my-4" />
        <div class="flex justify-between items-center">
            <span class="font-semibold text-sm text-gray-600">
                Total: ${{ $cartItems->sum(fn($i) => $i->product->price * $i->quantity) }}
            </span>
            <flux:button id="checkout-btn" type="button"
                class="px-4 py-2 bg-green-500 text-white rounded"
                wire:click="checkout">
                Checkout
            </flux:button>
        </div>
    @endif

    {{-- Checkout confirmation modal --}}
    <flux:modal name="checkout-confirm">
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-bold">Confirm Checkout</h3>
            <p class="text-sm text-gray-600">You'll be redirected to OnePay to complete payment.</p>

            <div>
                <flux:label for="phone">Phone Number</flux:label>
                <flux:input id="phone" type="tel" wire:model="phone"
                    placeholder="+94 77 123 4567" class="mt-1 w-full" />
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary"
                    wire:click="processCheckout"
                    wire:loading.attr="disabled"
                    wire:target="processCheckout">
                    <span wire:loading.remove wire:target="processCheckout">Pay with OnePay</span>
                    <span wire:loading wire:target="processCheckout">Redirecting…</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    @if (session('error'))
        <div class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:open-checkout-modal', () => {
        Flux.modal('checkout-confirm').show();
    });

    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            Flux.modal('checkout-confirm').show();
        });
    }
</script>
