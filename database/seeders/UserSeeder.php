<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('docs/data/usuarios.csv');
        $handle = fopen($filePath, 'r');

        // Skip header
        fgetcsv($handle, 1000, ';');

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            User::updateOrCreate(
                ['username' => $data[1]], // Unique key
                [
                    'id' => $data[0],
                    'name' => $data[8], // full_name as name
                    'email' => $data[2],
                    'password' => Hash::make($data[3]), // Hash the password
                    'is_active' => (bool) $data[4],
                    'created_at' => $data[5] ?: now(),
                    'updated_at' => $data[6] ?: now(),
                    'full_name' => $data[8],
                    // role_id left null for now, assign later based on employee position
                ]
            );
        }

        fclose($handle);
    }
}