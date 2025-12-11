<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeedbackMessage extends Model
{
    use HasFactory;

    protected $table = 'feedback_messages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'user_id', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
