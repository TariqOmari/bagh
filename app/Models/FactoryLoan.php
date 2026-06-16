<?php
// app/Models/FactoryLoan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_entry_id',
        'product_id',
        'loan_amount',
        'paid_amount',
        'remaining_amount',
        'status', // unpaid, partial, paid
        'loan_date', // gregorian date
        'loan_date_persian', // persian date 1405-3-12
        'note'
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'loan_date' => 'date'
    ];

    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function loanPayments()
    {
        return $this->hasMany(LoanPayment::class);
    }
}