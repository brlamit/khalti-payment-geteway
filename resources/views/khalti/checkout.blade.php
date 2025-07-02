@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/payment-form.css') }}">
@endsection

@section('content')
<div class="container">
   

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @auth
        <h2>Order Details</h2>
        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Order ID:</strong> {{ $order->id }}</li>
            <li class="list-group-item"><strong>Item:</strong> {{ $order->item->name }}</li>
            <li class="list-group-item"><strong>Amount:</strong> NPR {{ number_format($order->amount, 2) }}</li>
            @if ($order->item->description)
                <li class="list-group-item"><strong>Description:</strong> {{ $order->item->description }}</li>
            @endif
        </ul>

        <form action="{{ route('checkout.khalti', $order->id) }}" method="POST">
            @csrf
            <input type="hidden" name="amount" value="{{ $order->amount }}">
            <button type="submit" class="btn btn-primary">
                Pay with Khalti {{ config('services.khalti.testing') ? '(Test Mode)' : '' }}
            </button>
        </form>
    @else
        <div class="alert alert-info" role="alert">
            Please <a href="{{ route('login') }}" class="alert-link">log in</a> to proceed with checkout.
        </div>
    @endauth
</div>
@endsection