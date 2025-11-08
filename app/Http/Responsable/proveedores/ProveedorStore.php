<?php

namespace App\Http\Responsable\proveedores;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Proveedor;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class ProveedorStore implements Responsable
{
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
        
        $idTipoPersona = request('id_tipo_persona', null);
        $idTipoDocumento = request('id_tipo_documento', null);
        $identificacion = request('identificacion', null);
        $nombreProveedor = request('nombres_proveedor', null);
        $apellidoProveedor = request('apellidos_proveedor', null);
        $telefonoProveedor = request('telefono_proveedor', null);
        $celularProveedor = request('celular_proveedor', null);
        $emailProveedor = request('email_proveedor', null);
        $idGenero = request('id_genero', null);
        $direccionProveedor = request('direccion_proveedor', null);
        $idEstado = request('id_estado', null);
        $nitProveedor = request('nit_proveedor', null);
        $proveedorJuridico = request('proveedor_juridico', null);
        $telefonoJuridico = request('telefono_juridico', null);

        // ================================================
        try {
            Proveedor::create([
                'id_tipo_persona' => $idTipoPersona,
                'id_tipo_documento' => $idTipoDocumento,
                'identificacion' => $identificacion,
                'nombres_proveedor' => $nombreProveedor,
                'apellidos_proveedor' => $apellidoProveedor,
                'telefono_proveedor' => $telefonoProveedor,
                'celular_proveedor' => $celularProveedor,
                'email_proveedor' => $emailProveedor,
                'id_genero' => $idGenero,
                'direccion_proveedor' => $direccionProveedor,
                'id_estado' => $idEstado,
                'nit_proveedor' => $nitProveedor,
                'proveedor_juridico' => $proveedorJuridico,
                'telefono_juridico' => $telefonoJuridico
            ]);
    
            // ================================================
    
            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json(true);
            
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
