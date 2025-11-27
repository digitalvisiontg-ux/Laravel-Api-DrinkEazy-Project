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
        return $this->hasMany(Produit::class);
    }

    // Relation : une catégorie peut avoir plusieurs promotions
    // Promotions appliquées à la catégorie
    public function promotions()
    {
        return $this->morphMany(Promotion::class, 'cible');
    }

    // Promotions actives pour cette catégorie
    public function promotionsActives()
    {
        return $this->promotions()
            ->where('actif', true)
            ->where('debut', '<=', now())
            ->where('fin', '>=', now());
    }
}