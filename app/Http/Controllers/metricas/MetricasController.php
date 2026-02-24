<?php

namespace App\Http\Controllers\metricas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responsable\metricas\MetricaIndex;
use App\Models\Metrica;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class MetricasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new MetricaIndex();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idPersona)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idPersona)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    public function queryTotalAbsoluto(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            $totalAbsolutoPeticiones = Metrica::selectRaw('
                COUNT(*) as gran_total_peticiones,
                COUNT(DISTINCT tenant_db) as empresas_conectadas
            ')
            ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
            ->first(); // Usamos first() porque solo esperamos una fila de resultados

            return response()->json($totalAbsolutoPeticiones);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function querySubtotalActividad(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            $subtotalActividad = Metrica::selectRaw('
                    source,
                    COUNT(*) as total_peticiones,
                    COUNT(DISTINCT tenant_db) as tenants_activos
                ')
                ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
                ->groupBy('source')
                ->get(); // Usamos get() porque esperamos múltiples filas (una por cada source)

            return response()->json($subtotalActividad);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function queryMovimientoBd(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            $movimientoBd = Metrica::selectRaw('
                    tenant_db,
                    COUNT(*) as peticiones_hoy
                ')
                ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
                ->groupBy('tenant_db')
                ->orderBy('peticiones_hoy', 'DESC')
                ->get();

            return response()->json($movimientoBd);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function queryPorFuente(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            // 1. Primero obtenemos el gran total en ese rango para el cálculo del porcentaje
            $totalRango = Metrica::whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])->count();

            // 2. Ejecutamos la consulta agrupada
            // Evitamos división por cero si el rango está vacío
            $divisor = $totalRango > 0 ? $totalRango : 1;

            $traficoPorFuente = Metrica::selectRaw("
                    source,
                    COUNT(*) as total_peticiones,
                    ROUND(COUNT(*) * 100.0 / $divisor, 2) as porcentaje
                ")
                ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
                ->groupBy('source')
                ->get();

            return response()->json($traficoPorFuente);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function queryRankingTenants(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            $rankingTenants = Metrica::selectRaw('
                    tenant_db,
                    source,
                    COUNT(*) as peticiones
                ')
                ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
                ->groupBy('tenant_db', 'source')
                ->orderBy('tenant_db', 'DESC')
                ->get();

            return response()->json($rankingTenants);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function queryMonitoreoErrores(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            $monitoreoErrores = Metrica::selectRaw('
                    source,
                    path,
                    status_code,
                    COUNT(*) as ocurrencias
                ')
                ->where('status_code', '>=', 400)
                ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
                ->groupBy('source', 'path', 'status_code')
                ->orderBy('ocurrencias', 'DESC')
                ->get();

            return response()->json($monitoreoErrores);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function queryRutasUtilizadas(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            $rutasUtilizadas = Metrica::selectRaw('
                    path,
                    method,
                    COUNT(*) as visitas
                ')
                ->where('source', 'LUMEN_API')
                ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
                ->groupBy('path', 'method')
                ->orderBy('visitas', 'DESC')
                ->limit(10)
                ->get();

            return response()->json($rutasUtilizadas);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function queryActividadHoras(Request $request)
    {
        $fechaInicialMetrica = $request->input('fecha_inicial_metrica');
        $fechaFinalMetrica = $request->input('fecha_final_metrica');

        try {
            $actividadHoras = Metrica::selectRaw('
                    HOUR(created_at) as hora,
                    COUNT(*) as total_peticiones
                ')
                ->whereBetween('created_at', [$fechaInicialMetrica, $fechaFinalMetrica])
                ->groupBy('hora')
                ->orderBy('hora', 'ASC')
                ->get();

            return response()->json($actividadHoras);

        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }
    
    // ======================================================================
    // ======================================================================

    public function borrarRegistros(Request $request)
    {
        try {
            // Calculamos la fecha límite (hace 30 días)
            // Carbon es la librería que Laravel usa por defecto para fechas
            $fechaLimite = Carbon::now()->subDays(30);
    
            // Ejecutamos el borrado de registros cuya fecha sea menor a la límite
            $cantidadBorrados = Metrica::where('created_at', '<', $fechaLimite)->delete();
    
            return response()->json([
                'mensaje' => 'Mantenimiento realizado con éxito',
                'registros_eliminados' => $cantidadBorrados,
                'fecha_limite_usada' => $fechaLimite->toDateTimeString()
            ], 200);
    
        } catch (Exception $e) {
            return response()->json(['error_bd' => $e->getMessage()], 500);
        }
    }

}
