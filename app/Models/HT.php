<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class HT extends BaseModel
{
    use HasFactory;

    protected $table = 'hts';

    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }
}
