<?php

namespace App\Http\Controllers\Concerns;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait ManagesRegisterRecords
{
    public function exportExcel(Request $request): Response
    {
        $records = $this->filteredQuery($request)->latest('id')->get();
        $columns = $this->exportColumns();

        return response($this->buildXlsx($records, $columns, $request->string('search')->toString()), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $this->registerSlug() . '.xlsx"',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $records = $this->filteredQuery($request)->latest('id')->get();
        $columns = $this->exportColumns();

        return response($this->buildPdf($records, $columns), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->registerSlug() . '.pdf"',
        ]);
    }

    public function destroyAll(): RedirectResponse
    {
        $this->modelClass()::forUserCode($this->currentUserCode())->delete();

        return redirect()->route($this->registerRoute())->with('status', 'All ' . strtolower($this->registerTitle()) . ' records deleted.');
    }

    protected function updateRecord(Request $request, Model $record, array $rules): RedirectResponse
    {
        $this->ensureUserCanAccess($record);
        $record->update($request->validate($rules));

        return redirect()->route($this->registerRoute())->with('status', $this->registerTitle() . ' record updated.');
    }

    protected function destroyRecord(Model $record): RedirectResponse
    {
        $this->ensureUserCanAccess($record);
        $record->delete();

        return redirect()->route($this->registerRoute())->with('status', $this->registerTitle() . ' record deleted.');
    }

    protected function ensureUserCanAccess(Model $record): void
    {
        abort_unless($record->user_code === $this->currentUserCode(), 403);
    }

    protected function currentUserCode(): ?string
    {
        return auth()->user()?->user_code;
    }

    private function buildPdf($records, array $columns): string
    {
        $pdfColumns = $this->pdfColumns($columns);
        $totals = $this->totalsFor($records, $this->totalColumns($columns));
        $summaryCards = $this->summaryCards($totals, $columns);
        $rows = $records->map(fn (Model $record): array => array_map(
            fn (array $column): string => $this->displayValue($record, $column),
            $columns
        ))->values()->all();
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

            $stream = $this->pdfPageStream($pageRows, $pdfColumns, $pageIndex + 1, count($pages), $summaryCards);
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

        return $pdf . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }

    private function pdfPageStream(array $rows, array $columns, int $page, int $totalPages, array $cards): string
    {
        $lines = [];
        $lines[] = '0.98 0.99 1 rg 0 0 842 595 re f';
        $lines[] = '0.03 0.07 0.12 rg 24 528 794 48 re f';
        $lines[] = '0.00 0.55 0.63 rg 24 528 6 48 re f';
        $lines[] = '1 1 1 rg BT /F1 18 Tf 40 553 Td ' . $this->pdfText($this->registerTitle()) . ' Tj ET';
        $lines[] = '0.78 0.88 0.95 rg BT /F1 7 Tf 40 538 Td ' . $this->pdfText('DataBridge | VFP Sync Report | Generated ' . now()->format('d-m-Y h:i A')) . ' Tj ET';
        $lines[] = '1 1 1 rg BT /F1 8 Tf 735 550 Td ' . $this->pdfText("Page {$page} / {$totalPages}") . ' Tj ET';

        if ($page === 1 && $cards !== []) {
            $cardWidth = min(108, 794 / max(1, count($cards)));
            $cardX = 24;

            foreach ($cards as [$label, $value]) {
                $lines[] = "0.91 0.97 0.99 rg {$cardX} 490 {$cardWidth} 32 re f";
                $lines[] = "0.67 0.84 0.90 RG {$cardX} 490 {$cardWidth} 32 re S";
                $lines[] = "0.30 0.41 0.51 rg BT /F1 5 Tf " . ($cardX + 6) . " 511 Td " . $this->pdfText($this->fitText($label, 14)) . ' Tj ET';
                $lines[] = "0.02 0.10 0.18 rg BT /F1 8 Tf " . ($cardX + 6) . " 498 Td " . $this->pdfText($this->fitText($value, 17)) . ' Tj ET';
                $cardX += $cardWidth + 6;
            }
        }

        $x = 24;
        $y = $page === 1 ? 468 : 510;
        $lines[] = '0.04 0.30 0.35 rg 24 ' . ($y - 7) . ' 794 18 re f';

        foreach ($columns as [$label, $width]) {
            $lines[] = "1 1 1 rg BT /F1 6 Tf {$x} {$y} Td " . $this->pdfText($this->fitText($label, (int) floor($width / 3.6))) . ' Tj ET';
            $x += $width;
        }

        $y -= 18;

        foreach ($rows as $rowIndex => $row) {
            $x = 24;
            $lines[] = ($rowIndex % 2 === 0 ? '1 1 1' : '0.96 0.98 1') . ' rg 24 ' . ($y - 5) . ' 794 14 re f';
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

    private function buildXlsx($records, array $columns, string $search): string
    {
        $tempPath = tempnam(storage_path('framework/cache'), $this->registerSlug() . '-');
        $zip = new \ZipArchive();
        $zip->open($tempPath, \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRels());
        $zip->addFromString('docProps/app.xml', $this->xlsxAppProps());
        $zip->addFromString('docProps/core.xml', $this->xlsxCoreProps());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels());
        $zip->addFromString('xl/styles.xml', $this->xlsxStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxWorksheet($records, $columns, $search));
        $zip->close();

        $contents = file_get_contents($tempPath);
        @unlink($tempPath);

        return $contents === false ? '' : $contents;
    }

    private function xlsxWorksheet($records, array $columns, string $search): string
    {
        $totals = $this->totalsFor($records, $this->totalColumns($columns));
        $lastColumn = $this->xlsxColumnName(count($columns));
        $summary = $this->summaryCards($totals, $columns);
        $rows = [];
        $rows[] = $this->xlsxRow(1, [[$this->registerTitle(), 's', 1]]);

        $subtitle = 'DataBridge VFP Sync Report | Generated: ' . now()->format('d-m-Y h:i A');
        $subtitle .= $search !== '' ? ' | Search: ' . $search : '';
        $rows[] = $this->xlsxRow(2, [[$subtitle, 's', 2]]);

        $summaryLabels = [];
        $summaryValues = [];
        foreach ($summary as [$label, $value, $key]) {
            $summaryLabels[] = [$label, 's', 3];
            $summaryValues[] = [$totals[$key] ?? 0, 'n', 4];
        }
        $rows[] = $this->xlsxRow(4, $summaryLabels);
        $rows[] = $this->xlsxRow(5, $summaryValues);

        $rows[] = $this->xlsxRow(7, array_map(fn (array $column): array => [$column['label'], 's', 5], $columns));

        $rowNumber = 8;
        foreach ($records as $record) {
            $style = $rowNumber % 2 === 0 ? 6 : 7;
            $rows[] = $this->xlsxRow($rowNumber, array_map(function (array $column) use ($record, $style): array {
                return [
                    $this->rawValue($record, $column),
                    $this->isNumericColumn($column) ? 'n' : 's',
                    $this->isNumericColumn($column) ? $style + 2 : $style,
                ];
            }, $columns));
            $rowNumber++;
        }

        $rows[] = $this->xlsxRow($rowNumber + 1, array_map(function (array $column, int $index) use ($totals): array {
            if ($index === 0) {
                return ['Grand Total', 's', 5];
            }

            return $this->isNumericColumn($column)
                ? [$totals[$column['key']] ?? 0, 'n', 4]
                : ['', 's', 5];
        }, $columns, array_keys($columns)));

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="7" topLeftCell="A8" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $this->xlsxColumns($columns) . '</cols><sheetData>' . implode('', $rows) . '</sheetData>'
            . '<mergeCells count="2"><mergeCell ref="A1:' . $lastColumn . '1"/><mergeCell ref="A2:' . $lastColumn . '2"/></mergeCells>'
            . '</worksheet>';
    }

    private function totalsFor($records, array $columns): array
    {
        $totals = [];

        foreach ($columns as $column) {
            if ($this->isNumericColumn($column)) {
                $totals[$column['key']] = $records->sum(fn (Model $record): float => (float) ($record->{$column['key']} ?? 0));
            }
        }

        return $totals;
    }

    private function summaryCards(array $totals, array $columns): array
    {
        $cards = [];

        foreach ($this->summaryColumns($columns) as $column) {
            if (array_key_exists($column['key'], $totals)) {
                $cards[] = [$column['label'], $this->money($totals[$column['key']]), $column['key']];
            }
        }

        return array_slice($cards, 0, 7);
    }

    private function summaryColumns(array $columns): array
    {
        if (method_exists($this, 'exportSummaryColumns')) {
            return $this->exportSummaryColumns();
        }

        return array_values(array_filter($columns, fn (array $column): bool => $this->isNumericColumn($column)));
    }

    private function totalColumns(array $columns): array
    {
        $merged = [];

        foreach (array_merge($columns, $this->summaryColumns($columns)) as $column) {
            $merged[$column['key']] = $column;
        }

        return array_values($merged);
    }

    private function isNumericColumn(array $column): bool
    {
        if (! array_key_exists('key', $column)) {
            return false;
        }

        return in_array($column['key'], [
            'amount', 'taxable', 'cgst_amt', 'igst_amt', 'sgst_amt', 'add_total', 'less_total', 'net_amount',
        ], true);
    }

    private function pdfColumns(array $columns): array
    {
        $weights = array_map(fn (array $column): int => match ($column['key']) {
            'account' => 120,
            'remark' => 85,
            'add1' => 80,
            'city', 'state' => 55,
            default => $this->isNumericColumn($column) ? 56 : 50,
        }, $columns);
        $total = array_sum($weights) ?: 1;

        return array_map(fn (array $column, int $weight): array => [
            $column['label'],
            (int) floor(794 * $weight / $total),
        ], $columns, $weights);
    }

    private function rawValue(Model $record, array $column): mixed
    {
        $value = $record->{$column['key']};

        return $value instanceof DateTimeInterface ? $value->format('d-m-Y') : $value;
    }

    private function displayValue(Model $record, array $column): string
    {
        $value = $this->rawValue($record, $column);

        return $this->isNumericColumn($column) ? $this->money($value) : (string) ($value ?? '');
    }

    private function money(mixed $value): string
    {
        return number_format((float) ($value ?? 0), 2, '.', '');
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

    private function xlsxColumns(array $columns): string
    {
        $xml = '';

        foreach ($columns as $index => $column) {
            $number = $index + 1;
            $width = match ($column['key']) {
                'account' => 34,
                'remark' => 32,
                'add1' => 28,
                'city', 'state' => 18,
                default => $this->isNumericColumn($column) ? 14 : 13,
            };
            $xml .= '<col min="' . $number . '" max="' . $number . '" width="' . $width . '" customWidth="1"/>';
        }

        return $xml;
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
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $this->xmlText($this->registerTitle()) . '" sheetId="1" r:id="rId1"/></sheets></workbook>';
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
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>' . $this->xmlText($this->registerTitle()) . '</dc:title><dc:creator>DataBridge</dc:creator></cp:coreProperties>';
    }

    private function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="1"><numFmt numFmtId="164" formatCode="0.00"/></numFmts><fonts count="4"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="22"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FF0F172A"/><name val="Calibri"/></font></fonts><fills count="7"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF07111F"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE0F2FE"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F766E"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF0FDFA"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="10"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/><xf numFmtId="0" fontId="2" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center"/></xf><xf numFmtId="164" fontId="3" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyNumberFormat="1"><alignment horizontal="right"/></xf><xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyFill="1" applyBorder="1"/><xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyNumberFormat="1"><alignment horizontal="right"/></xf><xf numFmtId="164" fontId="0" fillId="6" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyNumberFormat="1"><alignment horizontal="right"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function recordValue(Model $record, string $key): mixed
    {
        $value = $record->{$key};

        return $value instanceof DateTimeInterface ? $value->format('d-m-Y') : $value;
    }

}
