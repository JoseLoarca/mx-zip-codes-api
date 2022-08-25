<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZipCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['zip_code'];

    ///////////////////
    // Relationships //
    ///////////////////

    /**
     * ZipCode -> Settlements relationship
     *
     * @return HasMany
     */
    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }
}
