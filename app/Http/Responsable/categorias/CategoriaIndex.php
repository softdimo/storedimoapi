<?php

namespace App\Http\Responsable\categorias;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;

class CategoriaIndex implements Responsable
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
        
        try {
            $categorias = Categoria::select(
                    'id_categoria',
                    'categoria',
                    'categorias.id_estado',
                    // 'estados.estado'
                )
                ->orderBy('categoria', 'ASC')
                ->get();

            if (isset($categorias) && !is_null($categorias) && !empty($categorias)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                foreach ($categorias as $categoria) {
                    $estado = DB::connection('mysql')
                        ->table('estados')
                        ->where('id_estado', $categoria->id_estado)
                        ->select('estado')
                        ->first();
    
                    $categoria->estado = $estado->estado ?? 'Sin Estado';
                }

                return response()->json($categorias);
            } else {
                return response()->json([
                    'message' => 'no hay categorias'
                ], 404);
            }
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'message' => 'Error en la consulta de la base de datos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
