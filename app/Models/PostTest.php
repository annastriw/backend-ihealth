<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTest extends BaseModel
{
    use HasFactory;

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }
}
