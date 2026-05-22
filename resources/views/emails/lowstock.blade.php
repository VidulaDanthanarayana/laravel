@component('mail::message')
# Low Stock Alert

Product **{{ $product->name }}** is running low.  
Remaining stock: **{{ $product->stock_quantity }}**

@endcomponent
