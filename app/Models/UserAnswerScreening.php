<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAnswerScreening extends BaseModel
{
    use HasFactory;

    public function userHistoryScreening()
    {
        return $this->belongsTo(UserHistoryScreening::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(Option::class, 'selected_option_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id')->orderBy('created_at', 'asc');
    }
}
