<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleContent extends BaseModel
{
    use HasFactory;

    public function subModule(): BelongsTo
    {
        return $this->belongsTo(SubModule::class);
    }

    public function userOpens(): HasMany
    {
        return $this->hasMany(UserModuleContentOpen::class);
    }
}
