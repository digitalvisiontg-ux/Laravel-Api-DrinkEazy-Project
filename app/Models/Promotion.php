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
        'type', 'type_reduction', 'valeur_reduction',
        'quantite_achat', 'quantite_offerte',
        'heure_debut', 'heure_fin',
        'cible_type', 'cible_id',
        'debut', 'fin', 'actif'
    ];

    // Relation polymorphe avec le modèle cible (Produit ou Categorie)
    public function cible()
    {
        return $this->morphTo();
    }

    // Scope pour ne récupérer que les promotions actives
    public function scopeActives($query)
    {
        return $query->where('actif', true)
                     ->where('debut', '<=', now())
                     ->where('fin', '>=', now());
    }
}