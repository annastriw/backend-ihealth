<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends BaseModel
{
    use HasFactory;

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class)->orderBy('option_index', 'asc');
    }

    public function userAnswerScreening()
    {
        return $this->hasMany(UserAnswerScreening::class, 'question_id');
    }
}
