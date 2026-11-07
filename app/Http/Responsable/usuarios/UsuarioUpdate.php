<?php

namespace App\Http\Responsable\usuarios;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Traits\AsignarPermisosPorRol;

class UsuarioUpdate implements Responsable
{
    use AsignarPermisosPorRol;

    protected $request;
    protected $idUsuario;

    public function __construct(Request $request, $idUsuario)
    {
        $this->request = $request;
        $this->idUsuario = $idUsuario;
    }

    public function toResponse($request)
    {
        $usuario = Usuario::find($this->idUsuario);

        if ($usuario) {
            try {
                $idRolAnterior = $usuario->id_rol;
                $idRol = $request->input('id_rol');

                $usuario->nombre_usuario = $request->input('nombre_usuario');
                $usuario->apellido_usuario = $request->input('apellido_usuario');
                $usuario->id_tipo_documento = $request->input('id_tipo_documento');
                $usuario->identificacion = $request->input('identificacion');
                $usuario->email = $request->input('email');
                $usuario->id_rol = $idRol;
                $usuario->id_tipo_persona = $request->input('id_tipo_persona');
                $usuario->numero_telefono = $request->input('numero_telefono');
                $usuario->celular = $request->input('celular');
                $usuario->id_genero = $request->input('id_genero');
                $usuario->direccion = $request->input('direccion');
                $usuario->id_estado = $request->input('id_estado');
                $usuario->fecha_contrato = $request->input('fecha_contrato');
                $usuario->fecha_terminacion_contrato = $request->input('fecha_terminacion_contrato');
                $usuario->id_empresa = $request->input('id_empresa');
                $usuario->update();

                if ($idRolAnterior != $idRol) {
                    $this->asignarPermisosPorRol($idRol, $usuario);
                }

                return response()->json(['success' => true]);
            } catch (Exception $e) {
                return response()->json(['error_bd' => $e->getMessage()]);
            }
        }
    }
}
