<x-layouts.app title="Edit Receipt">
    <section class="workspace-panel">
        <div class="panel-heading">
            <div><p class="eyebrow">Receipt Module</p><h1>Edit Receipt Record</h1></div>
            <a class="secondary-button" href="{{ route('receipts.register') }}">Back</a>
        </div>
        <form method="POST" action="{{ route('receipts.update', $receipt) }}" class="edit-grid">
            @csrf
            @method('PUT')
            <label><span>Use Code</span><input value="{{ $receipt->user_code }}" disabled></label>
            <label><span>Receipt No</span><input name="voucher_no" value="{{ old('voucher_no', $receipt->voucher_no) }}"></label>
            <label><span>Party Name</span><input name="account" value="{{ old('account', $receipt->account) }}"></label>
            <label><span>Tran Date</span><input type="date" name="tran_date" value="{{ old('tran_date', $receipt->tran_date?->format('Y-m-d')) }}"></label>
            <label><span>Amount</span><input type="number" step="0.01" name="amount" value="{{ old('amount', $receipt->amount) }}"></label>
            <label class="wide-field"><span>Remark</span><textarea name="remark" rows="4">{{ old('remark', $receipt->remark) }}</textarea></label>
            <div class="form-actions wide-field">
                <button class="primary-button" type="submit">Save Changes</button>
                <a class="secondary-button" href="{{ route('receipts.register') }}">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
