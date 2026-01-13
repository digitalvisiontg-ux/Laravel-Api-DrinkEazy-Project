<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    //

    protected $fillable = [
        'user_id',
        'guest_token',
        'guest_infos',
        'table_id',
        'status',
        'commentaire_client',
        'total',
    ];
    public function produits()
    {
        return $this->hasMany(CommandeProduit::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
