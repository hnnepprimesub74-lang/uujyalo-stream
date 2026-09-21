<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingContact extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'marketing_opt_out',
    ];

    protected $casts = [
        'marketing_opt_out' => 'boolean',
    ];
}
