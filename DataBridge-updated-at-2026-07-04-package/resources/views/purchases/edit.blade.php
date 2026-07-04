<x-layouts.app title="Edit Purchase">
    <section class="workspace-panel">
        <div class="panel-heading">
            <div><p class="eyebrow">Purchase Module</p><h1>Edit Purchase Record</h1></div>
            <a class="secondary-button" href="{{ route('purchases.register') }}">Back</a>
        </div>
        <form method="POST" action="{{ route('purchases.update', $purchase) }}" class="edit-grid">
            @csrf
            @method('PUT')
            <label><span>Use Code</span><input value="{{ $purchase->user_code }}" disabled></label>
            <label><span>Ref No</span><input name="voucher_no" value="{{ old('voucher_no', $purchase->voucher_no) }}"></label>
            <label><span>Invoice No</span><input name="invoice" value="{{ old('invoice', $purchase->invoice) }}"></label>
            <label><span>Party Name</span><input name="account" value="{{ old('account', $purchase->account) }}"></label>
            <label><span>Tran Date</span><input type="date" name="tran_date" value="{{ old('tran_date', $purchase->tran_date?->format('Y-m-d')) }}"></label>
            <label><span>Taxable Amount</span><input type="number" step="0.01" name="taxable" value="{{ old('taxable', $purchase->taxable ?? $purchase->amount) }}"></label>
            <label><span>CGST Amt</span><input type="number" step="0.01" name="cgst_amt" value="{{ old('cgst_amt', $purchase->cgst_amt) }}"></label>
            <label><span>IGST Amt</span><input type="number" step="0.01" name="igst_amt" value="{{ old('igst_amt', $purchase->igst_amt) }}"></label>
            <label><span>SGST Amt</span><input type="number" step="0.01" name="sgst_amt" value="{{ old('sgst_amt', $purchase->sgst_amt) }}"></label>
            <label><span>Net Amount</span><input type="number" step="0.01" name="net_amount" value="{{ old('net_amount', $purchase->net_amount) }}"></label>
            <label class="wide-field"><span>Remark</span><textarea name="remark" rows="4">{{ old('remark', $purchase->remark) }}</textarea></label>
            <div class="form-actions wide-field">
                <button class="primary-button" type="submit">Save Changes</button>
                <a class="secondary-button" href="{{ route('purchases.register') }}">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
