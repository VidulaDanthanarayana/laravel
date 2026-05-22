<x-layouts.app :title="$success ? 'Payment Successful' : 'Payment Failed'">
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="w-full max-w-sm">

            {{-- Icon --}}
            <div class="flex justify-center mb-6">
                @if ($success)
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                @else
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Heading --}}
            <h1 class="text-2xl font-bold text-center mb-1">
                {{ $success ? 'Payment Successful' : 'Payment ' . ucfirst($status) }}
            </h1>
            <p class="text-center text-sm text-gray-500 mb-6">
                {{ $success
                    ? 'Your order has been confirmed and your cart has been cleared.'
                    : 'Your payment could not be completed. Your cart has been kept.' }}
            </p>

            {{-- Details --}}
            @if ($reference || $amount)
                <flux:card class="mb-6 space-y-2 text-sm">
                    @if ($reference)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Reference</span>
                            <span class="font-mono font-medium">{{ $reference }}</span>
                        </div>
                        <flux:separator />
                    @endif
                    @if ($amount)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Amount</span>
                            <span class="font-medium">LKR {{ number_format((float) $amount, 2) }}</span>
                        </div>
                        <flux:separator />
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date</span>
                        <span>{{ now()->format('d M Y, H:i') }}</span>
                    </div>
                </flux:card>
            @endif

            {{-- CTA --}}
            @if ($success)
                <flux:button href="{{ route('dashboard') }}" variant="primary" class="w-full">
                    Back to Dashboard
                </flux:button>
            @else
                <flux:button href="{{ route('dashboard') }}" variant="primary" class="w-full mb-3">
                    Back to Cart
                </flux:button>
                @if ($reference)
                    <p class="text-center text-xs text-gray-400">
                        Support reference: <strong>{{ $reference }}</strong>
                    </p>
                @endif
            @endif

        </div>
    </div>
</x-layouts.app>
