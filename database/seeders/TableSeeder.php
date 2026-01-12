<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Services\QrCodeService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(QrCodeService $qrService): void
    {
        $zones = [
            'Salle principale',
            'Terrasse',
            'VIP',
        ];

        $tablePerZone = 2;
        $indexGlobal = 1;

        foreach ($zones as $zone) {
            for ($i = 1; $i <= $tablePerZone; $i++) {

                $table = Table::create([
                    'numero_table' => $this->generateNumeroTable(), // 
                    'libelle' => sprintf('%s – Table %02d', $zone, $i), 
                    'token' => (string) Str::uuid(),
                    'actif' => true,
                ]);

                // QR Code basé uniquement sur le token
                $path = $qrService->generateForTable($table);
                $table->update(['qr_image' => $path]);

                $indexGlobal++;
            }
        }
    }

    private function generateNumeroTable(): string
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
