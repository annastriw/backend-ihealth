<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscussionComment extends BaseModel
{
    use HasFactory;

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medical()
    {
        return $this->belongsTo(User::class, 'medical_id')
            ->where('role', 'medical_personal');
    }

    public function answers()
    {
        return $this->hasMany(DiscussionCommentAnswer::class);
    }
}
