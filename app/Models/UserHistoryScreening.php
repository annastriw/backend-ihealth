<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserHistoryScreening extends BaseModel
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function screening()
    {
        return $this->belongsTo(Screening::class);
    }

    public function answer()
    {
        return $this->hasMany(UserAnswerScreening::class);
    }
}
