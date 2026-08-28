<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login de usuario y generación de JWT
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'clave' => 'required|string',
        ]);

        $credentials = [
            'email'     => $request->input('email'),
            'password'  => $request->input('clave'), // Auth mapea esto internamente mediante getAuthPassword()
        ];

        if (! $token = Auth::attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Obtener el usuario autenticado actualmente a partir del Token
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'data'   => Auth::user()
        ]);
    }

    /**
     * Cierra la sesión (Invalida el token actual)
     */
    public function logout()
    {
        Auth::logout();

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesión cerrada correctamente.'
        ]);
    }

    /**
     * Refresca el token expirado por uno nuevo
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Estructura la respuesta con el Token de acceso
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status'       => 'success',
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::factory()->getTTL() * 60, // Tiempo de vida en segundos
            'usuario'      => [
                'id_usuario' => Auth::user()->id_usuario,
                'nombre'     => Auth::user()->nombre_usuario,
                'usuario'    => Auth::user()->usuario,
                'id_empresa' => Auth::user()->id_empresa,
                'id_rol'     => Auth::user()->id_rol,
            ]
        ]);
    }
}
