<?php

namespace App\Http\Responsable\unidades_medida;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Empresa;
use App\Models\UnidadMedida;
use App\Helpers\DatabaseConnectionHelper;


class UnidadMedidaUpdate implements Responsable
{
    protected $request;
    protected $idUmd;

    public function __construct(Request $request, $idUmd)
    {
        $this->request = $request;
        $this->idUmd = $idUmd;
    }

    // ===================================================================
    // ===================================================================

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
            $umd = UnidadMedida::find($this->idUmd);

            $umd->descripcion = $this->request->input('descripcion');
            $umd->abreviatura = $this->request->input('abreviatura');
            $umd->update();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json([
                'success' => true,
                'message' => 'Unidad medida actualizada correctamente'
            ]);
                
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'message' => 'Error actualizando la Umd en BD',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
