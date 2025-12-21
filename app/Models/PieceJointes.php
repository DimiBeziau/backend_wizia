<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PieceJointes extends Model
{
    protected $fillable = [
            'path',
            'type',
            'idUser'
        ];
    public function mailings()
{
    return $this->hasMany(PieceJointeMailings::class, 'idPieceJointe');
}

}
