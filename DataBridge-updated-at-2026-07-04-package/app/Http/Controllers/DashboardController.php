<?php

namespace App\Http\Controllers;

use App\Models\SaleRegister;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $userCode = auth()->user()->user_code;
        $saleQuery = SaleRegister::forUserCode($userCode);
        $saleRecords = (clone $saleQuery)->count();
        $latestSale = (clone $saleQuery)->latest('id')->first();

        return view('dashboard.index', [
            'totalImports' => $saleRecords,
            'saleRecords' => $saleRecords,
            'syncStatus' => $saleRecords > 0 ? 'Synced' : 'Idle',
            'syncMessage' => $latestSale
                ? 'Latest record received ' . $latestSale->created_at?->diffForHumans()
                : 'No background transfer running',
            'latestSale' => $latestSale,
            'latestSales' => (clone $saleQuery)->latest('id')->take(5)->get(),
        ]);
    }
}
