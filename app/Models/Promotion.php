<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    //
    use HasFactory;

    protected $table = 'promotions';

    protected $fillable = [
        'nomPromo',
        'typePromo',
        'valeurPromo',
        'qteAchat',
        'qteOfferte',
        'debutPromo',
        'finPromo',
        'ciblePromo',
        'categorieId',
        'produitId',
        'actif',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produitId');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorieId');
    }
}
