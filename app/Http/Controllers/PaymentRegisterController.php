<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesRegisterRecords;
use App\Models\PaymentRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentRegisterController extends Controller
{
    use ManagesRegisterRecords;

    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);
        $totalsQuery = clone $query;

        return view('payments.register', [
            'payments' => $query->latest('id')->paginate(50)->withQueryString(),
            'totalPayments' => PaymentRegister::forUserCode($this->currentUserCode())->count(),
            'totals' => [
                'amount' => (clone $totalsQuery)->sum('amount'),
            ],
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = PaymentRegister::forUserCode($this->currentUserCode());

        $query->when($request->filled('search'), function ($query) use ($request): void {
            $search = '%' . $request->string('search')->trim() . '%';

            $query->where(function ($query) use ($search): void {
                $query->where('voucher_no', 'like', $search)
                    ->orWhere('user_code', 'like', $search)
                    ->orWhere('account', 'like', $search)
                    ->orWhere('db_acno', 'like', $search)
                    ->orWhere('cr_acno', 'like', $search)
                    ->orWhere('cheque_no', 'like', $search)
                    ->orWhere('chq_no', 'like', $search)
                    ->orWhere('remark', 'like', $search)
                    ->orWhere('tran_date', 'like', $search)
                    ->orWhere('amount', 'like', $search);
            });
        });

        return $query;
    }

    private function currentUserCode(): ?string
    {
        return auth()->user()?->user_code;
    }

    public function edit(PaymentRegister $paymentRegister): View
    {
        $this->ensureUserCanAccess($paymentRegister);

        return view('payments.edit', ['payment' => $paymentRegister]);
    }

    public function update(Request $request, PaymentRegister $paymentRegister): RedirectResponse
    {
        return $this->updateRecord($request, $paymentRegister, $this->formRules());
    }

    public function destroy(PaymentRegister $paymentRegister): RedirectResponse
    {
        return $this->destroyRecord($paymentRegister);
    }

    protected function modelClass(): string
    {
        return PaymentRegister::class;
    }

    protected function registerRoute(): string
    {
        return 'payments.register';
    }

    protected function registerSlug(): string
    {
        return 'payment-register';
    }

    protected function registerTitle(): string
    {
        return 'Payment Register';
    }

    protected function exportColumns(): array
    {
        return [
            ['label' => 'Use Code', 'key' => 'user_code'],
            ['label' => 'Transaction No', 'key' => 'voucher_no'],
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
