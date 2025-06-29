<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discussion extends BaseModel
{
    use HasFactory;

    public function comments()
    {
        return $this->hasMany(DiscussionComment::class);
    }
}
