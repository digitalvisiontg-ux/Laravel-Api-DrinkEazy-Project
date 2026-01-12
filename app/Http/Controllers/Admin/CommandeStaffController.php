<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Http\Request;

class CommandeStaffController extends Controller
{
    public function index(Request $request)
    {
        $commandes = Commande::with([
                'table:id,numero_table',
                'produits.produit:id,nomProd,taille'
            ])
            ->whereIn('status', ['pending', 'preparing'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'commandes' => $commandes
        ]);
    }

    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:preparing,ready'
    ]);

    $commande = Commande::findOrFail($id);

    $commande->update([
        'status' => $request->status
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Statut mis à jour',
        'status' => $commande->status
    ]);
}
}
