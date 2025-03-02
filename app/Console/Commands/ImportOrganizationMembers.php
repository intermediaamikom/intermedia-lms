<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class ImportOrganizationMembers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'import:members';

    /**
     * The console command description.
     */
    protected $description = 'Import organization members from a JSON file and add them to the users table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prompt the user for the JSON file path
        $filePath = $this->ask('Enter the JSON file path');

        // Check if the file exists
        if (!File::exists($filePath)) {
            $this->error("File not found: $filePath");
            return;
        }

        // Read the file content
        $jsonContent = File::get($filePath);
        $jsonData = json_decode($jsonContent, true);

        // Validate the JSON structure
        if (!isset($jsonData['data']) || !is_array($jsonData['data'])) {
            $this->error('Invalid JSON structure. The root object must have a "data" array.');
            return;
        }

        // Process each member in the JSON file
        foreach ($jsonData['data'] as $member) {
            // Ensure required fields exist
            if (!isset($member['nim'], $member['nama'], $member['division_id'])) {
                $this->warn("Skipping entry due to missing required fields: " . json_encode($member));
                continue;
            }

            $division = $member['divisi'];
            // Prepare user data
            $nim = $member['nim'];
            $userData = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => $member['nama'],
                'username' => $nim,
                'email' => "$nim@students.amikompurwokerto.ac.id",
                'password' => Hash::make($nim),
                'division_id' => $member['division_id'],
                'total_point' => 0, // Default value
            ];

            // Check if the user already exists
            if (User::where('username', $nim)->exists()) {
                $this->warn("[$division] User with NIM $nim already exists. Skipping...");
                continue;
            }

            // Create the user
            $user = User::create($userData);

            // Assign "Member" role
            if ($user) {
                $user->assignRole('Member');
                $this->info("[$division] User {$user->name} (NIM: $nim) imported and assigned 'Member' role.");
            } else {
                $this->error("Failed to insert user: " . json_encode($userData));
            }
        }

        $this->info("Import process completed.");
    }
}