<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class HD extends BaseModel
{
    use HasFactory;
    protected $table = 'hds';

    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }
}
