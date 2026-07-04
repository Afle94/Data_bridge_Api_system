<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class LedgerApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $startedAt = microtime(true);
        $payload = $this->payloadFromRequest($request);
        $records = $this->recordsFromPayload($payload);

        if ($records === []) {
            return response()->json(['message' => 'No records received.'], 422);
        }

        $normalizedRecords = collect($records)
            ->map(fn (array $record): array => $this->normalize($record))
            ->filter(fn (array $record): bool => $this->hasImportableData($record))
            ->values();

        if ($normalizedRecords->isEmpty()) {
            return response()->json([
                'message' => 'No importable records received.',
                'received' => count($records),
                'inserted' => 0,
                'skipped' => count($records),
            ], 422);
        }

        $uniqueRecords = collect();
        $payloadKeys = [];
        $skipped = count($records) - $normalizedRecords->count();

        foreach ($normalizedRecords as $record) {
            $key = $this->duplicateKey($record);

            if (isset($payloadKeys[$key])) {
                $skipped++;
                continue;
            }

            $payloadKeys[$key] = true;
            $uniqueRecords->push($record);
        }

        $now = now();
        $insertRows = $uniqueRecords
            ->map(fn (array $record): array => array_merge($record, [
                'import_key' => $this->duplicateKey($record),
                'created_at' => $now,
                'updated_at' => $now,
            ]))
            ->values();

        $inserted = $insertRows
            ->chunk(1000)
            ->sum(fn ($chunk): int => DB::table('ledgers')->insertOrIgnore($chunk->all()));

        $skipped += $insertRows->count() - $inserted;

        return response()->json([
            'message' => 'Ledger data processed successfully.',
            'received' => count($records),
            'inserted' => $inserted,
            'skipped' => $skipped,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ], 201);
    }

    private function payloadFromRequest(Request $request): array
    {
        $payload = $request->json()->all() ?: $request->all();

        if ($payload !== []) {
            return $payload;
        }

        $content = trim($request->getContent());

        if ($content === '') {
            return [];
        }

        $cleaned = preg_replace('/,\s*([}\]])/', '$1', $content) ?? $content;
        $decoded = json_decode($cleaned, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function recordsFromPayload(array $payload): array
    {
        if (array_is_list($payload)) {
            return array_filter($payload, 'is_array');
        }

        if (isset($payload['records']) && is_array($payload['records'])) {
            return array_filter($payload['records'], 'is_array');
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return array_is_list($payload['data'])
                ? array_filter($payload['data'], 'is_array')
                : [$payload['data']];
        }

        return [$payload];
    }

    private function normalize(array $record): array
    {
        return [
            'user_code' => $this->value($record, ['user_code', 'usercode', 'UserCode', 'USERCODE', 'User Code', 'use_code', 'UseCode', 'Use Code', 'usr_code', 'UsrCode', 'USR_CODE', 'ucode', 'UCode']),
            'voucher_no' => $this->value($record, ['voucher_no', 'VoucherNO', 'Voucherno', 'Voucher No', 'Voucher', 'voucherno', 'VOUCHERNO']),
            'vtype' => $this->value($record, ['vtype', 'Vtype', 'VType', 'VTYPE']),
            'dtype' => $this->value($record, ['dtype', 'Dtype', 'DType', 'DTYPE']),
            'tran_type' => $this->value($record, ['tran_type', 'trantype', 'TranType', 'TRANTYPE']),
            'acno' => $this->value($record, ['acno', 'Acno', 'ACNO']),
            'achead' => $this->value($record, ['achead', 'AcHead', 'ACHEAD']),
            'tran_date' => $this->dateValue($record, ['tran_date', 'Tran Date', 'Trandate', 'trandate', 'TRANDATE']),
            'amount' => $this->decimalValue($record, ['amount', 'Amount', 'AMOUNT']),
            'sales_agent' => $this->value($record, ['sales_agent', 'salesagent', 'SalesAgent', 'SALESAGENT']),
            'remark1' => $this->value($record, ['remark1', 'Remark1', 'REMARK1', 'remark', 'Remark']),
            'remark2' => $this->value($record, ['remark2', 'Remark2', 'REMARK2']),
            'remark3' => $this->value($record, ['remark3', 'Remark3', 'REMARK3']),
            'remark4' => $this->value($record, ['remark4', 'Remark4', 'REMARK4']),
            'remark5' => $this->value($record, ['remark5', 'Remark5', 'REMARK5']),
            'adjustment' => $this->value($record, ['adjustment', 'Adjustment', 'ADJUSTMENT']),
            'add_flag' => $this->value($record, ['add_flag', 'add', 'Add', 'ADD']),
            'less_flag' => $this->value($record, ['less_flag', 'less', 'Less', 'LESS']),
            'opening' => $this->value($record, ['opening', 'Opening', 'OPENING']),
            'crbill' => $this->value($record, ['crbill', 'CrBill', 'CRBILL']),
            'disc_per' => $this->decimalValue($record, ['disc_per', 'discper', 'DiscPer', 'DISCPER']),
            'on_amount' => $this->decimalValue($record, ['on_amount', 'onamt', 'OnAmt', 'ONAMT']),
            'percent' => $this->decimalValue($record, ['percent', 'Percent', 'PERCENT']),
            'rate' => $this->decimalValue($record, ['rate', 'Rate', 'RATE']),
            'calc' => $this->value($record, ['calc', 'Calc', 'CALC']),
            'ms' => $this->value($record, ['ms', 'Ms', 'MS']),
            'add_less' => $this->value($record, ['add_less', 'addless', 'AddLess', 'ADDLESS']),
            'adj_per' => $this->decimalValue($record, ['adj_per', 'adjper', 'AdjPer', 'ADJPER']),
            'adj_type' => $this->value($record, ['adj_type', 'adjtype', 'AdjType', 'ADJTYPE']),
            'vat_adj' => $this->value($record, ['vat_adj', 'vatadj', 'VatAdj', 'VATADJ']),
            'cancelled' => $this->value($record, ['cancelled', 'Cancelled', 'CANCELLED']),
            'vno_made' => $this->value($record, ['vno_made', 'vnomade', 'VnoMade', 'VNOMADE']),
            'single_ent' => $this->value($record, ['single_ent', 'singleent', 'SingleEnt', 'SINGLEENT']),
            'salesman' => $this->value($record, ['salesman', 'Salesman', 'SALESMAN']),
            'extra' => $this->value($record, ['extra', 'Extra', 'EXTRA']),
        ];
    }

    private function hasImportableData(array $record): bool
    {
        if ($record['user_code'] === null || $record['user_code'] === '') {
            return false;
        }

        return collect($record)
            ->except('user_code')
            ->contains(fn ($value): bool => $value !== null && $value !== '');
    }

    private function duplicateKey(array $record): string
    {
        $amount = $record['amount'] === null ? '' : number_format((float) $record['amount'], 2, '.', '');

        return hash('sha256', implode('|', [
            $record['user_code'] ?? '',
            $record['voucher_no'] ?? '',
            $record['vtype'] ?? '',
            $record['acno'] ?? '',
            $record['tran_date'] ?? '',
            $amount,
        ]));
    }

    private function value(array $record, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (Arr::exists($record, $key) && $record[$key] !== '') {
                return is_string($record[$key]) ? trim($record[$key]) : $record[$key];
            }
        }

        return null;
    }

    private function decimalValue(array $record, array $keys): ?float
    {
        $value = $this->value($record, $keys);

        if ($value === null) {
            return null;
        }

        $cleaned = str_replace([',', ' '], '', (string) $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    private function dateValue(array $record, array $keys): ?string
    {
        $value = $this->value($record, $keys);

        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d'] as $format) {
            try {
                return Carbon::createFromFormat($format, (string) $value)->toDateString();
            } catch (Throwable) {
                //
            }
        }

        return null;
    }
}
