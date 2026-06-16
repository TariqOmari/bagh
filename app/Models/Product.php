<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }



       public function factoryLoans()
    {
        return $this->hasMany(FactoryLoan::class);
    }
}