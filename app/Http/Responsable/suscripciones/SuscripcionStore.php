<?php

namespace App\Http\Responsable\unidades_medida;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\UnidadMedida;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class UnidadMedidaStore implements Responsable
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
        
        $descripcion = request('descripcion', null);
        $abreviatura = request('abreviatura', null);
        $estado_id = request('estado_id', null);

        // ================================================

        try {
            $nuevaUmd = UnidadMedida::create([
                'descripcion' => $descripcion,
                'abreviatura' => $abreviatura,
                'estado_id' => $estado_id,
            ]);
    
            if (isset($nuevaUmd) && !is_null($nuevaUmd) && !empty($nuevaUmd)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json(['success' => true]);
            }
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
