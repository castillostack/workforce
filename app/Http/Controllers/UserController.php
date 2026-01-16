<?php

namespace App\Http\Controllers;

use App\Services\Core\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Obtener perfil del usuario.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load('employee.team.department');

        return response()->json($user);
    }

    /**
     * Actualizar perfil del usuario.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'avatar' => 'sometimes|url',
        ]);

        $user = $this->userService->updateUser($request->user()->id, $validated);

        return response()->json($user);
    }

    /**
     * Cambiar contraseña.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $this->userService->updateUser($user->id, [
            'password' => Hash::make($request->password),
        ]);

        // Revocar tokens para forzar re-login
        $user->tokens()->delete();

        return response()->json(['message' => 'Contraseña cambiada exitosamente.']);
    }
}