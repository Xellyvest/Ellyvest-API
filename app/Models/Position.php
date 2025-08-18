<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;
    use UUID;

    protected $fillable = [
        'id',
        'user_id',
        'asset_id',
        'asset_type',
        'account',
        'auto_plan_investment_id',
        'savings_id',
        'price',
        'quantity',
        'amount',
        'status',
        'entry',
        'exit',
        'leverage',
        'dividends',
        'interval',
        'tp',
        'sl',
        'extra',
        'created_at',
    ];

    public function getQuerySelectables(): array
    {
        $table = $this->getTable();

        return [
            "{$table}.id",
            "{$table}.user_id",
            "{$table}.amount",
            "{$table}.price",
            "{$table}.quantity",
            "{$table}.type",
            "{$table}.status",
            "{$table}.extra",
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function savings()
    {
        return $this->belongsTo(Savings::class);
    }

    public function autoInvest()
    {
        return $this->belongsTo(AutoPlanInvestment::class, 'auto_plan_investment_id');
    }
}
