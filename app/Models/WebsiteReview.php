<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteReview extends Model
{
    use HasFactory;

    protected $table = 'website_reviews';

    protected $fillable = [
        'user_id',
        'answers',
        'suggestion',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    /**
     * Relasi ke user yang membuat review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
