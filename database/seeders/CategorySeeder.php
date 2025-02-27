<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => 'A', 'kategori' => 'Pengurus'],
            ['code' => 'B', 'kategori' => 'Panitia'],
            ['code' => 'C', 'kategori' => 'Pembicara'],
            ['code' => 'D', 'kategori' => 'Juara'],
            ['code' => 'E', 'kategori' => 'Finalis'],
            ['code' => 'F', 'kategori' => 'Peserta'],
            ['code' => 'G', 'kategori' => 'Juri'],
            ['code' => 'H', 'kategori' => 'Mitra'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
