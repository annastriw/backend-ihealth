<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswerPostTest extends BaseModel
{
    use HasFactory;

    public function userHistoryPostTest()
    {
        return $this->belongsTo(UserHistoryPostTest::class);
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
