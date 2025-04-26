<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
  protected $fillable = [
    "datePost",
    "idUser",
    "idPieceJointe",
    "post"
  ];
}
