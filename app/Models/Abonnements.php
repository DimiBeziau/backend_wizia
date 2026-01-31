<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnements extends Model
{
    use HasFactory;

    protected $fillable = [
        'isFree',
        'isPremium',
        'isProfessionnel',
    ];
}
