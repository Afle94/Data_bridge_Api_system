<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Calibri, Arial, sans-serif; }
        .title { background: #07111f; color: #ffffff; font-size: 22px; font-weight: 700; height: 34px; }
        .subtitle { background: #e0f2fe; color: #0f172a; font-size: 11px; }
        .total-label { background: #0f766e; color: #ffffff; font-weight: 700; text-align: center; }
        .total-value { background: #f0fdfa; color: #0f172a; font-weight: 700; text-align: right; }
        .head { background: #12323c; color: #ffffff; font-weight: 700; text-align: center; border: 1px solid #94a3b8; }
        .cell { border: 1px solid #cbd5e1; vertical-align: top; }
        .money { border: 1px solid #cbd5e1; mso-number-format:"0.00"; text-align: right; }
        .date { border: 1px solid #cbd5e1; text-align: center; }
        .text { border: 1px solid #cbd5e1; }
        .zebra { background: #f8fafc; }
    </style>
</head>
<body>
<table>
    <colgroup>
        <col style="width: 90px">
        <col style="width: 85px">
        <col style="width: 85px">
        <col style="width: 260px">
        <col style="width: 250px">
        <col style="width: 120px">
        <col style="width: 130px">
        <col style="width: 115px">
        <col style="width: 95px">
        <col style="width: 95px">
        <col style="width: 95px">
        <col style="width: 95px">
        <col style="width: 95px">
        <col style="width: 110px">
        <col style="width: 220px">
    </colgroup>
    <tr>
        <td class="title" colspan="15">Sale Register</td>
    </tr>
    <tr>
        <td class="subtitle" colspan="15">
            DataBridge VFP Sync Report | Generated: {{ $generatedAt }}
            @if ($search)
                | Search: {{ $search }}
            @endif
        </td>
    </tr>
    <tr><td colspan="15"></td></tr>
    <tr>
        <td class="total-label" colspan="2">Taxable Amount</td>
        <td class="total-label" colspan="2">CGST Amt</td>
        <td class="total-label" colspan="2">IGST Amt</td>
        <td class="total-label" colspan="2">SGST Amt</td>
        <td class="total-label" colspan="2">Add Total</td>
        <td class="total-label" colspan="2">Less Total</td>
        <td class="total-label" colspan="3">Net Amount</td>
    </tr>
    <tr>
        <td class="total-value" colspan="2">{{ number_format($totals['taxable'] ?? 0, 2, '.', '') }}</td>
        <td class="total-value" colspan="2">{{ number_format($totals['cgst_amt'] ?? 0, 2, '.', '') }}</td>
        <td class="total-value" colspan="2">{{ number_format($totals['igst_amt'] ?? 0, 2, '.', '') }}</td>
        <td class="total-value" colspan="2">{{ number_format($totals['sgst_amt'] ?? 0, 2, '.', '') }}</td>
        <td class="total-value" colspan="2">{{ number_format($totals['add_total'] ?? 0, 2, '.', '') }}</td>
        <td class="total-value" colspan="2">{{ number_format($totals['less_total'] ?? 0, 2, '.', '') }}</td>
        <td class="total-value" colspan="3">{{ number_format($totals['net_amount'] ?? 0, 2, '.', '') }}</td>
    </tr>
    <tr><td colspan="15"></td></tr>
    <thead>
        <tr>
            <th class="head">Invoice No.</th>
            <th class="head">Tran Date</th>
            <th class="head">Rec Date</th>
            <th class="head">Party Name</th>
            <th class="head">Add1</th>
            <th class="head">City</th>
            <th class="head">State</th>
            <th class="head">Taxable Amount</th>
            <th class="head">CGST Amt</th>
            <th class="head">IGST Amt</th>
            <th class="head">SGST Amt</th>
            <th class="head">Add Total</th>
            <th class="head">Less Total</th>
            <th class="head">Net Amount</th>
            <th class="head">Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $sale)
            <tr class="{{ $loop->even ? 'zebra' : '' }}">
                <td class="text">{{ $sale->voucher_no }}</td>
                <td class="date">{{ $sale->tran_date?->format('d-m-Y') }}</td>
                <td class="date">{{ $sale->rec_date?->format('d-m-Y') }}</td>
                <td class="text">{{ $sale->account }}</td>
                <td class="text">{{ $sale->add1 }}</td>
                <td class="text">{{ $sale->city }}</td>
                <td class="text">{{ $sale->state }}</td>
                <td class="money">{{ $sale->taxable ?? $sale->amount }}</td>
                <td class="money">{{ $sale->cgst_amt }}</td>
                <td class="money">{{ $sale->igst_amt }}</td>
                <td class="money">{{ $sale->sgst_amt }}</td>
                <td class="money">{{ $sale->add_total }}</td>
                <td class="money">{{ $sale->less_total }}</td>
                <td class="money">{{ $sale->net_amount }}</td>
                <td class="text">{{ $sale->remark }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th class="head" colspan="7">Grand Total</th>
            <th class="total-value">{{ number_format($totals['taxable'] ?? 0, 2, '.', '') }}</th>
            <th class="total-value">{{ number_format($totals['cgst_amt'] ?? 0, 2, '.', '') }}</th>
            <th class="total-value">{{ number_format($totals['igst_amt'] ?? 0, 2, '.', '') }}</th>
            <th class="total-value">{{ number_format($totals['sgst_amt'] ?? 0, 2, '.', '') }}</th>
            <th class="total-value">{{ number_format($totals['add_total'] ?? 0, 2, '.', '') }}</th>
            <th class="total-value">{{ number_format($totals['less_total'] ?? 0, 2, '.', '') }}</th>
            <th class="total-value">{{ number_format($totals['net_amount'] ?? 0, 2, '.', '') }}</th>
            <th class="head"></th>
        </tr>
    </tfoot>
</table>
</body>
</html>
