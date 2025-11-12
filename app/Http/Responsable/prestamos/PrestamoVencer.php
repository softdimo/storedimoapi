<?php

namespace App\Http\Responsable\prestamos;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Prestamo;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class PrestamoVencer implements Responsable
{
    public function toResponse($request)
    {
        // Obtener empresa_actual del request
        $empresaActual = $request->input('empresa_actual');

        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual);
        }
        
        try {
            $prestamosVencer = Prestamo::leftjoin('usuarios','usuarios.id_usuario','=','prestamos.id_usuario')
                // ->leftjoin('tipo_documento','tipo_documento.id_tipo_documento','=','usuarios.id_tipo_documento')
                ->leftjoin('tipo_persona','tipo_persona.id_tipo_persona','=','usuarios.id_tipo_persona')
                ->select(
                    'id_prestamo',
                    'prestamos.id_estado_prestamo',
                    // 'estado',
                    'prestamos.id_usuario',
                    DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) AS nombres_usuario"),
                    'valor_prestamo',
                    'nombre_usuario',
                    'apellido_usuario',
                    'fecha_prestamo',
                    'fecha_limite',
                    'descripcion',
                    'usuarios.id_tipo_documento',
                    // 'tipo_documento',
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

                return response()->json($prestamosVencer);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
