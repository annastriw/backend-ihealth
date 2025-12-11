<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SusResponse extends Model
{
    use HasFactory;

    protected $table = 'sus_responses';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'total_score',
        'interpretation',
    ];

    public function details()
    {
        return $this->hasMany(SusResponseDetail::class, 'sus_response_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
