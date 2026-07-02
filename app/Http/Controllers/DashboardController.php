<?php

namespace App\Http\Controllers;

use App\Models\SaleRegister;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $saleRecords = SaleRegister::count();
        $latestSale = SaleRegister::latest('id')->first();

        return view('dashboard.index', [
            'totalImports' => $saleRecords,
            'saleRecords' => $saleRecords,
            'syncStatus' => $saleRecords > 0 ? 'Synced' : 'Idle',
            'syncMessage' => $latestSale
                ? 'Latest record received ' . $latestSale->created_at?->diffForHumans()
                : 'No background transfer running',
            'latestSale' => $latestSale,
            'latestSales' => SaleRegister::latest('id')->take(5)->get(),
        ]);
    }
}
