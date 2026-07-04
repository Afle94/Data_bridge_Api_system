<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountMaster;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountMasterApiController extends Controller
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

        $existingKeys = AccountMaster::query()
            ->whereIn('user_code', $uniqueRecords->pluck('user_code')->filter()->unique()->values()->all())
            ->whereIn('acno', $uniqueRecords->pluck('acno')->filter()->unique()->values()->all())
            ->get(['user_code', 'acno'])
            ->mapWithKeys(fn (AccountMaster $account): array => [$this->duplicateKey($account->getAttributes()) => true])
            ->all();

        $now = now();
        $insertRows = $uniqueRecords
            ->reject(function (array $record) use ($existingKeys, &$skipped): bool {
                $exists = isset($existingKeys[$this->duplicateKey($record)]);

                if ($exists) {
                    $skipped++;
                }

                return $exists;
            })
            ->map(fn (array $record): array => array_merge($record, [
                'created_at' => $now,
                'updated_at' => $now,
            ]))
            ->values();

        $insertRows
            ->chunk(500)
            ->each(fn ($chunk): bool => DB::table('account_masters')->insert($chunk->all()));

        return response()->json([
            'message' => 'Account master data processed successfully.',
            'received' => count($records),
            'inserted' => $insertRows->count(),
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
            'acno' => $this->value($record, ['acno', 'Acno', 'ACNO']),
            'hacno' => $this->value($record, ['hacno', 'Hacno', 'HACNO']),
            'achead' => $this->value($record, ['achead', 'AcHead', 'ACHEAD']),
            'opening' => $this->decimalValue($record, ['opening', 'Opening', 'OPENING']),
            'open_type' => $this->value($record, ['open_type', 'opentype', 'OpenType', 'OPENTYPE']),
            'current' => $this->decimalValue($record, ['current', 'Current', 'CURRENT']),
            'current_type' => $this->value($record, ['current_type', 'currentype', 'CurrenType', 'CURRENTYPE']),
            'add1' => $this->value($record, ['add1', 'Add1', 'ADD1']),
            'add2' => $this->value($record, ['add2', 'Add2', 'ADD2']),
            'add3' => $this->value($record, ['add3', 'Add3', 'ADD3']),
            'add4' => $this->value($record, ['add4', 'Add4', 'ADD4']),
            'city' => $this->value($record, ['city', 'City', 'CITY']),
            'phone_no' => $this->value($record, ['phone_no', 'phoneno', 'PhoneNo', 'PHONENO']),
            'email' => $this->value($record, ['email', 'Email', 'EMAIL']),
            'category' => $this->value($record, ['category', 'Category', 'CATEGORY']),
            'cr_days' => $this->integerValue($record, ['cr_days', 'crdays', 'CrDays', 'CRDAYS']),
            'tin_no' => $this->value($record, ['tin_no', 'tinno', 'TinNo', 'TINNO']),
            'contact' => $this->value($record, ['contact', 'Contact', 'CONTACT']),
            'mobile' => $this->value($record, ['mobile', 'Mobile', 'MOBILE']),
            'pan_no' => $this->value($record, ['pan_no', 'panno', 'PanNo', 'PANNO']),
            'pan_date' => $this->dateValue($record, ['pan_date', 'pandate', 'PanDate', 'PANDATE']),
            'state' => $this->value($record, ['state', 'State', 'STATE']),
            'on_ac_amt' => $this->decimalValue($record, ['on_ac_amt', 'onacamt', 'OnAcAmt', 'ONACAMT']),
            'on_ac_type' => $this->value($record, ['on_ac_type', 'onactype', 'OnAcType', 'ONACTYPE']),
            'sales_agent' => $this->value($record, ['sales_agent', 'salesagent', 'SalesAgent', 'SALESAGENT']),
            'cr_limit' => $this->decimalValue($record, ['cr_limit', 'crlimit', 'CrLimit', 'CRLIMIT']),
            'extra' => $this->value($record, ['extra', 'Extra', 'EXTRA']),
        ];
    }

    private function hasImportableData(array $record): bool
    {
        if ($record['user_code'] === null || $record['user_code'] === '') {
            return false;
        }

        return $record['acno'] !== null && $record['acno'] !== '';
    }

    private function duplicateKey(array $record): string
    {
        return ($record['user_code'] ?? '') . '|' . ($record['acno'] ?? '');
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

    private function integerValue(array $record, array $keys): ?int
    {
        $value = $this->decimalValue($record, $keys);

        return $value === null ? null : (int) $value;
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
