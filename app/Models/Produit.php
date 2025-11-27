<?php

namespace App\Models;

use Carbon\Carbon;
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

    // Promotions directes appliquées à ce produit
    public function promotions()
    {
        return $this->morphMany(Promotion::class, 'cible');
    }

    // Récupère les promotions actives de ce produit
    public function promotionsActives()
    {
        return $this->promotions()
            ->where('actif', true)
            ->where('debut', '<=', now())
            ->where('fin', '>=', now());
    }
}
