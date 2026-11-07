<?php

namespace App\Traits;

use App\Models\Usuario;
use App\Models\ModelHasPermissions;
use App\Models\Permission;

trait AsignarPermisosPorRol
{
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
        $metodo      = 'permisos' . $tipoPermiso;
        $permisos    = method_exists($this, $metodo) ? $this->$metodo() : [];

        $usuario = Usuario::where('identificacion', $nuevoUsuario->identificacion)->first();

        ModelHasPermissions::where('model_id', $usuario->id_usuario)
                            ->where('model_type', Usuario::class)
                            ->delete();

        $permisos = Permission::whereIn('id', $permisos)->pluck('id')->all();

        if ($idRol != 8 && !empty($permisos)) {
            $filas = [];

            foreach ($permisos as $permissionId) {
                $filas[] = [
                    'permission_id' => $permissionId,
                    'model_type'    => Usuario::class,
                    'model_id'      => $usuario->id_usuario,
                ];
            }

            ModelHasPermissions::insert($filas);
        }
    }

    private function permisosSoftdimo()
    {
        return range(1, 86);
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
        return $this->permisosSoftdimo();
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
