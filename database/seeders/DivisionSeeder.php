<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Division::factory()->create([
            'id' => '05ca9b6e-c6d3-4c3b-bf66-861d89e4137a',
            'name' => 'Programming',
        ]);

        Division::factory()->create([
            'id' => '99ecaa05-018e-4f95-9e61-63db1356d6c3',
            'name' => 'Multimedia',
        ]);

        Division::factory()->create([
            'id' => '64e2f870-0b1d-4a69-9098-4f57a80dbe5e',
            'name' => 'Sistem Robotika',
        ]);
    }
}
