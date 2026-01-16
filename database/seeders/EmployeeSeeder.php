<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Team;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create departments and teams if not exist
        $department = Department::firstOrCreate(['name' => 'Dirección Nacional de Asistencia de los Servicios al Asegurado']);

        $teams = [
            'Administración DNASA' => Team::firstOrCreate(['name' => 'Administración DNASA', 'department_id' => $department->id]),
            'Control y Monitoreo' => Team::firstOrCreate(['name' => 'Control y Monitoreo', 'department_id' => $department->id]),
            'Coord. De Asist. Serv. Aseg.' => Team::firstOrCreate(['name' => 'Coord. De Asist. Serv. Aseg.', 'department_id' => $department->id]),
            'Ingeniería DNASA' => Team::firstOrCreate(['name' => 'Ingeniería DNASA', 'department_id' => $department->id]),
            'Jefe DNASA' => Team::firstOrCreate(['name' => 'Jefe DNASA', 'department_id' => $department->id]),
            'Redes Sociales DNASA' => Team::firstOrCreate(['name' => 'Redes Sociales DNASA', 'department_id' => $department->id]),
            'Supervisores DNASA' => Team::firstOrCreate(['name' => 'Supervisores DNASA', 'department_id' => $department->id]),
            'Voz DNASA' => Team::firstOrCreate(['name' => 'Voz DNASA', 'department_id' => $department->id]),
        ];

        // Create roles
        $roles = [
            'admin' => Role::firstOrCreate(['name' => 'admin']),
            'supervisor' => Role::firstOrCreate(['name' => 'supervisor']),
            'agent' => Role::firstOrCreate(['name' => 'agent']),
            'analyst' => Role::firstOrCreate(['name' => 'analyst']),
        ];

        $filePath = base_path('docs/data/empleados.csv');
        $handle = fopen($filePath, 'r');

        // Skip header
        fgetcsv($handle, 1000, ';');

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $workState = $data[13];
            $position = $data[10];

            // Assign team based on work_state
            $team = $teams[$workState] ?? null;

            // Assign role based on position
            $role = $this->assignRole($position, $roles);

            Employee::updateOrCreate(
                ['user_id' => $data[0]],
                [
                    'username' => $data[1], // agent_id
                    'employee_code' => 'EMP' . str_pad($data[0], 4, '0', STR_PAD_LEFT), // Generate code
                    'extension' => $data[2],
                    'team_id' => $team ? $team->id : null,
                    'hire_date' => $data[4] ?: now(),
                    'status' => $data[5],
                    'position' => $position,
                    'created_at' => $data[9] ?: now(),
                ]
            );

            // Assign role to user
            $user = \App\Models\User::find($data[0]);
            if ($user && $role) {
                $user->assignRole($role);
            }
        }

        fclose($handle);
    }

    private function assignRole(string $position, array $roles): ?Role
    {
        if (str_contains($position, 'Jefe') || str_contains($position, 'Director')) {
            return $roles['admin'];
        }
        if (str_contains($position, 'Coord') || str_contains($position, 'Supervisor')) {
            return $roles['supervisor'];
        }
        if (str_contains($position, 'Analista') || str_contains($position, 'Control')) {
            return $roles['analyst'];
        }
        if (str_contains($position, 'Operador')) {
            return $roles['agent'];
        }
        return $roles['agent']; // Default
    }
}