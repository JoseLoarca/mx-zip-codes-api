<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'settlement_key', 'zone_type', 'settlement_type_id', 'municipality_id', 'locality_id', 'zip_code_id'
    ];

    ///////////////////
    // Relationships //
    ///////////////////

    /**
     * Settlement -> SettlementType relationship
     *
     * @return BelongsTo
     */
    public function settlementType(): BelongsTo
    {
        return $this->belongsTo(SettlementType::class);
    }

    /**
     * Settlement -> ZipCode relationship
     *
     * @return BelongsTo
     */
    public function zipCode(): BelongsTo
    {
        return $this->belongsTo(ZipCode::class);
    }

    /**
     * Settlement -> Locality relationship
     *
     * @return BelongsTo
     */
    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }

    /**
     * Settlement -> Municipality relationship
     *
     * @return BelongsTo
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
}
