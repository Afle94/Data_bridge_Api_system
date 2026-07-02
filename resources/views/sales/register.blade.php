<x-layouts.app title="Sale Register">
    <section class="workspace-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Sales Module</p>
                <h1>Sale Register</h1>
            </div>
            <div class="panel-actions">
                <span class="status-pill">{{ number_format($totalSales) }} Records</span>
                <a class="secondary-button" href="{{ route('sales.export.excel', request()->query()) }}">Excel</a>
                <a class="secondary-button" href="{{ route('sales.export.pdf', request()->query()) }}" target="_blank">PDF</a>
                <form method="POST" action="{{ route('sales.destroy-all') }}" data-confirm="Delete all sale records? This action cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button class="danger-button" type="submit">Delete All</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="notice-banner">{{ session('status') }}</div>
        @endif

        <section class="total-strip" aria-label="Sale amount totals">
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

        <form method="GET" action="{{ route('sales.register') }}" class="filter-panel">
            <label class="search-field">
                <span>Search</span>
                <input name="search" value="{{ request('search') }}" placeholder="Invoice, party, city, state, amount, date, remark">
            </label>

            <div class="filter-actions">
                <button class="primary-button small" type="submit">Search</button>
                <a class="secondary-button" href="{{ route('sales.register') }}">Clear</a>
            </div>
        </form>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Tran Date</th>
                        <th>Rec Date</th>
                        <th>Party Name</th>
                        <th>Add1</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Taxable Amount</th>
                        <th>CGST Amt</th>
                        <th>IGST Amt</th>
                        <th>SGST Amt</th>
                        <th>Add Total</th>
                        <th>Less Total</th>
                        <th>Net Amount</th>
                        <th>Remark</th>
                        <th class="action-cell">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->voucher_no }}</td>
                            <td>{{ $sale->tran_date?->format('d-m-Y') }}</td>
                            <td>{{ $sale->rec_date?->format('d-m-Y') }}</td>
                            <td>{{ $sale->account }}</td>
                            <td>{{ $sale->add1 }}</td>
                            <td>{{ $sale->city }}</td>
                            <td>{{ $sale->state }}</td>
                            <td>{{ $sale->taxable ?? $sale->amount }}</td>
                            <td>{{ $sale->cgst_amt }}</td>
                            <td>{{ $sale->igst_amt }}</td>
                            <td>{{ $sale->sgst_amt }}</td>
                            <td>{{ $sale->add_total }}</td>
                            <td>{{ $sale->less_total }}</td>
                            <td>{{ $sale->net_amount }}</td>
                            <td>{{ $sale->remark }}</td>
                            <td class="action-cell">
                                <div class="row-actions">
                                    <a class="mini-button" href="{{ route('sales.edit', $sale) }}">Edit</a>
                                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" data-confirm="Delete invoice {{ $sale->voucher_no ?: $sale->id }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="mini-button danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="16">
                            <div class="empty-state compact">
                                <strong>No sale data received</strong>
                                <p>Sale records will populate here after the VFP API logic is added.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-row">
            <div class="page-summary">
                Page {{ $sales->currentPage() }} of {{ $sales->lastPage() }} - {{ $sales->total() }} filtered records
            </div>
            <div class="pager-buttons">
                @if ($sales->onFirstPage())
                    <span class="pager-button disabled">Previous</span>
                @else
                    <a class="pager-button" href="{{ $sales->previousPageUrl() }}">Previous</a>
                @endif

                @if ($sales->hasMorePages())
                    <a class="pager-button" href="{{ $sales->nextPageUrl() }}">Next</a>
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
