<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PieceJointeMailings extends Model
{
    protected $fillable = [
        'idPieceJointe',
        'idMailing',
    ];

    public function pieceJointe()
    {
        return $this->belongsTo(PieceJointes::class, 'idPieceJointe');
    }
}
