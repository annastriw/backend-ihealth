<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserHistoryPreTest extends BaseModel
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function preTest()
    {
        return $this->belongsTo(PreTest::class);
    }

    public function answer()
    {
        return $this->hasMany(UserAnswerPreTest::class);
    }
}
