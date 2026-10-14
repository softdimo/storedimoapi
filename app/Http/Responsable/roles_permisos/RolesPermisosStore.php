<?php

namespace App\Http\Responsable\roles_permisos;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Roles;
use App\Models\Permission;
use App\Models\ModelHasPermissions;
use Illuminate\Support\Facades\DB;

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
            $nuevosPermisos = array_values(array_unique(array_filter((array) ($request->permissions ?? []))));
            $modelType = 'App\Models\Usuario';

            $filas = [];
            foreach ($nuevosPermisos as $permissionId) {
                $filas[] = [
                    'permission_id' => $permissionId,
                    'model_type' => $modelType,
                    'model_id' => $usuarioId,
                ];
            }

            DB::connection('mysql')->transaction(function () use ($usuarioId, $modelType, $filas) {
                ModelHasPermissions::where('model_id', $usuarioId)
                    ->where('model_type', $modelType)
                    ->delete();

                if (!empty($filas)) {
                    ModelHasPermissions::insert($filas);
                }
            });

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