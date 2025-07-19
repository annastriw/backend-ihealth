<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ScreeningScoring extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'question_set_id',
        'name',
        'type',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function histories()
    {
        return $this->hasMany(UserHistoryScreeningScoring::class, 'screening_scoring_id');
    }
}
