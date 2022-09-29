<?php

namespace Outl1ne\NovaTwoFactor\Models;

use Illuminate\Database\Eloquent\Model;
use Outl1ne\NovaTwoFactor\NovaTwoFactor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFa extends Model
{
    protected $casts = [
        'enabled' => 'boolean',
        'confirmed' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(NovaTwoFactor::getTableName());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('nova-two-factor.user_model'));
    }
}
