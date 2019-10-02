<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'email',
        'fb_messenger_id',
        'first_name',
        'last_name',
        'phone',
        'sticky_phone_number_id',
        'team_id',
        'time_zone',
        'twitter_id',
        'unsubscribed_status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customAttributes(): HasMany
    {
        return $this->hasMany(CustomAttribute::class);
    }
}
