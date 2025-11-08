<?php

namespace App\Http\Controllers\personas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responsable\personas\PersonaIndex;
use App\Http\Responsable\personas\PersonaStore;
use App\Http\Responsable\personas\PersonaUpdate;
use App\Http\Responsable\personas\PersonaEdit;
use App\Models\Persona;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Empresa;


class PersonasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new PersonaIndex();
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
        return new PersonaStore($request);
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
        return new PersonaEdit($idPersona);
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
        return new PersonaUpdate($request, $idPersona);
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

    public function consultarIdPersona(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $identificacion = request('identificacion', null);
        
        // Consultamos si ya existe un usuario con la cedula ingresada
        $persona = Persona::where('identificacion', $identificacion)->first();

        // Restaurar conexión principal si se usó tenant
        if ($empresaActual) {
            DatabaseConnectionHelper::restaurarConexionPrincipal();
        }

        if ($persona) {
            return response()->json($persona);
        } else {
            return response(null, 200);
        }
    }

    public function consultarNitEmpresa(Request $request)
    {
        // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
        $empresaId = $request->input('empresa_actual');

        // 2. Buscar empresa completa usando el ID
        $empresaActual = Empresa::find($empresaId);
        
        // Configurar conexión tenant si hay empresa
        if ($empresaActual) {
            DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
        }

        $nitEmpresa = request('nit_empresa', null);
        
        // Consultamos si ya existe un usuario con la cedula ingresada
        $persona = Persona::where('nit_empresa', $nitEmpresa)->first();

        // Restaurar conexión principal si se usó tenant
        if ($empresaActual) {
            DatabaseConnectionHelper::restaurarConexionPrincipal();
        }

        if ($persona) {
            return response()->json($persona);
        } else {
            return response(null, 200);
        }
    }

    public function personaTrait(Request $request)
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
            $clientes = Persona::leftJoin('tipo_persona', 'tipo_persona.id_tipo_persona', '=', 'personas.id_tipo_persona')
                ->select(
                    'personas.id_persona', // Ahora usamos id_persona como clave
                    'personas.identificacion',
                    'personas.id_tipo_persona',
                    DB::raw("CONCAT(nombres_persona, ' ', apellidos_persona, ' (', tipo_persona, ')',' - ', identificacion) AS nombres_cliente")
                )
                ->whereIn('personas.id_tipo_persona', [5,6])
                ->orderBy('nombres_cliente')
                ->get() // Usamos get() en lugar de pluck()
                ->mapWithKeys(function($cliente) {
                    return [$cliente->id_persona => [
                        'nombre' => $cliente->nombres_cliente, // Lo que se mostrará en el select
                        'tipo' => $cliente->id_tipo_persona // id_tipo_persona Necesario para activar el checkbox
                    ]];
                });

            // Retornamos la categoría si existe, de lo contrario retornamos null
            if (isset($clientes) && !is_null($clientes) && !empty($clientes)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json($clientes);
                
            } else {
                return response(null, 200);
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
