<?php

namespace App\Http\Responsable\categorias;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;

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
            $categoria = Categoria::select(
                    'id_categoria',
                    'categoria',
                    'categorias.id_estado',
                    // 'estados.estado'
                )
                ->orderByDesc('categoria')
                ->where('id_categoria', $this->idCategoria)
                ->first();

            if (isset($categoria) && !is_null($categoria) && !empty($categoria)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                $estado = DB::connection('mysql')
                        ->table('estados')
                        ->where('id_estado', $categoria->id_estado)
                        ->select('estado')
                        ->first();
                
                $categoria->estado = $estado->estado ?? 'Sin Estado';

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
