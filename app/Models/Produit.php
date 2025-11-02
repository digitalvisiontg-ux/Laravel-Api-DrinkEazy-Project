<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    //
    use HasFactory;

    protected $table = 'produits';

    protected $fillable = [
        'nomProd',
        'taille',
        'prixBase',
        'descProd',
        'qteStock',
        'actif',
        'imageUrl',
        'categorieId',
    ];

    // Relation : un produit appartient à une catégorie
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorieId');
    }

    // Relation : un produit peut avoir plusieurs promotions
    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'produitId');
    }
}
