<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screening extends BaseModel
{
    use HasFactory;

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function userHistories()
    {
        return $this->hasMany(UserHistoryScreening::class);
    }
}
