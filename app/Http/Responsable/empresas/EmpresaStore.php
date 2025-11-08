<?php

namespace App\Http\Responsable\empresas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Empresa;

class EmpresaStore implements Responsable
{
    public function toResponse($request)
    {
        $nitEmpresa = request('nit_empresa', null);
        $nombreEmpresa = request('nombre_empresa', null);
        $telefonoEmpresa = request('telefono_empresa', null);
        $celularEmpresa = request('celular_empresa');
        $emailEmpresa = request('email_empresa');
        $direccionEmpresa = request('direccion_empresa');
        $idEstado = request('id_estado');
        $appKey = request('app_key');
        $appUrl = request('app_url');
        $idTipoBd = request('id_tipo_bd');
        $dbHost = request('db_host');
        $dbDatabase = request('db_database');
        $dbUsername = request('db_username');
        $dbPassword = request('db_password');
        $logoEmpresa = request('logo_empresa');

        try {
            $nuevaEmpresa = Empresa::create([
                'nit_empresa' => $nitEmpresa,
                'nombre_empresa' => $nombreEmpresa,
                'telefono_empresa' => $telefonoEmpresa,
                'celular_empresa' => $celularEmpresa,
                'email_empresa' => $emailEmpresa,
                'direccion_empresa' => $direccionEmpresa,
                'id_estado' => $idEstado,
                'app_key' => $appKey,
                'app_url' => $appUrl,
                'id_tipo_bd' => $idTipoBd,
                'db_host' => $dbHost,
                'db_database' => $dbDatabase,
                'db_username' => $dbUsername,
                'db_password' => $dbPassword,
                'logo_empresa' => $logoEmpresa
            ]);

            if (isset($nuevaEmpresa) && !is_null($nuevaEmpresa) && !empty($nuevaEmpresa)) {
                return response()->json(['success' => true]);
            }

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
