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
    'add_total',
    'vno_made',
    'less_total',
    'net_amount',
    'mobile',
    'remark',
    'remark2',
    'remark3',
    'remark4',
    'db_acno',
    'cr_acno',
    'cheque_no',
    'cheque_date',
    'cheque_bank',
    'effect',
    'delete_it',
    'balance',
    'oppw',
    'chq_no',
    'chq_date',
    'chq_bank',
    'cancelled',
    'main_acno',
    'single_ent',
    'extra',
    'grno',
    'grdate',
    'add1',
    'add2',
    'add3',
    'add4',
    'city',
    'transport',
    'interstate',
    'crbill',
    'taxable',
    'cgst_amt',
    'sgst_amt',
    'igst_amt',
    'state',
    'gst_no',
    'total_customers',
])]
class ReceiptRegister extends Model
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
            'cheque_date' => 'date',
            'chq_date' => 'date',
            'grdate' => 'date',
            'amount' => 'decimal:2',
            'add_total' => 'decimal:2',
            'less_total' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'taxable' => 'decimal:2',
            'cgst_amt' => 'decimal:2',
            'sgst_amt' => 'decimal:2',
            'igst_amt' => 'decimal:2',
            'total_customers' => 'integer',
        ];
    }
}
