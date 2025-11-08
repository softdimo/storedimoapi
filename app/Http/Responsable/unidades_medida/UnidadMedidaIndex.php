<?php

namespace App\Http\Responsable\unidades_medida;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\UnidadMedida;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class UnidadMedidaIndex implements Responsable
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
        
        try
        {
            $unidadesMedida = UnidadMedida::leftJoin('estados', 'estados.id_estado', '=', 'unidades_medida.estado_id')
                ->select(
                    'id',
                    'descripcion',
                    'abreviatura',
                    'estado_id',
                    'estado'
                )
                ->orderBy('descripcion', 'asc')
                ->get();

            if (isset($unidadesMedida) && !is_null($unidadesMedida) && !empty($unidadesMedida))
            {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual)
                {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($unidadesMedida);
            } else
            {
                return response()->json([
                    'message' => 'no hay productos'
                ], 404);
            }
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual))
            {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'message' => 'Error en la consulta de la base de datos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
