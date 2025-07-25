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

    public function ht()
    {
        return $this->hasMany(HT::class);
    }

    public function dm()
    {
        return $this->hasMany(DM::class);
    }

    public function km()
    {
        return $this->hasMany(KM::class);
    }

    public function preTests()
    {
        return $this->hasMany(PreTest::class);
    }

    public function moduleContents()
    {
        return $this->hasMany(ModuleContent::class)->orderBy('created_at', 'asc');
    }

    public function postTests()
    {
        return $this->hasMany(PostTest::class);
    }
}
