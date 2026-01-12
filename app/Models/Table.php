<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Str;

class Table extends Model
{
    //
    protected $fillable = [
        'numero_table',
        'libelle',
        'token',
        'actif'
    ];

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}
