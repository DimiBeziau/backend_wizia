<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mailings extends Model
{
    protected $fillable = [
   'idUser',
   'idListeClient',
    'subject',
   'body',
   'altBody',
   'fromName',
   'fromEmail',
   'date',
    'isPublished',
    'isValidated',
    ];
}
