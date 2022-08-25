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

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'zip_code';
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['settlements.settlementType'];

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
