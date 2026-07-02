<x-layouts.app title="Edit Sale">
    <section class="workspace-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Sales Module</p>
                <h1>Edit Sale Record</h1>
            </div>
            <a class="secondary-button" href="{{ route('sales.register') }}">Back</a>
        </div>

        <form method="POST" action="{{ route('sales.update', $sale) }}" class="edit-grid">
            @csrf
            @method('PUT')

            <label>
                <span>Invoice No.</span>
                <input name="voucher_no" value="{{ old('voucher_no', $sale->voucher_no) }}">
                @error('voucher_no') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Tran Date</span>
                <input type="date" name="tran_date" value="{{ old('tran_date', $sale->tran_date?->format('Y-m-d')) }}">
                @error('tran_date') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Rec Date</span>
                <input type="date" name="rec_date" value="{{ old('rec_date', $sale->rec_date?->format('Y-m-d')) }}">
                @error('rec_date') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Party Name</span>
                <input name="account" value="{{ old('account', $sale->account) }}">
                @error('account') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Add1</span>
                <input name="add1" value="{{ old('add1', $sale->add1) }}">
                @error('add1') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>City</span>
                <input name="city" value="{{ old('city', $sale->city) }}">
                @error('city') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>State</span>
                <input name="state" value="{{ old('state', $sale->state) }}">
                @error('state') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Taxable Amount</span>
                <input type="number" step="0.01" name="taxable" value="{{ old('taxable', $sale->taxable ?? $sale->amount) }}">
                @error('taxable') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>CGST Amt</span>
                <input type="number" step="0.01" name="cgst_amt" value="{{ old('cgst_amt', $sale->cgst_amt) }}">
                @error('cgst_amt') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>IGST Amt</span>
                <input type="number" step="0.01" name="igst_amt" value="{{ old('igst_amt', $sale->igst_amt) }}">
                @error('igst_amt') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>SGST Amt</span>
                <input type="number" step="0.01" name="sgst_amt" value="{{ old('sgst_amt', $sale->sgst_amt) }}">
                @error('sgst_amt') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Add Total</span>
                <input type="number" step="0.01" name="add_total" value="{{ old('add_total', $sale->add_total) }}">
                @error('add_total') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Less Total</span>
                <input type="number" step="0.01" name="less_total" value="{{ old('less_total', $sale->less_total) }}">
                @error('less_total') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Net Amount</span>
                <input type="number" step="0.01" name="net_amount" value="{{ old('net_amount', $sale->net_amount) }}">
                @error('net_amount') <small>{{ $message }}</small> @enderror
            </label>

            <label class="wide-field">
                <span>Remark</span>
                <textarea name="remark" rows="4">{{ old('remark', $sale->remark) }}</textarea>
                @error('remark') <small>{{ $message }}</small> @enderror
            </label>

            <div class="form-actions wide-field">
                <button class="primary-button" type="submit">Save Changes</button>
                <a class="secondary-button" href="{{ route('sales.register') }}">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
