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
    'amount',
    'net_amount',
    'mobile',
    'remark',
    'grno',
    'grdate',
    'add1',
    'add2',
    'add3',
    'add4',
    'city',
    'transport',
    'interstate',
    'add_total',
    'less_total',
    'crbill',
    'taxable',
    'cgst_amt',
    'sgst_amt',
    'igst_amt',
    'state',
    'gst_no',
    'total_customers',
])]
class SaleRegister extends Model
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
            'amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'add_total' => 'decimal:2',
            'less_total' => 'decimal:2',
            'taxable' => 'decimal:2',
            'cgst_amt' => 'decimal:2',
            'sgst_amt' => 'decimal:2',
            'igst_amt' => 'decimal:2',
            'total_customers' => 'integer',
        ];
    }
}
