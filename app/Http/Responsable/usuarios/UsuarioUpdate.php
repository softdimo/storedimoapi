<?php

namespace App\Http\Responsable\usuarios;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use OwenIt\Auditing\Facades\Auditor;
use Illuminate\Support\Facades\Config;
use App\Models\Usuario;

class UsuarioUpdate implements Responsable
{
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
                $usuario->nombre_usuario = $request->input('nombre_usuario');
                $usuario->apellido_usuario = $request->input('apellido_usuario');
                $usuario->id_tipo_documento = $request->input('id_tipo_documento');
                $usuario->identificacion = $request->input('identificacion');
                $usuario->email = $request->input('email');
                $usuario->id_rol = $request->input('id_rol');
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

                return response()->json(['success' => true]);
            } catch (Exception $e) {
                return response()->json(['error_bd' => $e->getMessage()]);
            }
        }
    }
}
