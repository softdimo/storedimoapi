<?php

namespace App\Http\Responsable\traits;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Roles;
use App\Models\Estado;
use App\Models\TipoDocumento;
use App\Models\TipoPersona;
use App\Models\Genero;
use App\Models\TipoBaja;
use App\Models\TipoPago;
use App\Models\PeriodoPago;
use App\Models\PorcentajeComision;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\TipoBd;
use App\Models\Plan;
use App\Models\Suscripcion;

class TraitAppWeb implements Responsable
{
    public function toResponse($request)
    {
        //
    }

    // ========================================================
    // ========================================================

    public function getConfigInicial()
    {
        try {
            return response()->json([

                'roles' => Roles::orderBy('name')->get(['name', 'id']),
                'estados' => Estado::whereIn('id_estado', [1,2])->orderBy('estado')->get(['estado', 'id_estado']),
                'estados_suscripciones' => Estado::whereIn('id_estado', [1,2,10,11,12])->orderBy('estado')->get(['estado', 'id_estado']),
                'tipos_documento' => TipoDocumento::orderBy('tipo_documento')->get(['tipo_documento', 'id_tipo_documento']),
                'tipos_documento_usuario' => TipoDocumento::whereNotIn('id_tipo_documento', [3])->orderBy('tipo_documento')->get(['tipo_documento', 'id_tipo_documento']),
                'tipos_persona' => TipoPersona::whereNotIn('id_tipo_persona', [1,2])->orderBy('tipo_persona')->get(['tipo_persona', 'id_tipo_persona']),
                'tipos_empleado' => TipoPersona::whereIn('id_tipo_persona', [1,2])->orderBy('tipo_persona')->get(['tipo_persona', 'id_tipo_persona']),
                'tipos_proveedor' => TipoPersona::whereIn('id_tipo_persona', [3,4])->orderBy('tipo_persona')->get(['tipo_persona', 'id_tipo_persona']),
                'generos' => Genero::orderBy('genero')->get(['genero', 'id_genero']),
                'tipos_baja' => TipoBaja::orderBy('tipo_baja','asc')->get(['tipo_baja', 'id_tipo_baja']),
                'tipos_pago_ventas' => TipoPago::whereNotIn('id_tipo_pago', [4,5])->where('id_estado',1)->orderBy('tipo_pago')->get(['tipo_pago', 'id_tipo_pago']),
                'tipos_pago_nomina' => TipoPago::whereIn('id_tipo_pago', [4,5])->orderBy('tipo_pago')->get(['tipo_pago', 'id_tipo_pago']),
                'tipos_pago_suscripcion' => TipoPago::whereIn('id_tipo_pago', [6,7,8,9])->orderBy('tipo_pago')->get(['tipo_pago', 'id_tipo_pago']),
                'periodos_pago' => PeriodoPago::orderBy('periodo_pago')->get(['periodo_pago', 'id_periodo_pago']),
                'porcentajes_comision' => PorcentajeComision::orderBy('porcentaje_comision')->get(['porcentaje_comision', 'id_porcentaje_comision']),
                'empresas' => Empresa::orderBy('nombre_empresa')->where('id_estado', 1)->get(['nombre_empresa', 'id_empresa']),
                'tipos_bd' => TipoBd::orderBy('tipo_bd')->get(['tipo_bd', 'id_tipo_bd']),
                'usuarios' => Usuario::orderBy('id_usuario')
                                            ->select(
                                                DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario, ' => ', usuario) AS user"),
                                                'id_usuario'
                                            )
                                            ->where('id_estado', 1)
                                            ->get(['user', 'id_usuario']),

                'tipos_cliente' => TipoPersona::whereIn('id_tipo_persona', [5,6])->orderBy('tipo_persona')->get(['tipo_persona', 'id_tipo_persona']),

                // Para el get del select normal
                'planes' => Plan::orderBy('nombre_plan')->where('id_estado_plan', 1)->get(['nombre_plan', 'id_plan']),

                // Para obtener TODOS los campos del plan en un arreglo indexado por id_plan
                'planesData' => Plan::orderBy('nombre_plan')->get()->keyBy('id_plan'),

            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error cargando configuración Traits: ' . $e->getMessage()], 500);
        }
    }

    // ========================================================
    // ========================================================

    public function getEmpresasDisponiblesSuscripcion($idEmpresaActual = null)
    {
        try {
            // 1. IDs de empresas que YA tienen una suscripción
            $empresasConSuscripcion = Suscripcion::whereNotNull('id_empresa_suscrita')
                ->pluck('id_empresa_suscrita')
                ->toArray();

            $idsFijosAExcluir = [5];
            $idsAExcluir = array_merge($empresasConSuscripcion, $idsFijosAExcluir);

            // 4. Quitar de la exclusión si es edición
            if ($idEmpresaActual && $idEmpresaActual != 'null') {
                $idsAExcluir = array_diff($idsAExcluir, [$idEmpresaActual]);
            }

            // --- Consulta de Empresas Disponibles ---
            $empresasDisponibles = Empresa::orderBy('nombre_empresa')
                ->where('id_estado', 1)
                ->whereNotIn('id_empresa', $idsAExcluir)
                ->get(['nombre_empresa', 'id_empresa']);

            // 5. REINCORPORADO: Si estamos en EDICIÓN, forzamos la inclusión de la empresa actual
            if ($idEmpresaActual && $idEmpresaActual != 'null') {
                $empresaActual = Empresa::where('id_empresa', $idEmpresaActual)
                    ->get(['nombre_empresa', 'id_empresa']);
                
                // Unimos las colecciones
                $empresasDisponibles = $empresasDisponibles->merge($empresaActual);
            }

            return response()->json($empresasDisponibles);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
