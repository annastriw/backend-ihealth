<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DM extends BaseModel
{
    use HasFactory;
    protected $table = 'dms';

    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }
}
