<x-layouts.app title="Purchase Register">
    <section class="workspace-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Purchase Module</p>
                <h1>Purchase Register</h1>
            </div>
            <div class="panel-actions">
                <span class="status-pill">{{ number_format($totalPurchases) }} Records</span>
                <a class="secondary-button" href="{{ route('purchases.export.excel', request()->query()) }}">Excel</a>
                <a class="secondary-button" href="{{ route('purchases.export.pdf', request()->query()) }}" target="_blank">PDF</a>
                <form method="POST" action="{{ route('purchases.destroy-all') }}" data-confirm="Delete all purchase records? This action cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button class="danger-button" type="submit">Delete All</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="notice-banner">{{ session('status') }}</div>
        @endif

        <section class="total-strip" aria-label="Purchase amount totals">
            <article>
                <span>Taxable Amount</span>
                <strong>{{ number_format($totals['taxable'] ?? 0, 2) }}</strong>
            </article>
            <article>
                <span>CGST Amt</span>
                <strong>{{ number_format($totals['cgst_amt'] ?? 0, 2) }}</strong>
            </article>
            <article>
                <span>IGST Amt</span>
                <strong>{{ number_format($totals['igst_amt'] ?? 0, 2) }}</strong>
            </article>
            <article>
                <span>SGST Amt</span>
                <strong>{{ number_format($totals['sgst_amt'] ?? 0, 2) }}</strong>
            </article>
            <article>
                <span>Add Total</span>
                <strong>{{ number_format($totals['add_total'] ?? 0, 2) }}</strong>
            </article>
            <article>
                <span>Less Total</span>
                <strong>{{ number_format($totals['less_total'] ?? 0, 2) }}</strong>
            </article>
            <article>
                <span>Net Amount</span>
                <strong>{{ number_format($totals['net_amount'] ?? 0, 2) }}</strong>
            </article>
        </section>

        <form method="GET" action="{{ route('purchases.register') }}" class="filter-panel">
            <label class="use-code-field">
                <span>Use Code</span>
                <input value="{{ auth()->user()->user_code }}" disabled>
            </label>

            <label class="search-field">
                <span>Search</span>
                <input name="search" value="{{ request('search') }}" placeholder="Invoice, supplier, city, state, amount, date, remark">
            </label>

            <div class="filter-actions">
                <button class="primary-button small" type="submit">Search</button>
                <a class="secondary-button" href="{{ route('purchases.register') }}">Clear</a>
            </div>
        </form>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>Use Code</th>
                        <th>Ref No</th>
                        <th>Invoice No.</th>
                        <th>Party Name</th>
                        <th>Tran Date</th>
                        <th>Taxable Amount</th>
                        <th>CGST Amt</th>
                        <th>IGST Amt</th>
                        <th>SGST Amt</th>
                        <th>Net Amount</th>
                        <th>Remark</th>
                        <th class="action-cell">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->user_code }}</td>
                        <td>{{ $purchase->voucher_no }}</td>
                        <td>{{ $purchase->invoice }}</td>
                        <td>{{ $purchase->account }}</td>
                        <td>{{ $purchase->tran_date?->format('d-m-Y') }}</td>
                        <td>{{ $purchase->taxable ?? $purchase->amount }}</td>
                        <td>{{ $purchase->cgst_amt }}</td>
                        <td>{{ $purchase->igst_amt }}</td>
                        <td>{{ $purchase->sgst_amt }}</td>
                        <td>{{ $purchase->net_amount }}</td>
                        <td>{{ $purchase->remark }}</td>
                        <td class="action-cell">
                            <div class="row-actions">
                                <a class="mini-button" href="{{ route('purchases.edit', $purchase) }}">Edit</a>
                                <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" data-confirm="Delete purchase {{ $purchase->voucher_no ?: $purchase->id }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="mini-button danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12">
                            <div class="empty-state compact">
                                <strong>No purchase data received</strong>
                                <p>Purchase records will populate here after the VFP software posts data to the API.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-row">
            <div class="page-summary">
                Page {{ $purchases->currentPage() }} of {{ $purchases->lastPage() }} - {{ $purchases->total() }} filtered records
            </div>
            <div class="pager-buttons">
                @if ($purchases->onFirstPage())
                    <span class="pager-button disabled">Previous</span>
                @else
                    <a class="pager-button" href="{{ $purchases->previousPageUrl() }}">Previous</a>
                @endif

                @if ($purchases->hasMorePages())
                    <a class="pager-button" href="{{ $purchases->nextPageUrl() }}">Next</a>
                @else
                    <span class="pager-button disabled">Next</span>
                @endif
            </div>
        </div>
    </section>

    <div class="confirm-backdrop" data-confirm-modal hidden>
        <div class="confirm-box">
            <p class="eyebrow">Confirm Action</p>
            <h2>Delete record?</h2>
            <p data-confirm-message>Please confirm this action.</p>
            <div class="confirm-actions">
                <button class="secondary-button" type="button" data-confirm-cancel>Cancel</button>
                <button class="danger-button" type="button" data-confirm-ok>Delete</button>
            </div>
        </div>
    </div>
</x-layouts.app>
