<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['allow_registration', 'portfolio_maintenance'])]
class SystemSetting extends Model
{
    use HasFactory;

    protected $casts = [
        'allow_registration' => 'boolean',
        'portfolio_maintenance' => 'boolean',
    ];
}
