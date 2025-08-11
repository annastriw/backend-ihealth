<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserHistoryPostTest extends BaseModel
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postTest()
    {
        return $this->belongsTo(PostTest::class);
    }

    public function answer()
    {
        return $this->hasMany(UserAnswerPostTest::class);
    }
}
