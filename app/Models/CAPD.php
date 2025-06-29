<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAPD extends BaseModel
{
    use HasFactory;
    protected $table = 'capds';

    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }
}
