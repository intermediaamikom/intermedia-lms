<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    private array $resources = ['user', 'event', 'division', 'role', 'permission'];
    private array $operations = ['create', 'view', 'update', 'delete'];

    private array $specials = ['join event'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->resources as $resource) {
            foreach ($this->operations as $operation) {
                Permission::create([ 'name' => $operation . ' ' . $resource ]);
            }
        }

        foreach ($this->specials as $special) {
            Permission::create([ 'name' => $special ]);
        }

        Role::create([ 'name' => 'Super Admin' ]);

        // Division Admin can 'create event', 'view event', 'update event', 'delete event', and 'join event'.
        $divisionAdmin = Role::create([ 'name' => 'Division Admin' ]);
        $divisionAdmin->givePermissionTo(collect($this->operations)->map(fn ($item, $key) => $item . ' event')->all());
        $divisionAdmin->givePermissionTo('join event');

        // Member can 'join event'.
        $member = Role::create([ 'name' => 'Member' ]);
        $member->givePermissionTo('join event');

        // Assign the first user (if exists) as a 'Super Admin'
        $user = User::first();
        if ($user != null) {
            $user->assignRole('Super Admin');
        }
    }
}
