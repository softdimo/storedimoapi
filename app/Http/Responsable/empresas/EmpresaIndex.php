<?php

namespace App\Http\Responsable\empresas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Empresa;
use App\Models\Usuario;
class EmpresaIndex implements Responsable
{
    public function toResponse($request)
    {
        $idRol = $request->input('id_rol');
        $idUsuario = $request->input('id_usuario');

        try
        {
            // Obtener empresa del usuario
            $idEmpresa = Usuario::where('id_usuario', $idUsuario)
                ->value('id_empresa');

            // Query base
            $query = Empresa::leftJoin('estados', 'estados.id_estado', '=', 'empresas.id_estado')
                ->leftJoin('tipos_bd', 'tipos_bd.id_tipo_bd', '=', 'empresas.id_tipo_bd')
                ->leftJoin('tipo_documento', 'tipo_documento.id_tipo_documento', '=', 'empresas.id_tipo_documento')
                ->select(
                    'id_empresa',
                    'empresas.id_tipo_documento',
                    'tipo_documento',
                    'nit_empresa',
                    'ident_empresa_natural',
                    'nombre_empresa',
                    'telefono_empresa',
                    'celular_empresa',
                    'email_empresa',
                    'direccion_empresa',
                    'app_key',
                    'app_url',
                    'db_host',
                    'db_database',
                    'db_username',
                    'db_password',
                    'estados.id_estado',
                    'estado',
                    'tipos_bd.id_tipo_bd',
                    'tipo_bd',
                    'logo_empresa'
                )
                ->orderBy('nombre_empresa', 'asc');

            // Filtro según rol
            if ($idRol != 3) {
                $query->where('id_empresa', $idEmpresa);
            }

            return response()->json($query->get());

        } catch (Exception $e)
        {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}