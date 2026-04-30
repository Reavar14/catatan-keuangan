<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Total pemasukan
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        // Total pengeluaran
        $totalExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        // Saldo
        $balance = $totalIncome - $totalExpense;

        // Data grafik 12 bulan terakhir
        $monthlyChart = Transaction::select(
                DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as month'),
                DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense')
            )
            ->where('user_id', $userId)
            ->where('transaction_date', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(fn ($item) => [
                'month'   => $item->month,
                'income'  => number_format((float) $item->income, 2, '.', ''),
                'expense' => number_format((float) $item->expense, 2, '.', ''),
            ]);

        return response()->json([
            'data' => new DashboardResource([
                'total_income'  => number_format((float) $totalIncome, 2, '.', ''),
                'total_expense' => number_format((float) $totalExpense, 2, '.', ''),
                'balance'       => number_format((float) $balance, 2, '.', ''),
                'monthly_chart' => $monthlyChart,
            ]),
        ], 200);
    }
}
