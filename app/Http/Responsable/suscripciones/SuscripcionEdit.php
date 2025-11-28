<?php

namespace App\Http\Responsable\unidades_medida;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\UnidadMedida;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;

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
            $unidadMedida = UnidadMedida::select(
                    'id',
                    'descripcion',
                    'abreviatura',
                    'estado_id',
                    // 'estado'
                )
                ->where('id', $idUmd)
                ->first();

            if (isset($unidadMedida) && !is_null($unidadMedida) && !empty($unidadMedida)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                $estados = DB::connection('mysql')
                    ->table('estados')
                    ->select('id_estado', 'estado')
                    ->get()
                    ->keyBy('id_estado');

                // 🔹 Asignar texto descriptivo al registro
                $unidadMedida->estado = $estados[$unidadMedida->estado_id]->estado ?? 'Sin estado';

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