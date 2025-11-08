<?php

namespace App\Http\Responsable\empresas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Empresa;

class EmpresaEdit implements Responsable
{
    protected $idEmpresa;

    // =========================================

    public function __construct($idEmpresa)
    {
        $this->idEmpresa = $idEmpresa;
    }

    // =========================================
    public function toResponse($request)
    {
        try {
            $empresa = Empresa::leftjoin('estados','estados.id_estado','=','empresas.id_estado')
                ->leftjoin('tipos_bd','tipos_bd.id_tipo_bd','=','empresas.id_tipo_bd')
                ->select(
                    'id_empresa',
                    'nit_empresa',
                    'nombre_empresa',
                    'telefono_empresa',
                    'celular_empresa',
                    'email_empresa',
                    'direccion_empresa',
                    'estados.id_estado',
                    'estado',
                    'tipos_bd.id_tipo_bd',
                    'tipo_bd',
                    'app_key',
                    'app_url',
                    'db_host',
                    'db_database',
                    'db_username',
                    'db_password',
                    'logo_empresa'
                )
                ->orderByDesc('nombre_empresa')
                ->where('id_empresa', $this->idEmpresa)
                ->first();

            if (isset($empresa) && !is_null($empresa) && !empty($empresa)) {
                return response()->json($empresa);
            }

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
