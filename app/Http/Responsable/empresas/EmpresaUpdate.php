<?php

namespace App\Http\Responsable\empresas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\Empresa;

class EmpresaUpdate implements Responsable
{
    protected $request;
    protected $idEmpresa;

    public function __construct(Request $request, $idEmpresa)
    {
        $this->request = $request;
        $this->idEmpresa = $idEmpresa;
    }

    public function toResponse($request)
    {
        $idEmpresa = $this->idEmpresa;

        $empresa = Empresa::find($idEmpresa);

        try {
            $empresa->id_tipo_documento = $this->request->input('id_tipo_documento');
            $empresa->nit_empresa = $this->request->input('nit_empresa');
            $empresa->ident_empresa_natural = $this->request->input('ident_empresa_natural');
            $empresa->nombre_empresa = $this->request->input('nombre_empresa');
            $empresa->telefono_empresa = $this->request->input('telefono_empresa');
            $empresa->celular_empresa = $this->request->input('celular_empresa');
            $empresa->email_empresa = $this->request->input('email_empresa');
            $empresa->direccion_empresa = $this->request->input('direccion_empresa');
            $empresa->app_key = $this->request->input('app_key');
            $empresa->app_url = $this->request->input('app_url');
            $empresa->id_tipo_bd = $this->request->input('id_tipo_bd');
            $empresa->db_host = $this->request->input('db_host');
            $empresa->db_database = $this->request->input('db_database');
            $empresa->db_username = $this->request->input('db_username');
            $empresa->db_password = $this->request->input('db_password');
            $empresa->logo_empresa = $this->request->input('logo_empresa');
            $empresa->id_estado = $this->request->input('id_estado');
            $empresa->update();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
