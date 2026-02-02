<?php

namespace App\Http\Responsable\categorias;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class CategoriaDestroy implements Responsable
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

        $idCategoria = $this->idCategoria;

        $categoria = Categoria::where('id_categoria', $idCategoria)->first();

        if (!$categoria) {
            return response()->json(['error_bd' => 'Categoría no encontrada'], 404);
        }

        try {
            // --- NUEVA VALIDACIÓN ---
            // Si intentamos pasar de Activo (1) a Inactivo (2)
            if ($categoria->id_estado == 1) {
                
                // Verificamos si existen productos vinculados.
                // Asumiendo que el modelo Categoria tiene la relación 'productos'
                // o consultando directamente la tabla:
                $tieneProductos = DB::connection('tenant') // Asegura que use la conexión del tenant
                                    ->table('productos')
                                    ->where('id_categoria', $idCategoria)
                                    ->exists();

                if ($tieneProductos) {
                    if ($empresaActual) {
                        DatabaseConnectionHelper::restaurarConexionPrincipal();
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede inactivar: La categoría tiene productos asociados.'
                    ], 400);
                }

                $categoria->id_estado = 2;

            } else {
                // Si está inactiva, la activamos sin restricciones
                $categoria->id_estado = 1;
            }

            $categoria->save();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['success' => true]);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
