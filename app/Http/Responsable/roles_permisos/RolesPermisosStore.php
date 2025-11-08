<?php

namespace App\Http\Responsable\roles_permisos;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Roles;
use App\Models\Permission;
use App\Models\ModelHasPermissions;

class RolesPermisosStore implements Responsable
{
    public function toResponse($request)
    {
        try
        {
            $nameRol = ucwords($request->input('name'));

            if (!$this->existeNombreRol($nameRol))
            {
                Roles::create([
                    'name' => $nameRol,
                    'guard_name' => 'API'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Rol creado correctamente'
                ]);
            }

            return response()->json([
                'error' => true,
                'message' => 'El nombre de rol ya existe en la base de datos'
            ]);
        } catch (Exception $e)
        {
            return response()->json([
                'error' => true,
                'message' => 'Ha ocurrido un error de base de datos creando el rol'
            ]);
        }
    }

    public function crearPermiso($request)
    {
        try
        {
            $namePermission = ucwords($request->input('permission'));

            if (!$this->existeNombrePermiso($namePermission))
            {
                Permission::create([
                    'name' => $namePermission,
                    'guard_name' => 'API'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Permiso creado correctamente'
                ]);
            }

            return response()->json([
                'error' => true,
                'message' => 'El nombre de permiso ya existe en la base de datos'
            ]);
        } catch (Exception $e)
        {
            return response()->json([
                'error' => true,
                'message' => 'Ha ocurrido un error de base de datos creando el permiso'
            ]);
        }
    }

    public function crearPermisosPorUsuario($request)
    {
        try
        {
            $usuarioId = $request->usuario_id;
            $nuevosPermisos = $request->permissions ?? [];

            // Eliminar permisos actuales
            $permisosActuales = $this->consultarPermisosPorUsuario($usuarioId);
            if ($permisosActuales->isNotEmpty())
            {
                $permissionIds = $permisosActuales->pluck('permission_id');
                ModelHasPermissions::where('model_id', $usuarioId)
                                   ->whereIn('permission_id', $permissionIds)
                                   ->delete();
            }

            // Asignar nuevos permisos
            foreach ($nuevosPermisos as $permissionId)
            {
                ModelHasPermissions::updateOrCreate([
                    'permission_id' => $permissionId,
                    'model_type' => 'App\Models\Usuario',
                    'model_id' => $usuarioId
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Permisos actualizados correctamente'
            ]);
            
        } catch (Exception $e)
        {
            return response()->json([
                'error' => true,
                'message' => 'Ha ocurrido un error de base de datos actualizando los permisos'
            ]);
        }
    }

    public function consultarPermisosPorUsuario($usuarioId)
    {
        return ModelHasPermissions::select('permission_id')
                                  ->where('model_id', $usuarioId)
                                  ->get();
    }

    protected function existeNombreRol($name)
    {
        return Roles::where('name', $name)->exists();
    }

    protected function existeNombrePermiso($name)
    {
        return Permission::where('name', $name)->exists();
    }
}