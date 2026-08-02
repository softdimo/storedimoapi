<?php

namespace App\Http\Responsable\suscripciones;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Suscripcion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class SuscripcionIndex implements Responsable
{
    public function toResponse($request)
    {
        $idRol = $request->input('id_rol');
        $idUsuario = $request->input('id_usuario');

        try
        {
            // Obtener empresa del usuario
            $idEmpresa = Usuario::where('id_usuario', $idUsuario)
                ->value('id_empresa');

            $query = Suscripcion::leftjoin('empresas', 'empresas.id_empresa', '=', 'suscripciones.id_empresa_suscrita')
                ->leftjoin('planes', 'planes.id_plan', '=', 'suscripciones.id_plan_suscrito')
                ->leftjoin('tipos_pago', 'tipos_pago.id_tipo_pago', '=', 'suscripciones.id_tipo_pago_suscripcion')
                ->leftjoin('estados', 'estados.id_estado', '=', 'suscripciones.id_estado_suscripcion')
                ->select(
                    'id_suscripcion',
                    'id_empresa_suscrita',
                    'nombre_empresa',
                    'id_plan_suscrito',
                    'nombre_plan',
                    'dias_trial',
                    'id_tipo_pago_suscripcion',
                    'tipo_pago as modalidad_suscripcion',
                    DB::raw('FORMAT(valor_suscripcion, 0) as valor_suscripcion'),
                    'fecha_inicial',
                    'fecha_final',
                    'id_estado_suscripcion',
                    'estado',
                    'fecha_cancelacion',
                    'renovacion_automatica',
                    'observaciones_suscripcion'
                )
                ->orderBy('nombre_empresa', 'asc');

            // Filtro según rol
            if ($idRol != 3)
            {
                $query->where('id_empresa_suscrita', $idEmpresa);
            }

            return response()->json($query->get());
            
        } catch (Exception $e)
        {
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
