@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/payment-form.css') }}">
<div class="payment-container">
    <div class="payment-card">
        <div class="payment-header">
            <h2>Payment Successful</h2>
            <p>Your payment has been processed successfully via eSewa.</p>
        </div>
        <div class="payment-details">
            <p><strong>Transaction ID:</strong> {{ $transaction_uuid }}</p>
            <p><strong>Reference ID:</strong> {{ $refId }}</p>
            <p><strong>Amount:</strong> NPR {{ $amount }}</p>
        </div>
        <a href="{{ route('home') }}" class="submit-button">Return to Home</a>
    </div>
</div>
@endsection