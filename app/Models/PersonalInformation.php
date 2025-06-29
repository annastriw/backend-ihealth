<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class PersonalInformation extends BaseModel
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
