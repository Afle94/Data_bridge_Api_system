<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleRegister;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaleRegisterApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $startedAt = microtime(true);
        $payload = $this->payloadFromRequest($request);
        $records = $this->recordsFromPayload($payload);

        if ($records === []) {
            return response()->json([
                'message' => 'No records received.',
            ], 422);
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
        $payloadVoucherNumbers = [];
        $payloadFallbackKeys = [];
        $skipped = count($records) - $normalizedRecords->count();

        foreach ($normalizedRecords as $record) {
            $voucherNo = $record['voucher_no'];

            if ($voucherNo !== null && $voucherNo !== '') {
                if (isset($payloadVoucherNumbers[$voucherNo])) {
                    $skipped++;
                    continue;
                }

                $payloadVoucherNumbers[$voucherNo] = true;
                $uniqueRecords->push($record);
                continue;
            }

            $fallbackKey = $this->fallbackDuplicateKey($record);

            if ($fallbackKey !== null && isset($payloadFallbackKeys[$fallbackKey])) {
                $skipped++;
                continue;
            }

            if ($fallbackKey !== null) {
                $payloadFallbackKeys[$fallbackKey] = true;
            }

            $uniqueRecords->push($record);
        }

        $existingVoucherNumbers = SaleRegister::query()
            ->whereIn('voucher_no', array_keys($payloadVoucherNumbers))
            ->pluck('voucher_no')
            ->all();

        $existingVoucherNumbers = array_flip($existingVoucherNumbers);

        $now = now();
        $insertRows = $uniqueRecords
            ->reject(function (array $record) use ($existingVoucherNumbers, &$skipped): bool {
                $voucherNo = $record['voucher_no'];
                $exists = $voucherNo !== null && $voucherNo !== '' && isset($existingVoucherNumbers[$voucherNo]);

                if ($exists) {
                    $skipped++;
                }

                return $exists;
            })
            ->map(function (array $record) use ($now): array {
                return array_merge($record, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            })
            ->values();

        $insertRows
            ->chunk(500)
            ->each(fn ($chunk): bool => DB::table('sale_registers')->insert($chunk->all()));

        return response()->json([
            'message' => 'Sale register data processed successfully.',
            'received' => count($records),
            'inserted' => $insertRows->count(),
            'skipped' => $skipped,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
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

        // Some VFP builders leave a trailing comma before } or ]. Clean that only.
        $cleaned = preg_replace('/,\s*([}\]])/', '$1', $content) ?? $content;
        $decoded = json_decode($cleaned, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function normalize(array $record): array
    {
        $amount = $this->decimalValue($record, ['amount', 'Amount', 'taxableamt', 'Taxableamt', 'taxable', 'Taxable']);
        $taxable = $this->decimalValue($record, ['taxableamt', 'Taxableamt', 'taxable', 'Taxable', 'amount', 'Amount']);

        return [
            'voucher_no' => $this->value($record, ['voucher_no', 'VoucherNO', 'Voucherno', 'Voucher No', 'Voucher', 'voucherno']),
            'vtype' => $this->value($record, ['vtype', 'Vtype', 'VType']),
            'invoice' => $this->value($record, ['invoice', 'Invoice', 'Invoiceno', 'InvoiceNo']),
            'account' => $this->value($record, ['account', 'Account', 'Acno', 'ACNO', 'party_name', 'Party Name']),
            'tran_date' => $this->dateValue($record, ['tran_date', 'Tran Date', 'Trandate', 'trandate']),
            'rec_date' => $this->dateValue($record, ['rec_date', 'Rec Date', 'RecDate', 'recdate']),
            'amount' => $amount,
            'net_amount' => $this->decimalValue($record, ['net_amount', 'Net Amount', 'NetAmount', 'NetAmt', 'Netamt', 'netamount']),
            'mobile' => $this->value($record, ['mobile', 'Mobile']),
            'remark' => $this->value($record, ['remark', 'Remark', 'Remark1', 'remark1', 'remarks', 'Remarks']),
            'grno' => $this->value($record, ['grno', 'GRNO', 'Grno']),
            'grdate' => $this->dateValue($record, ['grdate', 'GRDate', 'GrDate']),
            'add1' => $this->value($record, ['add1', 'Add1', 'ADD1']),
            'add2' => $this->value($record, ['add2', 'Add2', 'ADD2']),
            'add3' => $this->value($record, ['add3', 'Add3', 'ADD3']),
            'add4' => $this->value($record, ['add4', 'Add4', 'ADD4']),
            'city' => $this->value($record, ['city', 'City']),
            'transport' => $this->value($record, ['transport', 'Transport']),
            'interstate' => $this->value($record, ['interstate', 'Interstate']),
            'add_total' => $this->decimalValue($record, ['add_total', 'addtotal', 'AddTotal', 'add total', 'Add Total']),
            'less_total' => $this->decimalValue($record, ['less_total', 'lesstotal', 'less total', 'Less Total', 'LessTotal']),
            'crbill' => $this->value($record, ['crbill', 'CrBill', 'CRBill']),
            'taxable' => $taxable,
            'cgst_amt' => $this->decimalValue($record, ['cgst_amt', 'cgstamt', 'CGSTAmt', 'CGST Amt']),
            'sgst_amt' => $this->decimalValue($record, ['sgst_amt', 'sgstamt', 'SGSTAmt', 'SGST Amt']),
            'igst_amt' => $this->decimalValue($record, ['igst_amt', 'igstamt', 'IGSTAmt', 'IGST Amt']),
            'state' => $this->value($record, ['state', 'State']),
            'gst_no' => $this->value($record, ['gst_no', 'gstno', 'GSTNo', 'GST No']),
            'total_customers' => $this->integerValue($record, ['total_customers', 'totalcustomers', 'TotalCustomers', 'Total Customers']),
        ];
    }

    private function hasImportableData(array $record): bool
    {
        return collect($record)->contains(fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * @param array<string, mixed> $record
     */
    private function fallbackDuplicateKey(array $record): ?string
    {
        if (empty($record['invoice']) || empty($record['account']) || empty($record['tran_date']) || $record['net_amount'] === null) {
            return null;
        }

        return implode('|', [
            $record['invoice'],
            $record['account'],
            $record['tran_date'],
            number_format((float) $record['net_amount'], 2, '.', ''),
        ]);
    }

    /**
     * @param array<string, mixed> $record
     * @param array<int, string> $keys
     */
    private function value(array $record, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (Arr::exists($record, $key) && $record[$key] !== '') {
                return is_string($record[$key]) ? trim($record[$key]) : $record[$key];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $record
     * @param array<int, string> $keys
     */
    private function decimalValue(array $record, array $keys): ?float
    {
        $value = $this->value($record, $keys);

        if ($value === null) {
            return null;
        }

        $cleaned = str_replace([',', ' '], '', (string) $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * @param array<string, mixed> $record
     * @param array<int, string> $keys
     */
    private function integerValue(array $record, array $keys): ?int
    {
        $value = $this->decimalValue($record, $keys);

        return $value === null ? null : (int) $value;
    }

    /**
     * @param array<string, mixed> $record
     * @param array<int, string> $keys
     */
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
                $date = Carbon::createFromFormat($format, (string) $value);
                return $date->toDateString();
            } catch (Throwable) {
                //
            }
        }

        return null;
    }
}
