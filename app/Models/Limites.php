<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Limites extends Model
{
    use HasFactory;

    protected $fillable = [
        'idAbonnement',
        'nomModule',
        'islimitAbonnement',
        'isprofessionnelle',
        'isLimitTexte',
        'isLimiteImage',
    ];
}
