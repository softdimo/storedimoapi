<?php

namespace App\Http\Responsable\proveedores;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\Proveedor;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class ProveedorUpdate implements Responsable
{
    protected $request;
    protected $idProveedor;

    public function __construct(Request $request, $idProveedor)
    {
        $this->request = $request;
        $this->idProveedor = $idProveedor;
    }

    public function toResponse($request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }
        
        $proveedor = Proveedor::findOrFail($this->idProveedor);

        try {
            if (isset($proveedor) && !is_null($proveedor) && !empty($proveedor)) {
            
                $proveedor->id_tipo_persona = $request->input('id_tipo_persona');
                $proveedor->id_tipo_documento = $request->input('id_tipo_documento');
                $proveedor->identificacion = $request->input('identificacion');
                $proveedor->nombres_proveedor = $request->input('nombres_proveedor');
                $proveedor->apellidos_proveedor = $request->input('apellidos_proveedor');
                $proveedor->telefono_proveedor = $request->input('telefono_proveedor');
                $proveedor->celular_proveedor = $request->input('celular_proveedor');
                $proveedor->email_proveedor = $request->input('email_proveedor');
                $proveedor->id_genero = $request->input('id_genero');
                $proveedor->direccion_proveedor = $request->input('direccion_proveedor');
                $proveedor->id_estado = $request->input('id_estado');
                $proveedor->nit_proveedor = $request->input('nit_proveedor');
                $proveedor->proveedor_juridico = $request->input('proveedor_juridico');
                $proveedor->telefono_juridico = $request->input('telefono_juridico');
                $proveedor->save();
    
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json(true);
            }
            
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
        
    }

    // ===================================================================
    // ===================================================================


}
