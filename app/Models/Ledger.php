<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_code',
    'import_key',
    'voucher_no',
    'vtype',
    'dtype',
    'tran_type',
    'acno',
    'achead',
    'tran_date',
    'amount',
    'sales_agent',
    'remark1',
    'remark2',
    'remark3',
    'remark4',
    'remark5',
    'adjustment',
    'add_flag',
    'less_flag',
    'opening',
    'crbill',
    'disc_per',
    'on_amount',
    'percent',
    'rate',
    'calc',
    'ms',
    'add_less',
    'adj_per',
    'adj_type',
    'vat_adj',
    'cancelled',
    'vno_made',
    'single_ent',
    'salesman',
    'extra',
])]
class Ledger extends Model
{
    public function scopeForUserCode($query, ?string $userCode)
    {
        return $query->where('user_code', $userCode);
    }

    protected function casts(): array
    {
        return [
            'tran_date' => 'date',
            'amount' => 'decimal:2',
            'disc_per' => 'decimal:2',
            'on_amount' => 'decimal:2',
            'percent' => 'decimal:2',
            'rate' => 'decimal:2',
            'adj_per' => 'decimal:2',
        ];
    }
}
