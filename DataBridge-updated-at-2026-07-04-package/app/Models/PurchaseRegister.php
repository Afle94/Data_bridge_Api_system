<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_code',
    'voucher_no',
    'vtype',
    'invoice',
    'account',
    'tran_date',
    'rec_date',
    'manual_no',
    'roadp_no',
    'repl_goods',
    'amount',
    'net_amount',
    'mobile',
    'remark',
    'remark2',
    'remark3',
    'remark4',
    'remark5',
    'remark6',
    'grno',
    'grdate',
    'order_no',
    'order_date',
    'disc_per',
    'discount',
    'dr_side',
    'cr_side',
    'add1',
    'add2',
    'add3',
    'add4',
    'city',
    'phone_no',
    'section',
    'transport',
    'interstate',
    'add_total',
    'less_total',
    'cancels',
    'cc_no',
    'delvat1',
    'delvat2',
    'delvat3',
    'delvat4',
    'weight',
    'boxes',
    'net_billing',
    'add_after',
    'less_after',
    'crbill',
    'taxable',
    'sgst_per',
    'cgst_per',
    'igst_per',
    'cgst_amt',
    'sgst_amt',
    'igst_amt',
    'cancelled',
    'extra',
    'state',
    'gst_no',
    'total_customers',
])]
class PurchaseRegister extends Model
{
    public function scopeForUserCode($query, ?string $userCode)
    {
        return $query->where('user_code', $userCode);
    }

    protected function casts(): array
    {
        return [
            'tran_date' => 'date',
            'rec_date' => 'date',
            'grdate' => 'date',
            'order_date' => 'date',
            'amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'disc_per' => 'decimal:2',
            'discount' => 'decimal:2',
            'dr_side' => 'decimal:2',
            'cr_side' => 'decimal:2',
            'add_total' => 'decimal:2',
            'less_total' => 'decimal:2',
            'weight' => 'decimal:3',
            'boxes' => 'integer',
            'add_after' => 'decimal:2',
            'less_after' => 'decimal:2',
            'taxable' => 'decimal:2',
            'sgst_per' => 'decimal:2',
            'cgst_per' => 'decimal:2',
            'igst_per' => 'decimal:2',
            'cgst_amt' => 'decimal:2',
            'sgst_amt' => 'decimal:2',
            'igst_amt' => 'decimal:2',
            'total_customers' => 'integer',
        ];
    }
}
