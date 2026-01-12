<?php

namespace App\Http\Controllers\User;

use App\Models\Table;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function store(Request $request, QrCodeService $qrService)
    {
        $request->validate([
            'libelle' => ['required', 'string', 'max:100', 'unique:tables,libelle'],
        ]);

        $table = Table::create([
            'numero_table' => $this->generateNumeroTable(),
            'libelle' => $request->libelle,
            'token' => (string) Str::uuid(),
            'actif' => true,
        ]);

        $qrPath = $qrService->generateForTable($table);
        $table->update(['qr_image' => $qrPath]);

        return response()->json([
            'success' => true,
            'data' => [
                'table_id' => $table->id,
                'numero_table' => $table->numero_table,
                'libelle'  => $table->libelle,
            ]
        ], 201);
    }

    public function verify(string $token)
    {
        // On cherche directement le token tel qu'il est lu par le scanner
        $table = Table::where('token', $token)
            ->where('actif', true)
            ->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Table introuvable, inactive ou QR Code invalide'
            ], 404);
        }

        return $this->successResponse($table);
    }

    public function verifyManual(string $numeroTable)
    {
        $table = Table::where('numero_table', strtoupper($numeroTable))
            ->where('actif', true)
            ->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de table invalide'
            ], 404);
        }

        return $this->successResponse($table);
    }

    private function successResponse(Table $table)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'table_id' => $table->id,
                'numero_table' => $table->numero_table,
                'libelle'  => $table->libelle,
            ]
        ]);
    }

    public function generateNumeroTable(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $length = 4;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (Table::where('numero_table', $code)->exists());

        return $code;
    }
}
