<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    //
    public function verifyByToken(Request $request)
    {
        $request->validate([
            'token' => 'required|uuid'
        ]);

        $table = Table::where('token', $request->token)
            ->where('actif', true)
            ->first();

        if (!$table) {
            return response()->json([
                'message' => 'Table invalide ou inactive'
            ], 404);
        }

        return response()->json([
            'table_id' => $table->id,
            'numero_table' => $table->numero_table
        ]);
    }

    /**
     * Vérification manuelle (volontairement plus contraignante)
     */
    public function verifyManually(Request $request)
    {
        $request->validate([
            'numero_table' => 'required|integer',
            'code_verification' => 'required|string|min:4'
        ]);

        $table = Table::where('numero_table', $request->numero_table)
            ->where('actif', true)
            ->first();

        if (!$table || $request->code_verification !== 'A3F9') {
            return response()->json([
                'message' => 'Vérification échouée'
            ], 403);
        }

        return response()->json([
            'table_id' => $table->id,
            'numero_table' => $table->numero_table
        ]);
    }
}
