<?php

namespace App\Http\Controllers\usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Http\Responsable\usuarios\UsuarioIndex;
use App\Http\Responsable\usuarios\UsuarioStore;
use App\Http\Responsable\usuarios\UsuarioEdit;
use App\Http\Responsable\usuarios\UsuarioUpdate;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class UsuariosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new UsuarioIndex();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new UsuarioStore($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idUsuario)
    {
        return new UsuarioEdit($idUsuario);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idUsuario)
    {
        return new UsuarioUpdate($request, $idUsuario);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function consultarId()
    {
        $identificacion = request('identificacion', null);
        
        // Consultamos si ya existe un usuario con la cedula ingresada
        return Usuario::where('identificacion', $identificacion)->first();
    }

    public function consultaUsuario()
    {
        try {
            $usuario = request('usuario', null);

            // Consultamos si ya existe este usuario específico
            $consultaUsuario = Usuario::where('usuario', $usuario)->first();

            if ($consultaUsuario) {
                return response()->json($consultaUsuario);
            }
        } catch (Exception $e) {
            return response()->json(['error_bd'=>$e->getMessage()]);
        }
    }


    public function queryUsuarioUpdate($idUsuario)
    {
        try {
            // Consultamos el id del usuario
            return Usuario::where('id_usuario', $idUsuario)->first();
        } catch (Exception $e) {
            return response()->json('error_bd');
        }
    }


    // public function cambiarClave(Request $request, $idUsuario)
    // {
    //     $claveNueva = request('clave', null);

    //     try {
    //         Usuario::where('id_usuario',$idUsuario)
    //             ->update([
    //                 'clave' => Hash::make($claveNueva),
    //         ]);
    //         return response()->json(true);

    //     } catch (Exception $e) {
    //         return response()->json(['error_bd' => $e->getMessage()]);
    //     }
    // }

    public function cambiarClave(Request $request, $idUsuario)
    {
        $claveNueva = request('clave', null);

        try {
            // Al actualizar, cambiamos la clave Y generamos un nuevo token aleatorio.
            // Esto "rompe" la coincidencia con cualquier sesión activa del usuario.
            Usuario::where('id_usuario', $idUsuario)
                ->update([
                    'clave'         => Hash::make($claveNueva),
                    'session_token' => Str::random(40), // <--- Aquí cambiamos el token de sesión
                ]);
                
            return response()->json(true);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }

    public function consultaRecuperarClave(Request $request)
    {
        $email = request('email', null);
        $identificacion = request('identificacion', null);

        try {
             return Usuario::select('id_usuario','usuario','identificacion','email')
                ->where('email', $email)
                ->where('identificacion', $identificacion)
                ->first();
        } catch (Exception $e) {
            return response()->json('error_bd');
        }
    }

    public function inactivarUsuario($idUsuario)
    {
        try {

            $user = Usuario::find($idUsuario);
            $user->id_estado = 2;
            $user->save();

        } catch (Exception $e) {
            return response()->json('error_bd');
        }
    }

    public function actualizarClaveFallas(Request $request, $idUsuario)
    {
        $contador = request('clave_fallas', null);
        try {
            $user = Usuario::find($idUsuario);
            $user->clave_fallas = $contador;
            $user->save();
        } catch (Exception $e) {
            return response()->json('error_bd');
        }
    }

    public function validarEmail(Request $request)
    {
        $email = $request->input('email');
        $existe = Usuario::where('email', $email)->exists();
        
        return response()->json([
            'valido' => !$existe
        ]);
    }

    public function validarIdentificacion(Request $request)
    {
        $identificacion = $request->input('identificacion');
        $existe = Usuario::where('identificacion', $identificacion)->exists();
        
        return response()->json([
            'valido' => !$existe
        ]);
    }

    public function validarEmailLogin(Request $request)
    {
        $email = $request->input('email');

        $user = Usuario::with('empresa')->where('email', $email)->first();
        return response()->json($user);
    }

    public function consultaUsuarioLogueado($idUsuario)
    {
        $user = Usuario::leftJoin('roles', 'roles.id', '=', 'usuarios.id_rol')
            ->leftJoin('empresas', 'empresas.id_empresa', '=', 'usuarios.id_empresa')
            ->where('id_usuario', $idUsuario)
            ->select(
                'nombre_usuario',
                'apellido_usuario',
                'name AS rol',
                'logo_empresa',
                'nombre_empresa'
            )
            ->first();

        return response()->json($user);
    }

    public function consultarSessionToken($idUsuario)
    {
        try {
            $sessionTokenUser = DB::connection('mysql')
                                ->table('usuarios')
                                ->select('session_token')
                                ->where('id_usuario', $idUsuario)
                                ->first();
            
            // Si el usuario no existe, devolvemos un token nulo claramente
            if (!$sessionTokenUser) {
                return response()->json(['session_token' => null], 404);
            }

            return response()->json($sessionTokenUser);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }

    public function actualizarTokenSesion(Request $request, $idUsuario)
    {
        // 1. Validación básica
        if (!$request->has('session_token')) {
            return response()->json(['error' => 'Token no proporcionado'], 400);
        }

        try {
            // 2. Intentar la actualización
            $actualizado = Usuario::where('id_usuario', $idUsuario)
                ->update([
                    'session_token' => $request->session_token,
                    // Si tienes un campo de auditoría en la tabla usuarios, podrías usar $request->id_audit
                ]);

            if ($actualizado) {
                return response()->json(true);
            }

            return response()->json(['error' => 'Usuario no encontrado'], 404);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
