<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_code',
    'acno',
    'hacno',
    'achead',
    'opening',
    'open_type',
    'current',
    'current_type',
    'add1',
    'add2',
    'add3',
    'add4',
    'city',
    'phone_no',
    'email',
    'category',
    'cr_days',
    'tin_no',
    'contact',
    'mobile',
    'pan_no',
    'pan_date',
    'state',
    'on_ac_amt',
    'on_ac_type',
    'sales_agent',
    'cr_limit',
    'extra',
])]
class AccountMaster extends Model
{
    public function scopeForUserCode($query, ?string $userCode)
    {
        return $query->where('user_code', $userCode);
    }

    protected function casts(): array
    {
        return [
            'opening' => 'decimal:2',
            'current' => 'decimal:2',
            'cr_days' => 'integer',
            'pan_date' => 'date',
            'on_ac_amt' => 'decimal:2',
            'cr_limit' => 'decimal:2',
        ];
    }
}
