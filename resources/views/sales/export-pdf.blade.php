<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sale Register PDF</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { color: #0f172a; font-family: Arial, sans-serif; margin: 0; }
        header { align-items: center; border-bottom: 2px solid #0f172a; display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 8px; }
        h1 { font-size: 18px; margin: 0; }
        p { color: #475569; font-size: 10px; margin: 3px 0 0; }
        .print-button { background: #0f766e; border: 0; border-radius: 6px; color: #fff; cursor: pointer; font-weight: 700; padding: 8px 12px; }
        .totals { display: grid; gap: 6px; grid-template-columns: repeat(7, 1fr); margin-bottom: 10px; }
        .totals div { border: 1px solid #cbd5e1; border-radius: 4px; padding: 5px; }
        .totals span { color: #64748b; display: block; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .totals strong { display: block; font-size: 10px; margin-top: 3px; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        th, td { border: 1px solid #cbd5e1; font-size: 7px; overflow-wrap: anywhere; padding: 3px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-weight: 700; text-transform: uppercase; }
        @media print { .print-button { display: none; } }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Sale Register</h1>
            <p>{{ $sales->count() }} records @if ($search) - Search: {{ $search }} @endif</p>
        </div>
        <button class="print-button" type="button" onclick="window.print()">Print / Save PDF</button>
    </header>

    <section class="totals">
        <div><span>Taxable</span><strong>{{ number_format($totals['taxable'] ?? 0, 2) }}</strong></div>
        <div><span>CGST</span><strong>{{ number_format($totals['cgst_amt'] ?? 0, 2) }}</strong></div>
        <div><span>IGST</span><strong>{{ number_format($totals['igst_amt'] ?? 0, 2) }}</strong></div>
        <div><span>SGST</span><strong>{{ number_format($totals['sgst_amt'] ?? 0, 2) }}</strong></div>
        <div><span>Add Total</span><strong>{{ number_format($totals['add_total'] ?? 0, 2) }}</strong></div>
        <div><span>Less Total</span><strong>{{ number_format($totals['less_total'] ?? 0, 2) }}</strong></div>
        <div><span>Net Amount</span><strong>{{ number_format($totals['net_amount'] ?? 0, 2) }}</strong></div>
    </section>

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
                <th>CGST</th>
                <th>IGST</th>
                <th>SGST</th>
                <th>Add Total</th>
                <th>Less Total</th>
                <th>Net Amount</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
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
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
