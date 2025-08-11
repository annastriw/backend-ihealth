<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends BaseModel
{
    use HasFactory;

    public function subModules()
    {
        return $this->hasMany(SubModule::class);
    }
}
