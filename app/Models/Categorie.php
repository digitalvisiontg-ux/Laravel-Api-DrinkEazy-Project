<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    //
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'nomCat',
        'descCat',
        'actif',
    ];

    // Relation : une catégorie possède plusieurs produits
    public function produits()
    {
        return $this->hasMany(Produit::class, 'categorieId');
    }
}
