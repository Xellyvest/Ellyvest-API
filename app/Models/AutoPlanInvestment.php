<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class AutoPlanInvestment extends Model
{
    use UUID;

    protected $fillable = [
        'user_id',
        'auto_plan_id',
        'amount',
        'start_at',
        'expire_at',
        'credited',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(AutoPlan::class, 'auto_plan_id');
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }
}
