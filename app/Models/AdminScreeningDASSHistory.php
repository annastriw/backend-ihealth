<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AdminScreeningDASSHistory extends Model
{
    protected $table = 'screening_dass_histories';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'answers' => 'array',
    ];

    protected $fillable = ['id', 'user_id', 'answers'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
