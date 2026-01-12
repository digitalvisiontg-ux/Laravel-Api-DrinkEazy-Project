<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    //
    public function table()
{
    return $this->belongsTo(Table::class);
}

public function produits()
{
    return $this->hasMany(CommandeProduit::class);
}
}
