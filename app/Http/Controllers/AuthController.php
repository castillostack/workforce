<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login de usuario.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Credenciales incorrectas.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Login web de usuario (para formularios HTML).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function webLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Credenciales incorrectas.',
            ])->withInput($request->only('username'));
        }

        // Verificar si el usuario está activo
        if (!$user->is_active) {
            return back()->withErrors([
                'username' => 'Su cuenta está desactivada.',
            ])->withInput($request->only('username'));
        }

        // Login del usuario usando sesión web
        Auth::login($user, $request->has('remember'));

        return redirect()->intended('/');
    }

    /**
     * Logout web de usuario.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function webLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/signin');
    }

    /**
     * Obtener usuario autenticado.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Registro de nuevo usuario (solo para admins o sistema).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'full_name' => 'required|string',
        ]);

        $user = \App\Models\User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
        ]);

        return response()->json($user, 201);
    }
}