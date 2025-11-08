<?php

namespace App\Http\Responsable\personas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Persona;
use App\Models\Empresa;

class PersonaUpdate implements Responsable
{
    protected $request;
    protected $idPersona;

    public function __construct(Request $request, $idPersona)
    {
        $this->request = $request;
        $this->idPersona = $idPersona;
    }

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

            $persona = Persona::find($this->idPersona);

            if (isset($persona) && !is_null($persona) && !empty($persona)) {
            
                $persona->id_tipo_persona = $request->input('id_tipo_persona');
                $persona->id_tipo_documento = $request->input('id_tipo_documento');
                $persona->identificacion = $request->input('identificacion');
                $persona->nombres_persona = $request->input('nombres_persona');
                $persona->apellidos_persona = $request->input('apellidos_persona');
                $persona->numero_telefono = $request->input('numero_telefono');
                $persona->celular = $request->input('celular');
                $persona->email = $request->input('email');
                $persona->id_genero = $request->input('id_genero');
                $persona->direccion = $request->input('direccion');
                $persona->id_estado = $request->input('id_estado');
                $persona->nit_empresa = $request->input('nit_empresa');
                $persona->nombre_empresa = $request->input('nombre_empresa');
                $persona->telefono_empresa = $request->input('telefono_empresa');
                $persona->update();

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

    // ===================================================================
    // ===================================================================


}
