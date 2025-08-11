<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class KM extends BaseModel
{
    use HasFactory;

    protected $table = 'kms';

    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }
}
