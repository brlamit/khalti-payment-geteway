<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Models\Item;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', fn () => view('welcome'))->name('welcome');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Item routes
   
        Route::get('/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
        Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
            Route::patch('/items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::post('/items/{item}/checkout', [ItemController::class, 'checkout'])->name('items.checkout');
 

    // Checkout and Khalti payment routes

        Route::get('/{order}', [ItemController::class, 'showCheckout'])->name('checkout.show');
        Route::post('/{order}/khalti', [ItemController::class, 'khaltiPayment'])->name('checkout.khalti');
        Route::get('/khalti/success', [ItemController::class, 'khaltiSuccess'])->name('khalti.success');
        Route::get('/khalti/failure', [ItemController::class, 'khaltiFailure'])->name('khalti.failure');
   
});



require __DIR__ . '/auth.php';