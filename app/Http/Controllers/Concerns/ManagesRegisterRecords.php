<?php

namespace App\Http\Controllers\Concerns;

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
        $filename = $this->registerSlug() . '.csv';

        $lines = [];
        $lines[] = $this->csvLine(array_column($columns, 'label'));

        foreach ($records as $record) {
            $lines[] = $this->csvLine(array_map(fn (array $column): mixed => $this->recordValue($record, $column['key']), $columns));
        }

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $records = $this->filteredQuery($request)->latest('id')->limit(1000)->get();
        $columns = $this->exportColumns();
        $title = $this->registerTitle();

        $html = '<!doctype html><html><head><meta charset="utf-8"><title>' . e($title) . '</title>'
            . '<style>body{font-family:Arial,sans-serif;font-size:11px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #cbd5e1;padding:5px;text-align:left}th{background:#0f172a;color:#fff}.totals{margin:12px 0;font-weight:bold}</style>'
            . '</head><body><h1>' . e($title) . '</h1><div class="totals">Records: ' . number_format($records->count()) . '</div><table><thead><tr>';

        foreach ($columns as $column) {
            $html .= '<th>' . e($column['label']) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($records as $record) {
            $html .= '<tr>';
            foreach ($columns as $column) {
                $html .= '<td>' . e((string) $this->recordValue($record, $column['key'])) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="' . $this->registerSlug() . '.html"',
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

    private function csvLine(array $values): string
    {
        return implode(',', array_map(function (mixed $value): string {
            $value = str_replace('"', '""', (string) ($value ?? ''));

            return '"' . $value . '"';
        }, $values));
    }

    private function recordValue(Model $record, string $key): mixed
    {
        $value = $record->{$key};

        return method_exists($value, 'format') ? $value->format('d-m-Y') : $value;
    }
}
