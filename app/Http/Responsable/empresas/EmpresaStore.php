<?php

namespace App\Http\Responsable\empresas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Empresa;

class EmpresaStore implements Responsable
{
    public function toResponse($request)
    {
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
            ]);

            if ($nuevaEmpresa) {
                return response()->json([
                    'success' => true,
                    'empresa' => $nuevaEmpresa
                ]);
            }

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
