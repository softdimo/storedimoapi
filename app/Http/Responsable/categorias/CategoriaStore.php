<?php

namespace App\Http\Responsable\categorias;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class CategoriaStore implements Responsable
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
        
        $categoria = request('categoria', null);
        $idEstado = request('id_estado', null);

        // ================================================

        $nuevaCategoria = Categoria::create([
            'categoria' => $categoria,
            'id_estado' => $idEstado,
        ]);

        // ================================================

        if (isset($nuevaCategoria) && !is_null($nuevaCategoria) && !empty($nuevaCategoria)) {
            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Categoría creada correctamente'
            ]);
        } else {
            return abort(404, $message = 'Categoría no creada');
        }
    }

    // ===================================================================
    // ===================================================================


}
