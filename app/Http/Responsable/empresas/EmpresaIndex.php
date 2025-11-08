<?php

namespace App\Http\Responsable\empresas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Empresa;

class EmpresaIndex implements Responsable
{
    public function toResponse($request)
    {
        try {
            $empresas = Empresa::leftjoin('estados','estados.id_estado','=','empresas.id_estado')
                ->leftjoin('tipos_bd','tipos_bd.id_tipo_bd','=','empresas.id_tipo_bd')
                ->select(
                    'id_empresa',
                    'nit_empresa',
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
                ->orderBy('nombre_empresa', 'asc')
                ->get();

                return response()->json($empresas);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
