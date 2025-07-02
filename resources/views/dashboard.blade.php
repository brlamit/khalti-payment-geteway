@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/payment-form.css') }}">
@endsection

@section('content')
<div class="items-list-container">
    <h3>Your Items</h3>

    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @auth
        @if ($items->count())
            <table class="items-table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Item Name</th>
                        <th scope="col">Description</th>
                        <th scope="col">Price (NPR)</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->description ?? 'N/A' }}</td>
                            <td>{{ number_format($item->price, 2) }}</td>
                            <td>
                                <form action="{{ route('items.checkout', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Checkout</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info" role="alert">
                No items found. <a href="{{ route('items.create') }}">Create a new item</a>.
            </div>
        @endif
    @else
        <div class="alert alert-warning" role="alert">
            Please <a href="{{ route('login') }}">log in</a> to view your items.
        </div>
    @endauth
</div>
@endsection