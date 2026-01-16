<?php

namespace App\Services\Core;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Crea un usuario y su empleado asociado.
     *
     * @param array $data
     * @return User
     */
    public function createUserWithEmployee(array $data): User
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'full_name' => $data['full_name'],
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
            ]);

            Employee::create([
                'user_id' => $user->id,
                'username' => $data['username'],
                'employee_code' => $data['employee_code'],
                'team_id' => $data['team_id'] ?? null,
                'position' => $data['position'] ?? null,
                'extension' => $data['extension'] ?? null,
                'hire_date' => $data['hire_date'] ?? now(),
                'status' => $data['status'] ?? 'active',
            ]);

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando user: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza un usuario.
     *
     * @param int $userId
     * @param array $data
     * @return User
     */
    public function updateUser(int $userId, array $data): User
    {
        $user = User::findOrFail($userId);
        $user->update($data);
        return $user;
    }

    /**
     * Asigna roles a un usuario.
     *
     * @param int $userId
     * @param array $roles
     */
    public function assignRoles(int $userId, array $roles): void
    {
        $user = User::findOrFail($userId);
        $user->syncRoles($roles);
    }
}