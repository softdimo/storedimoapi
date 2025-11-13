<?php

namespace App\Http\Responsable\personas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Persona;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;

class PersonaIndex implements Responsable
{
    public function toResponse($request)
    {
        try {
            // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
            $empresaId = $request->input('empresa_actual');

            // 2. Buscar empresa completa usando el ID
            $empresaActual = Empresa::find($empresaId);
            
            // Configurar conexión tenant si hay empresa
            if ($empresaActual) {
                DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
            }

            $personas = Persona::leftjoin('generos', 'generos.id_genero', '=', 'personas.id_genero')
                // ->leftjoin('estados', 'estados.id_estado', '=', 'personas.id_estado')
                // ->leftjoin('tipo_documento', 'tipo_documento.id_tipo_documento', '=', 'personas.id_tipo_documento')
                // ->leftjoin('tipo_persona', 'tipo_persona.id_tipo_persona', '=', 'personas.id_tipo_persona')
                ->select(
                    'id_persona',
                    'personas.id_tipo_persona',
                    // 'tipo_persona',
                    'personas.id_tipo_documento',
                    // 'tipo_documento',
                    'identificacion',
                    'nombres_persona',
                    'apellidos_persona',
                    'numero_telefono',
                    'celular',
                    'email',
                    'genero',
                    'personas.id_genero',
                    'direccion',
                    // 'estado',
                    'personas.id_estado',
                    'nit_empresa',
                    'nombre_empresa',
                    'telefono_empresa'
                )
                ->orderBy('nombres_persona')
                ->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            $tipoDocumento = DB::connection('mysql')
                    ->table('tipo_documento')
                    ->select('id_tipo_documento', 'tipo_documento')
                    ->get()
                    ->keyBy('id_tipo_documento');

            $estados = DB::connection('mysql')
                ->table('estados')
                ->select('id_estado', 'estado')
                ->get()
                ->keyBy('id_estado');

            $tipoPersona = DB::connection('mysql')
                ->table('tipo_persona')
                ->select('id_tipo_persona', 'tipo_persona')
                ->get()
                ->keyBy('id_tipo_persona');

            // Iterar las bajas sin hacer más consultas
            foreach ($personas as $persona) {
                $persona->tipo_documento = $tipoDocumento[$persona->id_tipo_documento]->tipo_documento ?? 'Sin Tipo Documento';
                $persona->estado = $estados[$persona->id_estado]->estado ?? 'Sin estado';
                $persona->tipo_persona = $tipoPersona[$persona->id_tipo_persona]->tipo_persona ?? 'Sin Tipo Persona';
            }

            return response()->json($personas);
            
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
