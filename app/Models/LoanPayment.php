<?php
// app/Models/LoanPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'factory_loan_id',
        'amount',
        'payment_date', // gregorian date
        'payment_date_persian', // persian date 1405-3-12
        'note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date'
    ];

    public function factoryLoan()
    {
        return $this->belongsTo(FactoryLoan::class);
    }
}