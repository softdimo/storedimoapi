<?php

namespace App\Http\Responsable\usuarios;

use Illuminate\Contracts\Support\Responsable;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Traits\AsignarPermisosPorRol;

class UsuarioStore implements Responsable
{
    use AsignarPermisosPorRol;

    public function toResponse($request)
    {
        $nombreUsuario = request('nombre_usuario', null);
        $apellidoUsuario = request('apellido_usuario', null);
        $idTipoDocumento = request('id_tipo_documento', null);
        $identificacion = request('identificacion', null);
        $usuario  = request('usuario', null);
        $email = request('email', null);
        $idRol = request('id_rol', null);
        $idTipoPersona = request('id_tipo_persona', null);
        $numeroTelefono = request('numero_telefono', null);
        $celular = request('celular', null);
        $idGenero = request('id_genero', null);
        $direccion = request('direccion', null);
        $fechaContrato = request('fecha_contrato', null);
        $fechaTerminacionContrato = request('fecha_terminacion_contrato', null);
        $idEstado = request('id_estado', null);
        $clave = request('clave', null);
        $claveFallas = request('clave_fallas', null);
        $idEmpresa = request('id_empresa', null);
        $correoEmpresa = null;
        $consultaUsuario = null;
        $datosEmpresa = null;

        if($idRol == 1) {
            $consultaUsuario = Usuario::where('id_rol', $idRol)
                                ->where('id_empresa', $idEmpresa)
                                ->first();
        }

        $nuevoUsuario = Usuario::create([
            'nombre_usuario'             => ucwords($nombreUsuario),
            'apellido_usuario'           => ucwords($apellidoUsuario),
            'id_tipo_documento'          => $idTipoDocumento,
            'identificacion'             => $identificacion,
            'usuario'                    => $usuario,
            'email'                      => $email,
            'clave'                      => $clave,
            'clave_fallas'               => $claveFallas,
            'id_estado'                  => $idEstado,
            'id_rol'                     => $idRol,
            'id_tipo_persona'            => $idTipoPersona,
            'numero_telefono'            => $numeroTelefono,
            'celular'                    => $celular,
            'id_genero'                  => $idGenero,
            'direccion'                  => $direccion,
            'fecha_contrato'             => $fechaContrato,
            'fecha_terminacion_contrato' => $fechaTerminacionContrato,
            'id_empresa'                 => $idEmpresa,
        ]);

        if (isset($nuevoUsuario) && !is_null($nuevoUsuario) && !empty($nuevoUsuario)) {
            if ($idRol == 1 && is_null($consultaUsuario)) {
                $datosEmpresa = Empresa::select('email_empresa')
                    ->where('id_empresa', $idEmpresa)
                    ->where('id_estado', 1)
                    ->first();
            }

            $this->asignarPermisosPorRol($idRol, $nuevoUsuario);

            return response()->json([
                'success'        => true,
                'message'        => 'Usuario creado correctamente',
                'usuario'        => $nuevoUsuario,
                'correo_empresa' => $datosEmpresa->email_empresa ?? null,
            ]);
        } else {
            return abort(404, 'No existe este usuario');
        }
    }
}
