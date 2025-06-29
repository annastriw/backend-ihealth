<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubModule extends BaseModel
{
    use HasFactory;

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function capd()
    {
        return $this->hasMany(CAPD::class);
    }

    public function moduleContents()
    {
        return $this->hasMany(ModuleContent::class);
    }

    public function hd()
    {
        return $this->hasMany(HD::class);
    }

    public function postTests()
    {
        return $this->hasMany(PostTest::class);
    }

    public function preTests()
    {
        return $this->hasMany(PreTest::class);
    }
}
