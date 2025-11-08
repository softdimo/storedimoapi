<?php

namespace App\Http\Responsable\unidades_medida;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\UnidadMedida;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class UnidadMedidaEdit implements Responsable
{
    protected $idUmd;

    // =========================================

    public function __construct($idUmd)
    {
        $this->idUmd = $idUmd;
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
        
        $idUmd = $this->idUmd;

        try {
            $unidadMedida = UnidadMedida::leftJoin('estados', 'estados.id_estado', '=', 'unidades_medida.estado_id')
                ->select(
                    'id',
                    'descripcion',
                    'abreviatura',
                    'estado_id',
                    'estado'
                )
                ->where('id', $idUmd)
                ->first();

            if (isset($unidadMedida) && !is_null($unidadMedida) && !empty($unidadMedida)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($unidadMedida);
                
            } else {
                return response()->json([
                    'message' => 'No existe producto'
                ], 404);
            }
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'message' => 'Error consultando la Umd en BD',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}