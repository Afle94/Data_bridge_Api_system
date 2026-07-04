<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRegister;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class PurchaseRegisterApiController extends Controller
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

        $existingVoucherNumbers = PurchaseRegister::query()
            ->whereIn('user_code', array_values(array_unique(array_column($payloadVoucherNumbers, 'user_code'))))
            ->whereIn('voucher_no', array_values(array_unique(array_column($payloadVoucherNumbers, 'voucher_no'))))
            ->get(['user_code', 'voucher_no'])
            ->mapWithKeys(fn (PurchaseRegister $purchase): array => [$this->voucherDuplicateKey($purchase->getAttributes()) => true])
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
            ->each(fn ($chunk): bool => DB::table('purchase_registers')->insert($chunk->all()));

        return response()->json([
            'message' => 'Purchase register data processed successfully.',
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
        $amount = $this->decimalValue($record, ['amount', 'Amount', 'AMOUNT', 'taxableamt', 'Taxableamt', 'TAXABLEAMT', 'taxable', 'Taxable']);
        $taxable = $this->decimalValue($record, ['taxableamt', 'Taxableamt', 'TAXABLEAMT', 'taxable', 'Taxable', 'amount', 'Amount', 'AMOUNT']);

        return [
            'user_code' => $this->value($record, ['user_code', 'usercode', 'UserCode', 'USERCODE', 'User Code', 'use_code', 'UseCode', 'Use Code', 'usr_code', 'UsrCode', 'USR_CODE', 'ucode', 'UCode']),
            'voucher_no' => $this->value($record, ['voucher_no', 'VoucherNO', 'Voucherno', 'Voucher No', 'Voucher', 'voucherno', 'VOUCHERNO']),
            'vtype' => $this->value($record, ['vtype', 'Vtype', 'VType', 'VTYPE']),
            'invoice' => $this->value($record, ['invoice', 'Invoice', 'Invoiceno', 'InvoiceNo', 'INVOICENO']),
            'account' => $this->value($record, ['account', 'Account', 'Acno', 'ACNO', 'supplier_name', 'Supplier Name', 'party_name', 'Party Name']),
            'tran_date' => $this->dateValue($record, ['tran_date', 'Tran Date', 'Trandate', 'trandate', 'TRANDATE']),
            'rec_date' => $this->dateValue($record, ['rec_date', 'Rec Date', 'RecDate', 'recdate', 'RECDATE']),
            'manual_no' => $this->value($record, ['manual_no', 'manualno', 'ManualNo', 'MANUALNO']),
            'roadp_no' => $this->value($record, ['roadp_no', 'roadpno', 'RoadpNo', 'ROADPNO']),
            'repl_goods' => $this->value($record, ['repl_goods', 'replgoods', 'ReplGoods', 'REPLGOODS']),
            'amount' => $amount,
            'net_amount' => $this->decimalValue($record, ['net_amount', 'Net Amount', 'NetAmount', 'NetAmt', 'Netamt', 'netamount', 'NETAMT']),
            'mobile' => $this->value($record, ['mobile', 'Mobile', 'PHONENO']),
            'remark' => $this->value($record, ['remark', 'Remark', 'Remark1', 'remark1', 'remarks', 'Remarks', 'REMARK1']),
            'remark2' => $this->value($record, ['remark2', 'Remark2', 'REMARK2']),
            'remark3' => $this->value($record, ['remark3', 'Remark3', 'REMARK3']),
            'remark4' => $this->value($record, ['remark4', 'Remark4', 'REMARK4']),
            'remark5' => $this->value($record, ['remark5', 'Remark5', 'REMARK5']),
            'remark6' => $this->value($record, ['remark6', 'Remark6', 'REMARK6']),
            'grno' => $this->value($record, ['grno', 'GRNO', 'Grno']),
            'grdate' => $this->dateValue($record, ['grdate', 'GRDate', 'GrDate', 'GRDATE']),
            'order_no' => $this->value($record, ['order_no', 'orderno', 'OrderNo', 'ORDERNO']),
            'order_date' => $this->dateValue($record, ['order_date', 'orderdate', 'OrderDate', 'ORDERDATE']),
            'disc_per' => $this->decimalValue($record, ['disc_per', 'discper', 'DiscPer', 'DISCPER']),
            'discount' => $this->decimalValue($record, ['discount', 'Discount', 'DISCOUNT']),
            'dr_side' => $this->decimalValue($record, ['dr_side', 'drside', 'DrSide', 'DRSIDE']),
            'cr_side' => $this->decimalValue($record, ['cr_side', 'crside', 'CrSide', 'CRSIDE']),
            'add1' => $this->value($record, ['add1', 'Add1', 'ADD1']),
            'add2' => $this->value($record, ['add2', 'Add2', 'ADD2']),
            'add3' => $this->value($record, ['add3', 'Add3', 'ADD3']),
            'add4' => $this->value($record, ['add4', 'Add4', 'ADD4']),
            'city' => $this->value($record, ['city', 'City', 'CITY']),
            'phone_no' => $this->value($record, ['phone_no', 'phoneno', 'PhoneNo', 'PHONENO']),
            'section' => $this->value($record, ['section', 'Section', 'SECTION']),
            'transport' => $this->value($record, ['transport', 'Transport', 'TRANSPORT']),
            'interstate' => $this->value($record, ['interstate', 'Interstate', 'INTERSTATE']),
            'add_total' => $this->decimalValue($record, ['add_total', 'addtotal', 'AddTotal', 'add total', 'Add Total', 'ADDTOTAL']),
            'less_total' => $this->decimalValue($record, ['less_total', 'lesstotal', 'less total', 'Less Total', 'LessTotal', 'LESSTOTAL']),
            'cancels' => $this->value($record, ['cancels', 'Cancels', 'CANCELS']),
            'cc_no' => $this->value($record, ['cc_no', 'ccno', 'CcNo', 'CCNO']),
            'delvat1' => $this->value($record, ['delvat1', 'Delvat1', 'DELVAT1']),
            'delvat2' => $this->value($record, ['delvat2', 'Delvat2', 'DELVAT2']),
            'delvat3' => $this->value($record, ['delvat3', 'Delvat3', 'DELVAT3']),
            'delvat4' => $this->value($record, ['delvat4', 'Delvat4', 'DELVAT4']),
            'weight' => $this->decimalValue($record, ['weight', 'Weight', 'WEIGHT']),
            'boxes' => $this->integerValue($record, ['boxes', 'Boxes', 'BOXES']),
            'net_billing' => $this->value($record, ['net_billing', 'netbilling', 'NetBilling', 'NETBILLING']),
            'add_after' => $this->decimalValue($record, ['add_after', 'addafter', 'AddAfter', 'ADDAFTER']),
            'less_after' => $this->decimalValue($record, ['less_after', 'lessafter', 'LessAfter', 'LESSAFTER']),
            'crbill' => $this->value($record, ['crbill', 'CrBill', 'CRBill', 'CRBILL']),
            'taxable' => $taxable,
            'sgst_per' => $this->decimalValue($record, ['sgst_per', 'sgstper', 'SGSTPer', 'SGST Per', 'SGSTPER']),
            'cgst_per' => $this->decimalValue($record, ['cgst_per', 'cgstper', 'CGSTPer', 'CGST Per', 'CGSTPER']),
            'igst_per' => $this->decimalValue($record, ['igst_per', 'igstper', 'IGSTPer', 'IGST Per', 'IGSTPER']),
            'cgst_amt' => $this->decimalValue($record, ['cgst_amt', 'cgstamt', 'CGSTAmt', 'CGST Amt', 'CGSTAMT']),
            'sgst_amt' => $this->decimalValue($record, ['sgst_amt', 'sgstamt', 'SGSTAmt', 'SGST Amt', 'SGSTAMT']),
            'igst_amt' => $this->decimalValue($record, ['igst_amt', 'igstamt', 'IGSTAmt', 'IGST Amt', 'IGSTAMT']),
            'cancelled' => $this->value($record, ['cancelled', 'Cancelled', 'CANCELLED']),
            'extra' => $this->value($record, ['extra', 'Extra', 'EXTRA']),
            'state' => $this->value($record, ['state', 'State', 'STATE']),
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
