<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\FactoryLoan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use App\Helpers\PersianDateHelper;

class ProductController extends Controller
{
    /**
     * Create product - ONLY NAME
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name'
        ]);

        $product = Product::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Add stock to product with Persian date in BODY
     */
    public function addStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'price_per_carton' => 'required|numeric|min:0',
            'carton_quantity' => 'required|integer|min:1',
            'currency' => 'required|in:afn,usd',
            'payment_method' => 'required|in:cash,loan',
            'paid_amount' => 'required|numeric|min:0',
            'entry_date_persian' => 'required|string', // 1405-3-12 IN BODY
            'note' => 'nullable|string'
        ]);

        $product = Product::findOrFail($request->product_id);

        // Convert Persian date to Gregorian
        $entry_date = PersianDateHelper::persianToGregorian($request->entry_date_persian);

        // Calculate total price
        $total_price = $request->price_per_carton * $request->carton_quantity;
        $paid_amount = $request->paid_amount;
        $remaining_amount = $total_price - $paid_amount;

        // Determine payment status
        $payment_status = 'unpaid';
        if ($paid_amount == $total_price) {
            $payment_status = 'paid';
        } elseif ($paid_amount > 0) {
            $payment_status = 'partial';
        }

        // Create stock entry with date
        $stockEntry = StockEntry::create([
            'product_id' => $product->id,
            'price_per_carton' => $request->price_per_carton,
            'carton_quantity' => $request->carton_quantity,
            'currency' => $request->currency,
            'total_price' => $total_price,
            'payment_method' => $request->payment_method,
            'paid_amount' => $paid_amount,
            'remaining_amount' => $remaining_amount,
            'payment_status' => $payment_status,
            'entry_date' => $entry_date,
            'entry_date_persian' => $request->entry_date_persian,
            'note' => $request->note
        ]);

        // If loan, create factory loan record with date
        if ($request->payment_method === 'loan') {
            $factoryLoan = FactoryLoan::create([
                'stock_entry_id' => $stockEntry->id,
                'product_id' => $product->id,
                'loan_amount' => $total_price,
                'paid_amount' => $paid_amount,
                'remaining_amount' => $remaining_amount,
                'status' => $payment_status,
                'loan_date' => $entry_date,
                'loan_date_persian' => $request->entry_date_persian,
                'note' => $request->note ?? 'Loan for stock entry #' . $stockEntry->id
            ]);

            return response()->json([
                'message' => 'Stock added on loan successfully',
                'data' => [
                    'product' => $product,
                    'stock_entry' => $stockEntry,
                    'factory_loan' => $factoryLoan
                ]
            ], 201);
        }

        return response()->json([
            'message' => 'Stock added successfully with cash',
            'data' => [
                'product' => $product,
                'stock_entry' => $stockEntry
            ]
        ], 201);
    }

    /**
     * Pay for loan with Persian date in BODY
     */
    public function payLoan(Request $request, $loanId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date_persian' => 'required|string', // 1405-3-12 IN BODY
            'note' => 'nullable|string'
        ]);

        // Convert Persian date to Gregorian
        $payment_date = PersianDateHelper::persianToGregorian($request->payment_date_persian);

        $loan = FactoryLoan::with('stockEntry')->findOrFail($loanId);

        if ($loan->status === 'paid') {
            return response()->json([
                'message' => 'This loan is already fully paid'
            ], 400);
        }

        if ($request->amount > $loan->remaining_amount) {
            return response()->json([
                'message' => 'Payment amount exceeds remaining balance',
                'remaining_amount' => $loan->remaining_amount
            ], 400);
        }

        // Create loan payment record with date
        $payment = LoanPayment::create([
            'factory_loan_id' => $loan->id,
            'amount' => $request->amount,
            'payment_date' => $payment_date,
            'payment_date_persian' => $request->payment_date_persian,
            'note' => $request->note ?? 'Payment for loan #' . $loan->id
        ]);

        // Update loan
        $loan->paid_amount += $request->amount;
        $loan->remaining_amount -= $request->amount;
        $loan->status = $loan->remaining_amount == 0 ? 'paid' : 'partial';
        $loan->save();

        // Also update the stock entry
        $stockEntry = $loan->stockEntry;
        if ($stockEntry) {
            $stockEntry->paid_amount += $request->amount;
            $stockEntry->remaining_amount -= $request->amount;
            $stockEntry->payment_status = $stockEntry->remaining_amount == 0 ? 'paid' : 'partial';
            $stockEntry->save();
        }

        return response()->json([
            'message' => 'Loan payment successful',
            'data' => [
                'loan' => $loan,
                'payment' => $payment,
                'remaining_amount' => $loan->remaining_amount,
                'status' => $loan->status
            ]
        ]);
    }

    /**
     * Get stock entries with date filters in BODY
     */
    public function stockEntries(Request $request)
    {
        $entries = StockEntry::with(['product', 'factoryLoans'])
            ->when($request->payment_method, function($query, $method) {
                return $query->where('payment_method', $method);
            })
            ->when($request->payment_status, function($query, $status) {
                return $query->where('payment_status', $status);
            })
            ->when($request->date_from_persian, function($query, $date) {
                return $query->where('entry_date', '>=', PersianDateHelper::persianToGregorian($date));
            })
            ->when($request->date_to_persian, function($query, $date) {
                return $query->where('entry_date', '<=', PersianDateHelper::persianToGregorian($date));
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $entries
        ]);
    }

    /**
     * Get all factory loans with date filters in BODY
     */
    public function getLoans(Request $request)
    {
        $loans = FactoryLoan::with(['product', 'stockEntry', 'loanPayments'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->product_id, function($query, $productId) {
                return $query->where('product_id', $productId);
            })
            ->when($request->date_from_persian, function($query, $date) {
                return $query->where('loan_date', '>=', PersianDateHelper::persianToGregorian($date));
            })
            ->when($request->date_to_persian, function($query, $date) {
                return $query->where('loan_date', '<=', PersianDateHelper::persianToGregorian($date));
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $loans
        ]);
    }

    /**
     * Get all products with their stock entries
     */
    public function index()
    {
        $products = Product::with('stockEntries.factoryLoans')->get();

        return response()->json([
            'data' => $products
        ]);
    }

    /**
     * Get single product with stock entries
     */
    public function show($id)
    {
        $product = Product::with('stockEntries.factoryLoans')->findOrFail($id);

        return response()->json([
            'data' => $product
        ]);
    }

    /**
     * Get single loan with payments
     */
    public function getLoan($id)
    {
        $loan = FactoryLoan::with(['product', 'stockEntry', 'loanPayments'])
            ->findOrFail($id);

        return response()->json([
            'data' => $loan
        ]);
    }

    /**
     * Get loan summary
     */
    public function loanSummary()
    {
        $totalLoans = FactoryLoan::where('status', '!=', 'paid')->count();
        $totalAmount = FactoryLoan::sum('loan_amount');
        $totalPaid = FactoryLoan::sum('paid_amount');
        $totalRemaining = FactoryLoan::sum('remaining_amount');

        $unpaidLoans = FactoryLoan::where('status', 'unpaid')->with('product')->get();
        $partialLoans = FactoryLoan::where('status', 'partial')->with('product')->get();

        return response()->json([
            'data' => [
                'summary' => [
                    'total_loans' => $totalLoans,
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'total_remaining' => $totalRemaining
                ],
                'unpaid_loans' => $unpaidLoans,
                'partial_loans' => $partialLoans
            ]
        ]);
    }
}