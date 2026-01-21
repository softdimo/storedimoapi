<?php

namespace App\Http\Responsable\empresas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Empresa;

class EmpresaStore implements Responsable
{
    public function toResponse($request)
    {
        // $idTipoDocumento = request('id_tipo_documento', null);
        // $nitEmpresa = request('nit_empresa', null);
        // $identEmpresaNatural = request('ident_empresa_natural', null);
        // $nombreEmpresa = request('nombre_empresa', null);
        // $telefonoEmpresa = request('telefono_empresa', null);
        // $celularEmpresa = request('celular_empresa');
        // $emailEmpresa = request('email_empresa');
        // $direccionEmpresa = request('direccion_empresa');
        // $idEstado = request('id_estado');
        // $appKey = request('app_key');
        // $appUrl = request('app_url');
        // $idTipoBd = request('id_tipo_bd');
        // $dbHost = request('db_host');
        // $dbDatabase = request('db_database');
        // $dbUsername = request('db_username');
        // $dbPassword = request('db_password');
        // $logoEmpresa = request('logo_empresa');

        try {
            $nuevaEmpresa = Empresa::create([
                'id_tipo_documento'     => $request->input('id_tipo_documento'),
                'nit_empresa'           => $request->input('nit_empresa'),
                'ident_empresa_natural' => $request->input('ident_empresa_natural'),
                'nombre_empresa'        => $request->input('nombre_empresa'),
                'telefono_empresa'      => $request->input('telefono_empresa'),
                'celular_empresa'       => $request->input('celular_empresa'),
                'email_empresa'         => $request->input('email_empresa'),
                'direccion_empresa'     => $request->input('direccion_empresa'),
                'id_estado'             => $request->input('id_estado'),
                'app_key'               => $request->input('app_key'),
                'app_url'               => $request->input('app_url'),
                'id_tipo_bd'            => $request->input('id_tipo_bd'),
                'db_host'               => $request->input('db_host'),
                'db_database'           => $request->input('db_database'),
                'db_username'           => $request->input('db_username'),
                'db_password'           => $request->input('db_password'),
                'logo_empresa'          => $request->input('logo_empresa')

                // 'id_tipo_documento' => $idTipoDocumento,
                // 'nit_empresa' => $nitEmpresa,
                // 'ident_empresa_natural' => $identEmpresaNatural,
                // 'nombre_empresa' => $nombreEmpresa,
                // 'telefono_empresa' => $telefonoEmpresa,
                // 'celular_empresa' => $celularEmpresa,
                // 'email_empresa' => $emailEmpresa,
                // 'direccion_empresa' => $direccionEmpresa,
                // 'id_estado' => $idEstado,
                // 'app_key' => $appKey,
                // 'app_url' => $appUrl,
                // 'id_tipo_bd' => $idTipoBd,
                // 'db_host' => $dbHost,
                // 'db_database' => $dbDatabase,
                // 'db_username' => $dbUsername,
                // 'db_password' => $dbPassword,
                // 'logo_empresa' => $logoEmpresa
            ]);

            if (isset($nuevaEmpresa) && !is_null($nuevaEmpresa) && !empty($nuevaEmpresa)) {
                return response()->json(['success' => true]);
            }

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
