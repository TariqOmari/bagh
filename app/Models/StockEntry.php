<?php
// app/Models/StockEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price_per_carton',
        'carton_quantity',
        'currency', // afn or usd
        'total_price',
        'payment_method', // cash or loan
        'paid_amount',
        'remaining_amount',
        'payment_status', // paid, partial, unpaid
        'entry_date', // gregorian date
        'entry_date_persian', // persian date 1405-3-12
        'note'
    ];

    protected $casts = [
        'price_per_carton' => 'decimal:2',
        'carton_quantity' => 'integer',
        'total_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'entry_date' => 'date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function factoryLoans()
    {
        return $this->hasMany(FactoryLoan::class);
    }
}