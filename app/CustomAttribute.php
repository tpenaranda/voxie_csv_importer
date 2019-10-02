<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomAttribute extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'key' => 'string',
        'value' => 'string',
    ];

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class);
    }
}
