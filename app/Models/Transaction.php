<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "user_id",
        "transaction_category_id",
        "transaction_type",
        "description",
        "amount",
        "date",
    ];

    protected $casts = [
        "amount" => "float",
        'transaction_type' => TransactionTypeEnum::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionCategory()
    {
        return $this->belongsTo(TransactionCategory::class);
    }
}
