<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesRegisterRecords;
use App\Models\ReceiptRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptRegisterController extends Controller
{
    use ManagesRegisterRecords;

    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);
        $totalsQuery = clone $query;

        return view('receipts.register', [
            'receipts' => $query->latest('id')->paginate(50)->withQueryString(),
            'totalReceipts' => ReceiptRegister::forUserCode($this->currentUserCode())->count(),
            'totals' => [
                'amount' => (clone $totalsQuery)->sum('amount'),
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
        $query = ReceiptRegister::forUserCode($this->currentUserCode());

        $query->when($request->filled('search'), function ($query) use ($request): void {
            $search = '%' . $request->string('search')->trim() . '%';

            $query->where(function ($query) use ($search): void {
                $query->where('voucher_no', 'like', $search)
                    ->orWhere('user_code', 'like', $search)
                    ->orWhere('invoice', 'like', $search)
                    ->orWhere('account', 'like', $search)
                    ->orWhere('db_acno', 'like', $search)
                    ->orWhere('cr_acno', 'like', $search)
                    ->orWhere('cheque_no', 'like', $search)
                    ->orWhere('chq_no', 'like', $search)
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

    public function edit(ReceiptRegister $receiptRegister): View
    {
        $this->ensureUserCanAccess($receiptRegister);

        return view('receipts.edit', ['receipt' => $receiptRegister]);
    }

    public function update(Request $request, ReceiptRegister $receiptRegister): RedirectResponse
    {
        return $this->updateRecord($request, $receiptRegister, $this->formRules());
    }

    public function destroy(ReceiptRegister $receiptRegister): RedirectResponse
    {
        return $this->destroyRecord($receiptRegister);
    }

    protected function modelClass(): string
    {
        return ReceiptRegister::class;
    }

    protected function registerRoute(): string
    {
        return 'receipts.register';
    }

    protected function registerSlug(): string
    {
        return 'receipt-register';
    }

    protected function registerTitle(): string
    {
        return 'Receipt Register';
    }

    protected function exportColumns(): array
    {
        return [
            ['label' => 'Use Code', 'key' => 'user_code'],
            ['label' => 'Receipt No', 'key' => 'voucher_no'],
            ['label' => 'Party Name', 'key' => 'account'],
            ['label' => 'Tran Date', 'key' => 'tran_date'],
            ['label' => 'Amount', 'key' => 'amount'],
            ['label' => 'Remark', 'key' => 'remark'],
        ];
    }

    private function formRules(): array
    {
        return [
            'voucher_no' => ['nullable', 'string', 'max:255'],
            'account' => ['nullable', 'string', 'max:255'],
            'tran_date' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric'],
            'remark' => ['nullable', 'string'],
        ];
    }
}
