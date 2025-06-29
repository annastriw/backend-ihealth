<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSet extends BaseModel
{
    use HasFactory;

    public function preTests()
    {
        return $this->hasMany(PreTest::class);
    }

    public function postTests()
    {
        return $this->hasMany(PostTest::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('created_at', 'asc');
    }

    public function screenings()
    {
        return $this->hasMany(Screening::class);
    }
}
