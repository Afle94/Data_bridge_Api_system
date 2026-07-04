<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_code',
    'voucher_no',
    'vtype',
    'account',
    'tran_date',
    'amount',
    'add_total',
    'vno_made',
    'less_total',
    'net_amount',
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
    'classes',
    'main_acno',
    'single_ent',
    'extra',
])]
class PaymentRegister extends Model
{
    public function scopeForUserCode($query, ?string $userCode)
    {
        return $query->where('user_code', $userCode);
    }

    protected function casts(): array
    {
        return [
            'tran_date' => 'date',
            'cheque_date' => 'date',
            'chq_date' => 'date',
            'amount' => 'decimal:2',
            'add_total' => 'decimal:2',
            'less_total' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }
}
