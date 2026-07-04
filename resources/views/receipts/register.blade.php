<x-layouts.app title="Receipt Register">
    <section class="workspace-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Receipt Module</p>
                <h1>Receipt Register</h1>
            </div>
            <div class="panel-actions">
                <span class="status-pill">{{ number_format($totalReceipts) }} Records</span>
                <a class="secondary-button" href="{{ route('receipts.export.excel', request()->query()) }}">Excel</a>
                <a class="secondary-button" href="{{ route('receipts.export.pdf', request()->query()) }}" target="_blank">PDF</a>
                <form method="POST" action="{{ route('receipts.destroy-all') }}" data-confirm="Delete all receipt records? This action cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button class="danger-button" type="submit">Delete All</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="notice-banner">{{ session('status') }}</div>
        @endif

        <section class="total-strip" aria-label="Receipt amount totals">
            <article>
                <span>Amount</span>
                <strong>{{ number_format($totals['amount'] ?? 0, 2) }}</strong>
            </article>
        </section>

        <form method="GET" action="{{ route('receipts.register') }}" class="filter-panel">
            <label class="use-code-field">
                <span>Use Code</span>
                <input value="{{ auth()->user()->user_code }}" disabled>
            </label>

            <label class="search-field">
                <span>Search</span>
                <input name="search" value="{{ request('search') }}" placeholder="Receipt, customer, city, state, amount, date, remark">
            </label>

            <div class="filter-actions">
                <button class="primary-button small" type="submit">Search</button>
                <a class="secondary-button" href="{{ route('receipts.register') }}">Clear</a>
            </div>
        </form>

        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>Use Code</th>
                        <th>Receipt No</th>
                        <th>Party Name</th>
                        <th>Tran Date</th>
                        <th>Amount</th>
                        <th>Remark</th>
                        <th class="action-cell">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                    <tr>
                        <td>{{ $receipt->user_code }}</td>
                        <td>{{ $receipt->voucher_no }}</td>
                        <td>{{ $receipt->account }}</td>
                        <td>{{ $receipt->tran_date?->format('d-m-Y') }}</td>
                        <td>{{ $receipt->amount }}</td>
                        <td>{{ $receipt->remark }}</td>
                        <td class="action-cell">
                            <div class="row-actions">
                                <a class="mini-button" href="{{ route('receipts.edit', $receipt) }}">Edit</a>
                                <form method="POST" action="{{ route('receipts.destroy', $receipt) }}" data-confirm="Delete receipt {{ $receipt->voucher_no ?: $receipt->id }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="mini-button danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state compact">
                                <strong>No receipt data received</strong>
                                <p>Receipt records will populate here after the VFP software posts data to the API.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-row">
            <div class="page-summary">
                Page {{ $receipts->currentPage() }} of {{ $receipts->lastPage() }} - {{ $receipts->total() }} filtered records
            </div>
            <div class="pager-buttons">
                @if ($receipts->onFirstPage())
                    <span class="pager-button disabled">Previous</span>
                @else
                    <a class="pager-button" href="{{ $receipts->previousPageUrl() }}">Previous</a>
                @endif

                @if ($receipts->hasMorePages())
                    <a class="pager-button" href="{{ $receipts->nextPageUrl() }}">Next</a>
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
