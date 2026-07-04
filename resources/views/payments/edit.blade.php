<x-layouts.app title="Edit Payment">
    <section class="workspace-panel">
        <div class="panel-heading">
            <div><p class="eyebrow">Payment Module</p><h1>Edit Payment Record</h1></div>
            <a class="secondary-button" href="{{ route('payments.register') }}">Back</a>
        </div>
        <form method="POST" action="{{ route('payments.update', $payment) }}" class="edit-grid">
            @csrf
            @method('PUT')
            <label><span>Use Code</span><input value="{{ $payment->user_code }}" disabled></label>
            <label><span>Transaction No</span><input name="voucher_no" value="{{ old('voucher_no', $payment->voucher_no) }}"></label>
            <label><span>Party Name</span><input name="account" value="{{ old('account', $payment->account) }}"></label>
            <label><span>Tran Date</span><input type="date" name="tran_date" value="{{ old('tran_date', $payment->tran_date?->format('Y-m-d')) }}"></label>
            <label><span>Amount</span><input type="number" step="0.01" name="amount" value="{{ old('amount', $payment->amount) }}"></label>
            <label class="wide-field"><span>Remark</span><textarea name="remark" rows="4">{{ old('remark', $payment->remark) }}</textarea></label>
            <div class="form-actions wide-field">
                <button class="primary-button" type="submit">Save Changes</button>
                <a class="secondary-button" href="{{ route('payments.register') }}">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
