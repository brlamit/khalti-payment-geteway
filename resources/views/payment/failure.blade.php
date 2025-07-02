
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/payment-form.css') }}">
<div class="payment-container">
    <div class="payment-card">
        <div class="payment-header">
            <h2>Payment Failed</h2>
            <p>{{ $error }}</p>
        </div>
        <a href="{{ route('payment.form') }}" class="submit-button">Try Again</a>
    </div>
</div>
@endsection