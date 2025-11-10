<?php

namespace App\Http\Responsable\existencias;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Baja;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;

class BajaIndex implements Responsable
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
            $bajas = Baja::select(
                    'id_baja',
                    'id_responsable_baja',
                    // DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario, ' - ', identificacion) AS nombres_usuario"),
                    'fecha_baja',
                    'id_estado_baja',
                    // 'estado'
                )
                ->orderByDesc('fecha_baja')
                ->get();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                // 3. Agregar nombre completo del usuario y nombre estado desde la base principal
                // Traer los catálogos completos en memoria
                $usuarios = DB::connection('mysql')
                    ->table('usuarios')
                    ->select('id_usuario', DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario"))
                    ->get()
                    ->keyBy('id_usuario');

                $estados = DB::connection('mysql')
                    ->table('estados')
                    ->select('id_estado', 'estado')
                    ->get()
                    ->keyBy('id_estado');

                // Iterar las bajas sin hacer más consultas
                foreach ($bajas as $baja) {
                    $baja->nombres_usuario = $usuarios[$baja->id_responsable_baja]->nombres_usuario ?? 'Sin usuario';
                    $baja->estado = $estados[$baja->id_estado_baja]->estado ?? 'Sin estado';
                }

                // foreach ($bajas as $baja) {
                //     $usuario = DB::connection('mysql') // o la conexión principal que uses
                //         ->table('usuarios')
                //         ->where('id_usuario', $baja->id_responsable_baja)
                //         ->select(DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario"))
                //         ->first();

                //     $baja->nombres_usuario = $usuario->nombres_usuario ?? 'Sin usuario';

                //     $estado = DB::connection('mysql') // o la conexión principal que uses
                //         ->table('estados')
                //         ->where('id_estado', $baja->id_estado_baja)
                //         ->select('estado')
                //         ->first();

                //     $baja->estado = $estado->estado ?? 'Sin estado';
                // }

                return response()->json($bajas);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
