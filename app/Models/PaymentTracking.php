<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTracking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'paymentable_type',
        'paymentable_id',
        'amount',
        'paymentMethod',
        'transactionId',
        'transactionUuid',
        'paymentStatus',
    ];

    /**
     * Get the parent paymentable model (e.g., Order).
     */
    public function paymentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the payment tracking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}