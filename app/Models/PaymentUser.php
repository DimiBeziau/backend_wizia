<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentUser extends Model
{
    protected $fillable = [
        'idUser',
        'idAbonnements',
        'datePayement',
        'dateStart',
        'dateEnd',
        'dateCancel',
        'cancelAbonnement',
        'paymentMethod',
        'idTransaction',
        'currency',
        'isRecurring',
        'notes',
    ];
}
