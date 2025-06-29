<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscussionCommentAnswer extends BaseModel
{
    use HasFactory;

    public function discussionComment()
    {
        return $this->belongsTo(DiscussionComment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
