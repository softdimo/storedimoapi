<?php

namespace App\Http\Responsable\roles_permisos;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\ModelHasPermissions;

class RolesPermisosShow implements Responsable
{
    public function toResponse($request)
    {
        try
        {
            $usuario = request('usuarioId', null);

            $consulta = ModelHasPermissions::select('permission_id')
                        ->where('model_id', isset($usuario) ? $usuario : $request->usuario_id)
                        ->get();

            return response()->json(["resultado" => $consulta]);
            
        } catch (Exception $e)
        {
            return response()->json("error_exception");
        }
    }
}
