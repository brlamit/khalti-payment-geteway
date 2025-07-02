<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentTracking;
use App\Services\KhaltiPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    protected $paymentService;
    protected $isTesting;

    public function __construct(KhaltiPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->isTesting = env('KHALTI_Testing', true);
    }

    public function index()
    {
        $items = Auth::user()->items()->get();
        return view('dashboard', compact('items'));
    }

    public function create()
    {
        return view('items.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|unique:items,name,NULL,id,user_id,' . Auth::id(),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:10',
        ]);

        Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('items.create')->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        if ($item->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
        }
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        if ($item->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
        }
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        if ($item->user_id !== Auth::id()) {
            return redirect()->route('items.index')->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|max:255|unique:items,name,' . $item->id . ',id,user_id,' . Auth::id(),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:10',
        ]);

        $item->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

   public function checkout(Request $request, Item $item)
{
    if ($item->user_id === Auth::id()) {
        return redirect()->route('dashboard')->with('error', 'You cannot purchase your own item.');
    }

    $purchaseOrderId = 'PO-' . Auth::id() . '-' . $item->id . '-' . uniqid();

    $order = Order::create([
        'user_id' => Auth::id(),
        'item_id' => $item->id,
        'amount' => $item->price,
        'status' => 'pending',
        'transaction_id' => $purchaseOrderId,
    ]);

    try {
        $paymentResponse = $this->paymentService->initiatePayment(
            amount: $item->price,
            purchaseOrderId: $purchaseOrderId,
            purchaseOrderName: $item->name,
            customerInfo: [
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'phone' => '9800000001',
            ]
        );

        Log::info('Khalti payment initiated', $paymentResponse);

        $order->update(['pidx' => $paymentResponse['pidx']]);

        return redirect($paymentResponse['payment_url']); // ✅ Redirects to Khalti payment page

    } catch (\Exception $e) {
        $order->delete();
        Log::error('Khalti payment initiation failed: ' . $e->getMessage());
        return redirect()->route('items.index')->with('error', 'Payment initiation failed: ' . $e->getMessage());
    }
}


    public function showCheckout(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
        }
        return view('khalti.checkout', compact('order'));
    }

    public function khaltiPayment(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:10|in:' . $order->amount,
        ]);

        try {
            $paymentResponse = $this->paymentService->initiatePayment(
                amount: $order->amount,
                purchaseOrderId: $order->transaction_id,
                purchaseOrderName: $order->item->name,
                customerInfo: [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'phone' => '9800000001',
                ]
            );

            $order->update(['pidx' => $paymentResponse['pidx']]);
            return redirect($paymentResponse['payment_url']);
        } catch (\Exception $e) {
    $order->delete();
    Log::error('Khalti payment initiation failed: ' . $e->getMessage());
    return redirect()->route('items.index')->with('error', 'Payment initiation failed: ' . $e->getMessage());
}

    }

    public function khaltiSuccess(Request $request)
    {
        $pidx = $request->query('pidx');

        if (!$pidx) {
            return redirect()->route('dashboard')->with('error', 'Invalid payment identifier.');
        }

        try {
            $lookup = $this->paymentService->verifyPayment($pidx);
            $order = Order::where('pidx', $pidx)->firstOrFail();

            $status = match ($lookup['status'] ?? 'Pending') {
                'Completed' => 'completed',
                'Canceled', 'Expired', 'Failed' => 'failed',
                default => 'pending',
            };

            $order->update(['status' => $status]);

            PaymentTracking::create([
                'user_id' => Auth::id(),
                'paymentable_type' => Order::class,
                'paymentable_id' => $order->id,
                'amount' => (float) ($lookup['total_amount'] / 100),
                'paymentMethod' => 'khalti',
                'transactionId' => $lookup['transaction_id'] ?? null,
                'transactionUuid' => $pidx,
                'paymentStatus' => $status,
            ]);

            return redirect()->route('dashboard')->with(
                $status === 'completed' ? 'message' : 'error',
                $status === 'completed'
                    ? 'Payment successful for order ' . $order->id
                    : 'Payment ' . $status . '. Please contact support.'
            );
        } catch (\Exception $e) {
            Log::error('Payment verification error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Verification failed: ' . $e->getMessage());
        }
    }

    public function khaltiFailure(Request $request)
    {
        $pidx = $request->query('pidx');
        $order = Order::where('pidx', $pidx)->first();

        if ($order && $order->user_id === Auth::id()) {
            $order->update(['status' => 'failed']);
            Log::warning('Khalti payment failed for order ' . $order->id . ': ' . json_encode($request->all()));
        }

        return redirect()->route('dashboard')->with('error', 'Payment failed. Please try again.');
    }
}
