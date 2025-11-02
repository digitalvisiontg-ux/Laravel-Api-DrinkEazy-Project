<?php

namespace Database\Seeders;

use App\Models\Bar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Bar::create([
            'name' => 'Le Chill Bar',
            'address' => 'Lomé centre',
            'phone' => '+22890000000',
        ]);
    }
}
