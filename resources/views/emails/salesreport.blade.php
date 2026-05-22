@component('mail::message')
# Daily Sales Report

@foreach($sales as $sale)
- {{ $sale->product->name }}: {{ $sale->quantity }} sold
@endforeach

@endcomponent
