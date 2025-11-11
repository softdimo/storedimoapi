<?php

namespace App\Http\Responsable\proveedores;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Proveedor;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;

class ProveedorEdit implements Responsable
{
    protected $idProveedor;

    // =========================================

    public function __construct($idProveedor)
    {
        $this->idProveedor = $idProveedor;
    }

    // =========================================

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
        
        try {
            $proveedor = Proveedor::leftjoin('empresas', 'empresas.id_empresa', '=', 'proveedores.id_empresa')
                ->leftjoin('tipo_persona', 'tipo_persona.id_tipo_persona', '=', 'proveedores.id_tipo_persona')
                // ->leftjoin('tipo_documento', 'tipo_documento.id_tipo_documento', '=', 'proveedores.id_tipo_documento')
                // ->leftjoin('estados', 'estados.id_estado', '=', 'proveedores.id_estado')
                ->leftjoin('generos', 'generos.id_genero', '=', 'proveedores.id_genero')
                ->select(
                    'id_proveedor',
                    'empresas.id_empresa',
                    'empresas.nombre_empresa',
                    'proveedores.id_tipo_persona',
                    'tipo_persona',
                    'proveedores.id_tipo_documento',
                    // 'tipo_documento',
                    'identificacion',
                    'nombres_proveedor',
                    'apellidos_proveedor',
                    'telefono_proveedor',
                    'celular_proveedor',
                    'email_proveedor',
                    'proveedores.id_genero',
                    'genero',
                    'direccion_proveedor',
                    'proveedores.id_estado',
                    // 'estado',
                    'nit_proveedor',
                    'proveedor_juridico',
                    'telefono_juridico'
                )
                ->orderByRaw("
                    CASE
                        WHEN proveedor_juridico IS NOT NULL THEN proveedor_juridico
                        ELSE nombres_proveedor
                    END ASC
                ")
                ->where('id_proveedor', $this->idProveedor)
                ->first();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            // ===========================
            // Consultar tipo_documento y estados desde la base principal
            // ===========================
            if ($proveedor) {
                $tipoDocumento = DB::connection('mysql')
                    ->table('tipo_documento')
                    ->select('id_tipo_documento', 'tipo_documento')
                    ->get()
                    ->keyBy('id_tipo_documento');

                $estados = DB::connection('mysql')
                    ->table('estados')
                    ->select('id_estado', 'estado')
                    ->get()
                    ->keyBy('id_estado');

                // Asignar valores al proveedor
                $proveedor->tipo_documento = $tipoDocumento[$proveedor->id_tipo_documento]->tipo_documento ?? 'Sin Tipo Documento';
                $proveedor->estado = $estados[$proveedor->id_estado]->estado ?? 'Sin estado';
            }

            return response()->json($proveedor);
            
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
