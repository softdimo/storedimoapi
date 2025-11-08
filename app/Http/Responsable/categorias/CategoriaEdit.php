<?php

namespace App\Http\Responsable\categorias;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class CategoriaEdit implements Responsable
{
    protected $idCategoria;

    // =========================================

    public function __construct($idCategoria)
    {
        $this->idCategoria = $idCategoria;
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
            $categoria = Categoria::leftJoin('estados', 'estados.id_estado', '=', 'categorias.id_estado')
                ->select(
                    'id_categoria',
                    'categoria',
                    'categorias.id_estado',
                    'estados.estado'
                )
                ->orderByDesc('categoria')
                ->where('id_categoria', $this->idCategoria)
                ->first();

            if (isset($categoria) && !is_null($categoria) && !empty($categoria)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($categoria);
            }

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
}
