<?php

namespace App\Http\Responsable\prestamos;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Prestamo;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class PrestamoIndex implements Responsable
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
            $prestamos = Prestamo::leftjoin('estados','estados.id_estado','=','prestamos.id_estado_prestamo')
                ->leftjoin('usuarios','usuarios.id_usuario','=','prestamos.id_usuario')
                ->leftjoin('tipo_documento','tipo_documento.id_tipo_documento','=','usuarios.id_tipo_documento')
                ->leftjoin('tipo_persona','tipo_persona.id_tipo_persona','=','usuarios.id_tipo_persona')
                ->select(
                    'id_prestamo',
                    'prestamos.id_estado_prestamo',
                    'estado',
                    'prestamos.id_usuario',
                    DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) AS nombres_usuario"),
                    'valor_prestamo',
                    'nombre_usuario',
                    'apellido_usuario',
                    'fecha_prestamo',
                    'fecha_limite',
                    'descripcion',
                    'usuarios.id_tipo_documento',
                    'tipo_documento',
                    'usuarios.identificacion',
                    'usuarios.id_tipo_persona',
                    'tipo_persona'
                )
                ->orderByDesc('fecha_prestamo')
                ->get();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($prestamos);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
