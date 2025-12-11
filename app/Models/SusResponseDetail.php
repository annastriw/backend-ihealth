<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SusResponseDetail extends Model
{
    use HasFactory;

    protected $table = 'sus_response_details';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'sus_response_id',
        'question_id',
        'answer_raw',
        'answer_converted',
    ];

    public function response()
    {
        return $this->belongsTo(SusResponse::class, 'sus_response_id');
    }
}
