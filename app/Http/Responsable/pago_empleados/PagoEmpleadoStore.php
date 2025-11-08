<?php

namespace App\Http\Responsable\pago_empleados;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\PagoEmpleado;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class PagoEmpleadoStore implements Responsable
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
        
        $idUsuario = request('id_usuario', null);
        $identificacion = request('identificacion', null);
        $valorBase = request('valor_base', null);
        $fechaInicioLabores = request('fecha_inicio_labores', null);
        $fechaFinalLabores = request('fecha_final_labores', null);
        $idTipoPago = request('id_tipo_pago', null);
        $idPeriodoPago = request('id_periodo_pago', null);
        $cantidadDias = request('cantidad_dias', null);
        $totalDiasPagar = request('total_dias_pagar', null);
        $idPorcentajeComision = request('id_porcentaje_comision', null);
        $valorDia = request('valor_dia', null);
        $fechaUltimoPago = request('fecha_ultimo_pago', null);
        $valorVentas = request('valor_ventas', null);
        $pendientePrestamos = request('pendiente_prestamos', null);
        $salarioNeto = request('salario_neto', null);
        $vacaciones = request('vacaciones', null);
        $comisiones = request('comisiones', null);
        $cesantias = request('cesantias', null);
        $total = request('total', null);

        try {
            $registroPagoEmpleado = PagoEmpleado::create([
                'id_usuario' => $idUsuario,
                'identificacion' => $identificacion,
                'valor_base' => $valorBase,
                'fecha_inicio_labores' => $fechaInicioLabores,
                'fecha_final_labores' => $fechaFinalLabores,
                'id_tipo_pago' => $idTipoPago,
                'id_periodo_pago' => $idPeriodoPago,
                'cantidad_dias' => $cantidadDias,
                'total_dias_pagar' => $totalDiasPagar,
                'id_porcentaje_comision' => $idPorcentajeComision,
                'valor_dia' => $valorDia,
                'fecha_ultimo_pago' => $fechaUltimoPago,
                'valor_ventas' => $valorVentas,
                'pendiente_prestamos' => $pendientePrestamos,
                'salario_neto' => $salarioNeto,
                'vacaciones' => $vacaciones,
                'comisiones' => $comisiones,
                'cesantias' => $cesantias,
                'total' => $total
            ]);

            if (isset($registroPagoEmpleado) && !is_null($registroPagoEmpleado) && !empty($registroPagoEmpleado)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json(['success' => true]);
            }

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
