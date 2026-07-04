<x-layouts.app title="Dashboard">
    <section class="hero-band">
        <div>
            <p class="eyebrow">Transfer Overview</p>
            <h1>The dashboard base is ready for records coming from VFP.</h1>
            <p>Once the API is connected, incoming sales, sync health, and the latest transfer batches will appear here.</p>
        </div>
        <img src="{{ asset('images/data-sync-dashboard.png') }}" alt="Data transfer dashboard illustration">
    </section>

    <section class="metric-grid">
        <article class="metric-card">
            <span>Total Imports</span>
            <strong>{{ number_format($totalImports) }}</strong>
            <p>{{ $latestSale ? 'Data received successfully' : 'Waiting for first API import' }}</p>
        </article>
        <article class="metric-card">
            <span>Sale Records</span>
            <strong>{{ number_format($saleRecords) }}</strong>
            <p>{{ $saleRecords > 0 ? 'Available in Sale Register' : 'Sale Register source ready' }}</p>
        </article>
        <article class="metric-card">
            <span>Sync Status</span>
            <strong>{{ $syncStatus }}</strong>
            <p>{{ $syncMessage }}</p>
        </article>
        <article class="metric-card">
            <span>Current User</span>
            <strong>{{ auth()->user()->user_code }}</strong>
            <p>{{ auth()->user()->email }}</p>
        </article>
    </section>

    <section class="workspace-panel">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">Next Integration Area</p>
                <h2>Incoming Data Queue</h2>
            </div>
            <span class="status-pill">{{ $saleRecords > 0 ? 'Data Received' : 'Waiting for API' }}</span>
        </div>

        @if ($latestSales->isNotEmpty())
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Use Code</th>
                            <th>Tran Date</th>
                            <th>Party Name</th>
                            <th>City</th>
                            <th>Net Amount</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestSales as $sale)
                            <tr>
                                <td>{{ $sale->voucher_no ?: $sale->invoice ?: $sale->id }}</td>
                                <td>{{ $sale->user_code ?: '-' }}</td>
                                <td>{{ $sale->tran_date?->format('d-m-Y') ?: '-' }}</td>
                                <td>{{ $sale->account ?: '-' }}</td>
                                <td>{{ $sale->city ?: '-' }}</td>
                                <td>{{ number_format((float) ($sale->net_amount ?? 0), 2) }}</td>
                                <td>{{ $sale->created_at?->format('d-m-Y h:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <strong>No transfer records yet</strong>
                <p>When the VFP software sends a payload, the latest batch summary and validation status can be shown here.</p>
            </div>
        @endif
    </section>
</x-layouts.app>
