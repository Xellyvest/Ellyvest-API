<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfitLossHistory extends Model
{
    use HasFactory;
    use UUID;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
