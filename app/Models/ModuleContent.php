<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleContent extends BaseModel
{
    use HasFactory;

    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }
}
