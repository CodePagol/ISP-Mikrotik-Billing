<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionSummary;
use App\Models\HotspotSale;
use App\Models\IspExpense;
use App\Models\ResellerCommission;
use Carbon\Carbon;

class ProfitSummaryController extends Controller
{
    public function index()
    {
        $month = (int) request('month', now()->month);
        $year  = (int) request('year',  now()->year);

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        // ── Revenue ───────────────────────────────────────────────
        $collectionRevenue = CollectionSummary::whereBetween('collection_date', [$from, $to])
            ->sum('collection_amount');

        $hotspotRevenue = HotspotSale::whereBetween('sale_date', [$from, $to])
            ->sum('amount');

        $totalRevenue = $collectionRevenue + $hotspotRevenue;

        // ── Commissions (cost) ────────────────────────────────────
        $resellerCommissions = ResellerCommission::whereBetween('created_at', [$from, $to])
            ->sum('amount');

        // ── Expenses by category ──────────────────────────────────
        $expensesByCategory = IspExpense::whereBetween('expense_date', [$from, $to])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $totalExpenses = $expensesByCategory->sum();

        // ── Net Profit ────────────────────────────────────────────
        $netProfit = $totalRevenue - $resellerCommissions - $totalExpenses;

        // ── Detailed Lists ────────────────────────────────────────
        $itemPurchases = IspExpense::whereBetween('expense_date', [$from, $to])
            ->where('category', 'item_purchase')
            ->orderBy('expense_date', 'desc')
            ->get();

        $employeeSalaries = IspExpense::whereBetween('expense_date', [$from, $to])
            ->where('category', 'employee_salary')
            ->orderBy('expense_date', 'desc')
            ->get();

        // ── Year-over-year chart data (last 12 months) ────────────
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        // 1. Collections sum by Year-Month
        $collectionsByMonth = CollectionSummary::whereBetween('collection_date', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(collection_date, '%Y-%m') as month_year, SUM(collection_amount) as total")
            ->groupBy('month_year')
            ->pluck('total', 'month_year')
            ->toArray();

        // 2. HotspotSales sum by Year-Month
        $hotspotByMonth = HotspotSale::whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') as month_year, SUM(amount) as total")
            ->groupBy('month_year')
            ->pluck('total', 'month_year')
            ->toArray();

        // 3. Expenses sum by Year-Month
        $expensesByMonth = IspExpense::whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month_year, SUM(amount) as total")
            ->groupBy('month_year')
            ->pluck('total', 'month_year')
            ->toArray();

        // 4. ResellerCommissions sum by Year-Month
        $commissionsByMonth = ResellerCommission::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_year, SUM(amount) as total")
            ->groupBy('month_year')
            ->pluck('total', 'month_year')
            ->toArray();

        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key  = $date->format('Y-m');

            $mRevenue = ($collectionsByMonth[$key] ?? 0) + ($hotspotByMonth[$key] ?? 0);
            $mExpense = ($expensesByMonth[$key] ?? 0) + ($commissionsByMonth[$key] ?? 0);

            $chartData[] = [
                'label'   => $date->format('M Y'),
                'revenue' => round($mRevenue, 2),
                'expense' => round($mExpense, 2),
                'profit'  => round($mRevenue - $mExpense, 2),
            ];
        }

        $categories  = IspExpense::$categories;
        $months      = [
            1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
            7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December',
        ];
        $years = range(now()->year, now()->year - 4);

        return view('admin.profit-summary', compact(
            'month', 'year',
            'totalRevenue', 'collectionRevenue', 'hotspotRevenue',
            'resellerCommissions',
            'expensesByCategory', 'totalExpenses',
            'netProfit',
            'chartData',
            'categories', 'months', 'years',
            'itemPurchases', 'employeeSalaries'
        ));
    }
}
