<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatronType extends Model
{
    protected $guarded = [];

    public function patronDetails() : hasMany
    {
        return $this->hasMany(PatronDetail::class);
    }
}
