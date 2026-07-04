<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReceiptRegister;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReceiptRegisterApiController extends Controller
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
        $payloadVoucherNumbers = [];
        $payloadFallbackKeys = [];
        $skipped = count($records) - $normalizedRecords->count();

        foreach ($normalizedRecords as $record) {
            $voucherNo = $record['voucher_no'];

            if ($voucherNo !== null && $voucherNo !== '') {
                $voucherKey = $this->voucherDuplicateKey($record);

                if (isset($payloadVoucherNumbers[$voucherKey])) {
                    $skipped++;
                    continue;
                }

                $payloadVoucherNumbers[$voucherKey] = [
                    'user_code' => $record['user_code'],
                    'voucher_no' => $voucherNo,
                ];
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

        $existingVoucherNumbers = ReceiptRegister::query()
            ->whereIn('user_code', array_values(array_unique(array_column($payloadVoucherNumbers, 'user_code'))))
            ->whereIn('voucher_no', array_values(array_unique(array_column($payloadVoucherNumbers, 'voucher_no'))))
            ->get(['user_code', 'voucher_no'])
            ->mapWithKeys(fn (ReceiptRegister $receipt): array => [$this->voucherDuplicateKey($receipt->getAttributes()) => true])
            ->all();

        $now = now();
        $insertRows = $uniqueRecords
            ->reject(function (array $record) use ($existingVoucherNumbers, &$skipped): bool {
                $voucherNo = $record['voucher_no'];
                $exists = $voucherNo !== null && $voucherNo !== '' && isset($existingVoucherNumbers[$this->voucherDuplicateKey($record)]);

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
            ->each(fn ($chunk): bool => DB::table('receipt_registers')->insert($chunk->all()));

        return response()->json([
            'message' => 'Receipt register data processed successfully.',
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
        $amount = $this->decimalValue($record, ['amount', 'Amount', 'AMOUNT', 'receipt_amount', 'Receipt Amount', 'ReceiptAmount']);

        return [
            'user_code' => $this->value($record, ['user_code', 'usercode', 'UserCode', 'USERCODE', 'User Code', 'use_code', 'UseCode', 'Use Code', 'usr_code', 'UsrCode', 'USR_CODE', 'ucode', 'UCode']),
            'voucher_no' => $this->value($record, ['voucher_no', 'VoucherNO', 'Voucherno', 'Voucher No', 'Voucher', 'voucherno', 'VOUCHERNO', 'receipt_no', 'ReceiptNO', 'Receipt No', 'ReceiptNo']),
            'vtype' => $this->value($record, ['vtype', 'Vtype', 'VType', 'VTYPE']),
            'invoice' => $this->value($record, ['invoice', 'Invoice', 'Invoiceno', 'InvoiceNo', 'receipt_no', 'ReceiptNO', 'Receipt No', 'ReceiptNo', 'VOUCHERNO']),
            'account' => $this->value($record, ['account', 'Account', 'Acno', 'ACNO', 'customer_name', 'Customer Name', 'party_name', 'Party Name']),
            'tran_date' => $this->dateValue($record, ['tran_date', 'Tran Date', 'Trandate', 'trandate', 'TRANDATE']),
            'rec_date' => $this->dateValue($record, ['rec_date', 'Rec Date', 'RecDate', 'recdate']),
            'amount' => $amount,
            'add_total' => $this->decimalValue($record, ['add_total', 'addtotal', 'AddTotal', 'add total', 'Add Total', 'ADDTOTAL']),
            'vno_made' => $this->value($record, ['vno_made', 'vnomade', 'VnoMade', 'VNOMADE']),
            'less_total' => $this->decimalValue($record, ['less_total', 'lesstotal', 'less total', 'Less Total', 'LessTotal', 'LESSTOTAL']),
            'net_amount' => $this->decimalValue($record, ['net_amount', 'Net Amount', 'NetAmount', 'NetAmt', 'Netamt', 'netamount', 'NETAMT', 'receipt_amount', 'Receipt Amount', 'ReceiptAmount']),
            'mobile' => $this->value($record, ['mobile', 'Mobile']),
            'remark' => $this->value($record, ['remark', 'Remark', 'Remark1', 'remark1', 'remarks', 'Remarks', 'REMARK1']),
            'remark2' => $this->value($record, ['remark2', 'Remark2', 'REMARK2']),
            'remark3' => $this->value($record, ['remark3', 'Remark3', 'REMARK3']),
            'remark4' => $this->value($record, ['remark4', 'Remark4', 'REMARK4']),
            'db_acno' => $this->value($record, ['db_acno', 'dbacno', 'DbAcno', 'DBACNO']),
            'cr_acno' => $this->value($record, ['cr_acno', 'cracno', 'CrAcno', 'CRACNO']),
            'cheque_no' => $this->value($record, ['cheque_no', 'chequeno', 'ChequeNo', 'CHEQUENO']),
            'cheque_date' => $this->dateValue($record, ['cheque_date', 'chequedate', 'ChequeDate', 'CHEQUEDATE']),
            'cheque_bank' => $this->value($record, ['cheque_bank', 'chequebank', 'ChequeBank', 'CHEQUEBANK']),
            'effect' => $this->value($record, ['effect', 'Effect', 'EFFECT']),
            'delete_it' => $this->value($record, ['delete_it', 'deleteit', 'DeleteIt', 'DELETEIT']),
            'balance' => $this->decimalValue($record, ['balance', 'Balance', 'BALANCE']),
            'oppw' => $this->value($record, ['oppw', 'Oppw', 'OPPW']),
            'chq_no' => $this->value($record, ['chq_no', 'chqno', 'ChqNo', 'CHQNO']),
            'chq_date' => $this->dateValue($record, ['chq_date', 'chqdate', 'ChqDate', 'CHQDATE']),
            'chq_bank' => $this->value($record, ['chq_bank', 'chqbank', 'ChqBank', 'CHQBANK']),
            'cancelled' => $this->value($record, ['cancelled', 'Cancelled', 'CANCELLED']),
            'main_acno' => $this->value($record, ['main_acno', 'mainacno', 'MainAcno', 'MAINACNO']),
            'single_ent' => $this->value($record, ['single_ent', 'singleent', 'SingleEnt', 'SINGLEENT']),
            'extra' => $this->value($record, ['extra', 'Extra', 'EXTRA']),
            'grno' => $this->value($record, ['grno', 'GRNO', 'Grno']),
            'grdate' => $this->dateValue($record, ['grdate', 'GRDate', 'GrDate']),
            'add1' => $this->value($record, ['add1', 'Add1', 'ADD1']),
            'add2' => $this->value($record, ['add2', 'Add2', 'ADD2']),
            'add3' => $this->value($record, ['add3', 'Add3', 'ADD3']),
            'add4' => $this->value($record, ['add4', 'Add4', 'ADD4']),
            'city' => $this->value($record, ['city', 'City']),
            'transport' => $this->value($record, ['transport', 'Transport']),
            'interstate' => $this->value($record, ['interstate', 'Interstate']),
            'crbill' => $this->value($record, ['crbill', 'CrBill', 'CRBill']),
            'taxable' => $amount,
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
        if ($record['user_code'] === null || $record['user_code'] === '') {
            return false;
        }

        return collect($record)
            ->except('user_code')
            ->contains(fn ($value): bool => $value !== null && $value !== '');
    }

    private function voucherDuplicateKey(array $record): string
    {
        return ($record['user_code'] ?? '') . '|' . ($record['voucher_no'] ?? '');
    }

    private function fallbackDuplicateKey(array $record): ?string
    {
        if (empty($record['invoice']) || empty($record['account']) || empty($record['tran_date']) || $record['net_amount'] === null) {
            return null;
        }

        return implode('|', [
            $record['user_code'],
            $record['invoice'],
            $record['account'],
            $record['tran_date'],
            number_format((float) $record['net_amount'], 2, '.', ''),
        ]);
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
