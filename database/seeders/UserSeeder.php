<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
        ]);
        $superadmin->assignRole('Super Admin');

        $admin = User::factory()->create([
            'email' => 'admin@gmail.com',
            'division_id' => '05ca9b6e-c6d3-4c3b-bf66-861d89e4137a',
        ]);
        $admin->assignRole('Admin');

        $member1 = User::factory()->create([
            'email' => 'member1@gmail.com',
            'division_id' => '05ca9b6e-c6d3-4c3b-bf66-861d89e4137a',
        ]);
        $member1->assignRole('Member');
    }
}
