<?php

namespace App\Http\Controllers;

use App\Models\SaleRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SaleRegisterController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);
        $totalsQuery = clone $query;

        return view('sales.register', [
            'sales' => $query->latest('id')->paginate(50)->withQueryString(),
            'totalSales' => SaleRegister::forUserCode($this->currentUserCode())->count(),
            'totals' => [
                'taxable' => (clone $totalsQuery)->sum('taxable'),
                'cgst_amt' => (clone $totalsQuery)->sum('cgst_amt'),
                'igst_amt' => (clone $totalsQuery)->sum('igst_amt'),
                'sgst_amt' => (clone $totalsQuery)->sum('sgst_amt'),
                'add_total' => (clone $totalsQuery)->sum('add_total'),
                'less_total' => (clone $totalsQuery)->sum('less_total'),
                'net_amount' => (clone $totalsQuery)->sum('net_amount'),
            ],
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $sales = $this->filteredQuery($request)->latest('id')->get();

        return response($this->buildXlsx($sales, $this->totalsFor($sales), $request->string('search')->toString()), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="sale-register.xlsx"',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $sales = $this->filteredQuery($request)->latest('id')->get();

        return response($this->buildPdf($sales, $this->totalsFor($sales)), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sale-register.pdf"',
        ]);
    }

    public function pdfViewer(Request $request): View
    {
        return view('sales.pdf-viewer', [
            'pdfUrl' => route('sales.export.pdf-file', $request->query()),
        ]);
    }

    public function edit(SaleRegister $saleRegister): View
    {
        $this->ensureUserCanAccess($saleRegister);

        return view('sales.edit', [
            'sale' => $saleRegister,
        ]);
    }

    public function update(Request $request, SaleRegister $saleRegister): RedirectResponse
    {
        $this->ensureUserCanAccess($saleRegister);

        $validated = $request->validate([
            'voucher_no' => ['nullable', 'string', 'max:255'],
            'tran_date' => ['nullable', 'date'],
            'rec_date' => ['nullable', 'date'],
            'account' => ['nullable', 'string', 'max:255'],
            'add1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'taxable' => ['nullable', 'numeric'],
            'cgst_amt' => ['nullable', 'numeric'],
            'igst_amt' => ['nullable', 'numeric'],
            'sgst_amt' => ['nullable', 'numeric'],
            'add_total' => ['nullable', 'numeric'],
            'less_total' => ['nullable', 'numeric'],
            'net_amount' => ['nullable', 'numeric'],
            'remark' => ['nullable', 'string'],
        ]);

        $saleRegister->update($validated);

        return redirect()->route('sales.register')->with('status', 'Sale record updated.');
    }

    public function destroy(SaleRegister $saleRegister): RedirectResponse
    {
        $this->ensureUserCanAccess($saleRegister);

        $saleRegister->delete();

        return redirect()->route('sales.register')->with('status', 'Sale record deleted.');
    }

    public function destroyAll(): RedirectResponse
    {
        SaleRegister::forUserCode($this->currentUserCode())->delete();

        return redirect()->route('sales.register')->with('status', 'All sale records deleted.');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = SaleRegister::forUserCode($this->currentUserCode());

        $query->when($request->filled('search'), function ($query) use ($request): void {
            $search = '%' . $request->string('search')->trim() . '%';

            $query->where(function ($query) use ($search): void {
                $query->where('voucher_no', 'like', $search)
                    ->orWhere('user_code', 'like', $search)
                    ->orWhere('invoice', 'like', $search)
                    ->orWhere('account', 'like', $search)
                    ->orWhere('add1', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhere('state', 'like', $search)
                    ->orWhere('remark', 'like', $search)
                    ->orWhere('tran_date', 'like', $search)
                    ->orWhere('rec_date', 'like', $search)
                    ->orWhere('taxable', 'like', $search)
                    ->orWhere('cgst_amt', 'like', $search)
                    ->orWhere('igst_amt', 'like', $search)
                    ->orWhere('sgst_amt', 'like', $search)
                    ->orWhere('net_amount', 'like', $search);
            });
        });

        return $query;
    }

    private function currentUserCode(): ?string
    {
        return auth()->user()?->user_code;
    }

    private function ensureUserCanAccess(SaleRegister $saleRegister): void
    {
        abort_unless($saleRegister->user_code === $this->currentUserCode(), 403);
    }

    private function totalsFor($sales): array
    {
        return [
            'taxable' => $sales->sum('taxable'),
            'cgst_amt' => $sales->sum('cgst_amt'),
            'igst_amt' => $sales->sum('igst_amt'),
            'sgst_amt' => $sales->sum('sgst_amt'),
            'add_total' => $sales->sum('add_total'),
            'less_total' => $sales->sum('less_total'),
            'net_amount' => $sales->sum('net_amount'),
        ];
    }

    private function buildPdf($sales, array $totals): string
    {
        $columns = [
            ['User', 38],
            ['Invoice', 44],
            ['Tran', 34],
            ['Rec', 34],
            ['Party', 90],
            ['Add1', 65],
            ['City', 40],
            ['State', 45],
            ['Taxable', 46],
            ['CGST', 36],
            ['IGST', 36],
            ['SGST', 36],
            ['Add', 36],
            ['Less', 36],
            ['Net', 44],
            ['Remark', 58],
        ];

        $rows = $sales->map(fn (SaleRegister $sale): array => [
            $sale->user_code,
            $sale->voucher_no,
            $sale->tran_date?->format('d-m-y'),
            $sale->rec_date?->format('d-m-y'),
            $sale->account,
            $sale->add1,
            $sale->city,
            $sale->state,
            $this->money($sale->taxable ?? $sale->amount),
            $this->money($sale->cgst_amt),
            $this->money($sale->igst_amt),
            $this->money($sale->sgst_amt),
            $this->money($sale->add_total),
            $this->money($sale->less_total),
            $this->money($sale->net_amount),
            $sale->remark,
        ])->values()->all();

        $pages = array_chunk($rows, 27);
        $pages = $pages === [] ? [[]] : $pages;

        $objects = [];
        $pageObjectNumbers = [];
        $fontObjectNumber = 3;
        $pagesObjectNumber = 2;

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[$fontObjectNumber] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        $nextObject = 4;

        foreach ($pages as $pageIndex => $pageRows) {
            $contentObjectNumber = $nextObject++;
            $pageObjectNumber = $nextObject++;
            $pageObjectNumbers[] = $pageObjectNumber;

            $stream = $this->pdfPageStream($pageRows, $columns, $pageIndex + 1, count($pages), $totals);
            $objects[$contentObjectNumber] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
            $objects[$pageObjectNumber] = "<< /Type /Page /Parent {$pagesObjectNumber} 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 {$fontObjectNumber} 0 R >> >> /Contents {$contentObjectNumber} 0 R >>";
        }

        $kids = collect($pageObjectNumbers)->map(fn (int $number): string => "{$number} 0 R")->implode(' ');
        $objects[$pagesObjectNumber] = "<< /Type /Pages /Kids [{$kids}] /Count " . count($pageObjectNumbers) . ' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function pdfPageStream(array $rows, array $columns, int $page, int $totalPages, array $totals): string
    {
        $lines = [];
        $lines[] = '0.98 0.99 1 rg 0 0 842 595 re f';
        $lines[] = '0.03 0.07 0.12 rg 24 528 794 48 re f';
        $lines[] = '0.00 0.55 0.63 rg 24 528 6 48 re f';
        $lines[] = '1 1 1 rg BT /F1 18 Tf 40 553 Td ' . $this->pdfText('Sale Register') . ' Tj ET';
        $lines[] = '0.78 0.88 0.95 rg BT /F1 7 Tf 40 538 Td ' . $this->pdfText('DataBridge | VFP Sync Report | Generated ' . now()->format('d-m-Y h:i A')) . ' Tj ET';
        $lines[] = '1 1 1 rg BT /F1 8 Tf 735 550 Td ' . $this->pdfText("Page {$page} / {$totalPages}") . ' Tj ET';

        if ($page === 1) {
            $cards = [
                ['Taxable', $this->money($totals['taxable'])],
                ['CGST', $this->money($totals['cgst_amt'])],
                ['IGST', $this->money($totals['igst_amt'])],
                ['SGST', $this->money($totals['sgst_amt'])],
                ['Add Total', $this->money($totals['add_total'])],
                ['Less Total', $this->money($totals['less_total'])],
                ['Net Amount', $this->money($totals['net_amount'])],
            ];

            $cardX = 24;
            foreach ($cards as [$label, $value]) {
                $lines[] = "0.91 0.97 0.99 rg {$cardX} 490 108 32 re f";
                $lines[] = "0.67 0.84 0.90 RG {$cardX} 490 108 32 re S";
                $lines[] = "0.30 0.41 0.51 rg BT /F1 5 Tf " . ($cardX + 6) . " 511 Td " . $this->pdfText($label) . ' Tj ET';
                $lines[] = "0.02 0.10 0.18 rg BT /F1 8 Tf " . ($cardX + 6) . " 498 Td " . $this->pdfText($value) . ' Tj ET';
                $cardX += 114;
            }
        }

        $x = 24;
        $y = $page === 1 ? 468 : 510;
        $lines[] = '0.04 0.30 0.35 rg 24 ' . ($y - 7) . ' 794 18 re f';

        foreach ($columns as [$label, $width]) {
            $lines[] = "1 1 1 rg BT /F1 6 Tf {$x} {$y} Td " . $this->pdfText($label) . ' Tj ET';
            $x += $width;
        }

        $y -= 18;

        foreach ($rows as $rowIndex => $row) {
            $x = 24;
            if ($rowIndex % 2 === 0) {
                $lines[] = '1 1 1 rg 24 ' . ($y - 5) . ' 794 14 re f';
            } else {
                $lines[] = '0.96 0.98 1 rg 24 ' . ($y - 5) . ' 794 14 re f';
            }
            $lines[] = "0.82 0.88 0.94 RG 24 " . ($y - 5) . " 794 0.2 re S";

            foreach ($columns as $index => [, $width]) {
                $text = $this->fitText((string) ($row[$index] ?? ''), (int) floor($width / 3.6));
                $lines[] = "0.03 0.07 0.12 rg BT /F1 5 Tf {$x} {$y} Td " . $this->pdfText($text) . ' Tj ET';
                $x += $width;
            }

            $y -= 15;
        }

        return implode("\n", $lines);
    }

    private function pdfText(string $text): string
    {
        $text = str_replace(["\\", '(', ')', "\r", "\n"], ["\\\\", "\\(", "\\)", ' ', ' '], $text);

        return '(' . $text . ')';
    }

    private function fitText(string $text, int $limit): string
    {
        $text = trim($text);

        return strlen($text) <= $limit ? $text : substr($text, 0, max(0, $limit - 2)) . '..';
    }

    private function money(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 2, '.', '');
    }

    private function buildXlsx($sales, array $totals, string $search): string
    {
        $tempPath = tempnam(storage_path('framework/cache'), 'sale-register-');
        $zip = new \ZipArchive();
        $zip->open($tempPath, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRels());
        $zip->addFromString('docProps/app.xml', $this->xlsxAppProps());
        $zip->addFromString('docProps/core.xml', $this->xlsxCoreProps());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels());
        $zip->addFromString('xl/styles.xml', $this->xlsxStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxWorksheet($sales, $totals, $search));
        $zip->close();

        $contents = file_get_contents($tempPath);
        @unlink($tempPath);

        return $contents === false ? '' : $contents;
    }

    private function xlsxWorksheet($sales, array $totals, string $search): string
    {
        $rows = [];
        $rows[] = $this->xlsxRow(1, [
            ['Sale Register', 's', 1],
        ]);
        $subtitle = 'DataBridge VFP Sync Report | Generated: ' . now()->format('d-m-Y h:i A');
        $subtitle .= $search !== '' ? ' | Search: ' . $search : '';
        $rows[] = $this->xlsxRow(2, [
            [$subtitle, 's', 2],
        ]);
        $rows[] = $this->xlsxRow(4, [
            ['Taxable Amount', 's', 3], ['', 's', 3],
            ['CGST Amt', 's', 3], ['', 's', 3],
            ['IGST Amt', 's', 3], ['', 's', 3],
            ['SGST Amt', 's', 3], ['', 's', 3],
            ['Add Total', 's', 3], ['', 's', 3],
            ['Less Total', 's', 3], ['', 's', 3],
            ['Net Amount', 's', 3], ['', 's', 3], ['', 's', 3],
        ]);
        $rows[] = $this->xlsxRow(5, [
            [$totals['taxable'] ?? 0, 'n', 4], ['', 's', 4],
            [$totals['cgst_amt'] ?? 0, 'n', 4], ['', 's', 4],
            [$totals['igst_amt'] ?? 0, 'n', 4], ['', 's', 4],
            [$totals['sgst_amt'] ?? 0, 'n', 4], ['', 's', 4],
            [$totals['add_total'] ?? 0, 'n', 4], ['', 's', 4],
            [$totals['less_total'] ?? 0, 'n', 4], ['', 's', 4],
            [$totals['net_amount'] ?? 0, 'n', 4], ['', 's', 4], ['', 's', 4],
        ]);

        $headers = [
            'User Code', 'Invoice No.', 'Tran Date', 'Rec Date', 'Party Name', 'Add1', 'City', 'State',
            'Taxable Amount', 'CGST Amt', 'IGST Amt', 'SGST Amt', 'Add Total', 'Less Total',
            'Net Amount', 'Remark',
        ];
        $rows[] = $this->xlsxRow(7, collect($headers)->map(fn (string $header): array => [$header, 's', 5])->all());

        $rowNumber = 8;
        foreach ($sales as $sale) {
            $style = $rowNumber % 2 === 0 ? 6 : 7;
            $rows[] = $this->xlsxRow($rowNumber, [
                [$sale->user_code, 's', $style],
                [$sale->voucher_no, 's', $style],
                [$sale->tran_date?->format('d-m-Y'), 's', $style],
                [$sale->rec_date?->format('d-m-Y'), 's', $style],
                [$sale->account, 's', $style],
                [$sale->add1, 's', $style],
                [$sale->city, 's', $style],
                [$sale->state, 's', $style],
                [$sale->taxable ?? $sale->amount, 'n', $style + 2],
                [$sale->cgst_amt, 'n', $style + 2],
                [$sale->igst_amt, 'n', $style + 2],
                [$sale->sgst_amt, 'n', $style + 2],
                [$sale->add_total, 'n', $style + 2],
                [$sale->less_total, 'n', $style + 2],
                [$sale->net_amount, 'n', $style + 2],
                [$sale->remark, 's', $style],
            ]);
            $rowNumber++;
        }

        $rows[] = $this->xlsxRow($rowNumber + 1, [
            ['Grand Total', 's', 5], ['', 's', 5], ['', 's', 5], ['', 's', 5], ['', 's', 5], ['', 's', 5], ['', 's', 5], ['', 's', 5],
            [$totals['taxable'] ?? 0, 'n', 4],
            [$totals['cgst_amt'] ?? 0, 'n', 4],
            [$totals['igst_amt'] ?? 0, 'n', 4],
            [$totals['sgst_amt'] ?? 0, 'n', 4],
            [$totals['add_total'] ?? 0, 'n', 4],
            [$totals['less_total'] ?? 0, 'n', 4],
            [$totals['net_amount'] ?? 0, 'n', 4],
            ['', 's', 5],
        ]);

        $mergeRow = $rowNumber + 1;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="7" topLeftCell="A8" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>'
            . '<col min="1" max="2" width="13" customWidth="1"/><col min="3" max="4" width="12" customWidth="1"/>'
            . '<col min="5" max="6" width="34" customWidth="1"/><col min="7" max="8" width="18" customWidth="1"/>'
            . '<col min="9" max="15" width="14" customWidth="1"/><col min="16" max="16" width="32" customWidth="1"/>'
            . '</cols><sheetData>' . implode('', $rows) . '</sheetData>'
            . '<mergeCells count="10"><mergeCell ref="A1:P1"/><mergeCell ref="A2:P2"/><mergeCell ref="A4:B4"/><mergeCell ref="C4:D4"/><mergeCell ref="E4:F4"/><mergeCell ref="G4:H4"/><mergeCell ref="I4:J4"/><mergeCell ref="K4:L4"/><mergeCell ref="M4:P4"/><mergeCell ref="A' . $mergeRow . ':H' . $mergeRow . '"/></mergeCells>'
            . '</worksheet>';
    }

    private function xlsxRow(int $rowNumber, array $cells): string
    {
        $xml = '<row r="' . $rowNumber . '">';

        foreach ($cells as $index => [$value, $type, $style]) {
            $cell = $this->xlsxColumnName($index + 1) . $rowNumber;
            $xml .= $this->xlsxCell($cell, $value, $type, $style);
        }

        return $xml . '</row>';
    }

    private function xlsxCell(string $cell, mixed $value, string $type, int $style): string
    {
        if ($type === 'n' && $value !== null && $value !== '') {
            return '<c r="' . $cell . '" s="' . $style . '"><v>' . (float) $value . '</v></c>';
        }

        return '<c r="' . $cell . '" s="' . $style . '" t="inlineStr"><is><t>' . $this->xmlText((string) ($value ?? '')) . '</t></is></c>';
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function xmlText(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/></Types>';
    }

    private function xlsxRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>';
    }

    private function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sale Register" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function xlsxWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function xlsxAppProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"><Application>DataBridge</Application></Properties>';
    }

    private function xlsxCoreProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>Sale Register</dc:title><dc:creator>DataBridge</dc:creator></cp:coreProperties>';
    }

    private function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="1"><numFmt numFmtId="164" formatCode="0.00"/></numFmts><fonts count="4"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="22"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FF0F172A"/><name val="Calibri"/></font></fonts><fills count="7"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF07111F"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE0F2FE"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F766E"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF0FDFA"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="10"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center"/></xf><xf numFmtId="164" fontId="3" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyNumberFormat="1"><alignment horizontal="right"/></xf><xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyFill="1" applyBorder="1"/><xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyNumberFormat="1"><alignment horizontal="right"/></xf><xf numFmtId="164" fontId="0" fillId="6" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyNumberFormat="1"><alignment horizontal="right"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }
}
