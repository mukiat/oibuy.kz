<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderInfoBankTransfer
 */
class OrderInfoBankTransfer extends Model
{
    protected $table = 'order_info_bank_transfer';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'user_id',
        'payee_name',
        'bank_no',
        'bank_name',
        'bank_branch',
        'mark',
        'pay_document',
    ];

    protected $guarded = [];
}
