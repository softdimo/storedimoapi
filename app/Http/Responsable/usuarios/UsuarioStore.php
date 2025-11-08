<?php

namespace App\Http\Responsable\usuarios;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\ModelHasPermissions;

class UsuarioStore implements Responsable
{
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

        $nuevoUsuario = Usuario::create([
            'nombre_usuario' => ucwords($nombreUsuario),
            'apellido_usuario' => ucwords($apellidoUsuario),
            'id_tipo_documento' => $idTipoDocumento,
            'identificacion' => $identificacion,
            'usuario' => $usuario,
            'email' => $email,
            'clave' => $clave,
            'clave_fallas' => $claveFallas,
            'id_estado' => $idEstado,
            'id_rol' => $idRol,
            'id_tipo_persona' => $idTipoPersona,
            'numero_telefono' => $numeroTelefono,
            'celular' => $celular,
            'id_genero' => $idGenero,
            'direccion' => $direccion,
            'fecha_contrato' => $fechaContrato,
            'fecha_terminacion_contrato' => $fechaTerminacionContrato,
            'id_empresa' => $idEmpresa,
        ]);

        if (isset($nuevoUsuario) && !is_null($nuevoUsuario) && !empty($nuevoUsuario))
        {
            $this->asignarPermisosPorRol($idRol, $nuevoUsuario);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'usuario' => $nuevoUsuario
            ]);
        } else {
            return abort(404, 'No existe este usuario');
        }
    }

    private function asignarPermisosPorRol($idRol, $nuevoUsuario)
    {
        $mapaRolesPermisos = [
            1 => 'Admin',
            2 => 'VendedorEmpleado',
            3 => 'Softdimo',
            4 => 'VendedorEmpleado',
            5 => 'SuperAdmin',
            6 => 'Consulta',
            7 => 'Pruebas'
        ];

        $tipoPermiso = $mapaRolesPermisos[$idRol] ?? 'Consulta';
        $metodo = 'permisos' . $tipoPermiso;
        $permisos = method_exists($this, $metodo) ? $this->$metodo() : [];

        $usuario = Usuario::where('identificacion', $nuevoUsuario->identificacion)->first();

        // Eliminar todos los permisos existentes de una sola vez
        ModelHasPermissions::where('model_id', $usuario->id_usuario)
                            ->where('model_type', Usuario::class)
                            ->delete();

        if($idRol != 8)
        {
            // Asignar nuevos permisos
            foreach ($permisos as $permissionId)
            {
                ModelHasPermissions::create([
                    'permission_id' => $permissionId,
                    'model_type' => Usuario::class,
                    'model_id' => $usuario->id_usuario
                ]);
            }
        }
    }

    private function permisosSoftdimo()
    {
        return range(1, 65);
    }

    private function permisosSuperAdmin()
    {
        return array_diff($this->permisosSoftdimo(), [4, 6, 12, 22, 50]);
    }

    private function permisosAdmin()
    {
        return array_diff($this->permisosSoftdimo(), [2, 4, 6, 9, 10, 11, 12, 22, 50]);
    }

    private function permisosPruebas()
    {
        return $this->permisosSoftdimo(); // Igual a Softdimo
    }

    private function permisosVendedorEmpleado()
    {
        return array_diff($this->permisosSoftdimo(), [2, 3, 4, 6, 9, 10, 11, 12, 22, 42, 50, 58]);
    }

    private function permisosConsulta()
    {
        return [
            1, 3, 7, 14, 19,
            34, 38, 43, 46, 47,
            48, 49, 51, 52, 53, 
            54, 55, 56, 57, 58, 
            59, 60, 61, 62, 63,
            64, 65
        ];
    }
}
