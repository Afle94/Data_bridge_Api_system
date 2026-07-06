<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesRegisterRecords;
use App\Models\PurchaseRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseRegisterController extends Controller
{
    use ManagesRegisterRecords;

    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);
        $totalsQuery = clone $query;

        return view('purchases.register', [
            'purchases' => $query->latest('id')->paginate(50)->withQueryString(),
            'totalPurchases' => PurchaseRegister::forUserCode($this->currentUserCode())->count(),
            'totals' => [
                'taxable' => (clone $totalsQuery)->sum('taxable'),
                'cgst_amt' => (clone $totalsQuery)->sum('cgst_amt'),
                'igst_amt' => (clone $totalsQuery)->sum('igst_amt'),
                'sgst_amt' => (clone $totalsQuery)->sum('sgst_amt'),
                'add_total' => (clone $totalsQuery)->sum('add_total'),
                'less_total' => (clone $totalsQuery)->sum('less_total'),
                'net_amount' => (clone $totalsQuery)->sum('net_amount'),
            ],
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = PurchaseRegister::forUserCode($this->currentUserCode());

        $query->when($request->filled('search'), function ($query) use ($request): void {
            $search = '%' . $request->string('search')->trim() . '%';

            $query->where(function ($query) use ($search): void {
                $query->where('voucher_no', 'like', $search)
                    ->orWhere('user_code', 'like', $search)
                    ->orWhere('invoice', 'like', $search)
                    ->orWhere('account', 'like', $search)
                    ->orWhere('add1', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhere('state', 'like', $search)
                    ->orWhere('remark', 'like', $search)
                    ->orWhere('tran_date', 'like', $search)
                    ->orWhere('rec_date', 'like', $search)
                    ->orWhere('taxable', 'like', $search)
                    ->orWhere('cgst_amt', 'like', $search)
                    ->orWhere('igst_amt', 'like', $search)
                    ->orWhere('sgst_amt', 'like', $search)
                    ->orWhere('net_amount', 'like', $search);
            });
        });

        return $query;
    }

    private function currentUserCode(): ?string
    {
        return auth()->user()?->user_code;
    }

    public function edit(PurchaseRegister $purchaseRegister): View
    {
        $this->ensureUserCanAccess($purchaseRegister);

        return view('purchases.edit', ['purchase' => $purchaseRegister]);
    }

    public function update(Request $request, PurchaseRegister $purchaseRegister): RedirectResponse
    {
        return $this->updateRecord($request, $purchaseRegister, $this->formRules());
    }

    public function destroy(PurchaseRegister $purchaseRegister): RedirectResponse
    {
        return $this->destroyRecord($purchaseRegister);
    }

    protected function modelClass(): string
    {
        return PurchaseRegister::class;
    }

    protected function registerRoute(): string
    {
        return 'purchases.register';
    }

    protected function registerSlug(): string
    {
        return 'purchase-register';
    }

    protected function registerTitle(): string
    {
        return 'Purchase Register';
    }

    protected function exportColumns(): array
    {
        return [
            ['label' => 'Use Code', 'key' => 'user_code'],
            ['label' => 'Ref No', 'key' => 'voucher_no'],
            ['label' => 'Invoice No', 'key' => 'invoice'],
            ['label' => 'Party Name', 'key' => 'account'],
            ['label' => 'Tran Date', 'key' => 'tran_date'],
            ['label' => 'Taxable Amount', 'key' => 'taxable'],
            ['label' => 'CGST Amt', 'key' => 'cgst_amt'],
            ['label' => 'IGST Amt', 'key' => 'igst_amt'],
            ['label' => 'SGST Amt', 'key' => 'sgst_amt'],
            ['label' => 'Net Amount', 'key' => 'net_amount'],
            ['label' => 'Remark', 'key' => 'remark'],
        ];
    }

    protected function exportSummaryColumns(): array
    {
        return [
            ['label' => 'Taxable Amount', 'key' => 'taxable'],
            ['label' => 'CGST Amt', 'key' => 'cgst_amt'],
            ['label' => 'IGST Amt', 'key' => 'igst_amt'],
            ['label' => 'SGST Amt', 'key' => 'sgst_amt'],
            ['label' => 'Add Total', 'key' => 'add_total'],
            ['label' => 'Less Total', 'key' => 'less_total'],
            ['label' => 'Net Amount', 'key' => 'net_amount'],
        ];
    }

    private function formRules(): array
    {
        return [
            'voucher_no' => ['nullable', 'string', 'max:255'],
            'invoice' => ['nullable', 'string', 'max:255'],
            'account' => ['nullable', 'string', 'max:255'],
            'tran_date' => ['nullable', 'date'],
            'taxable' => ['nullable', 'numeric'],
            'cgst_amt' => ['nullable', 'numeric'],
            'igst_amt' => ['nullable', 'numeric'],
            'sgst_amt' => ['nullable', 'numeric'],
            'net_amount' => ['nullable', 'numeric'],
            'remark' => ['nullable', 'string'],
        ];
    }
}
