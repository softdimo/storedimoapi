<?php

namespace App\Http\Responsable\personas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Persona;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\Log;

class PersonaStore implements Responsable
{
    public function toResponse($request)
    {
        $idTipoPersona = request('id_tipo_persona', null);
        $idTipoDocumento = request('id_tipo_documento', null);
        $identificacion = request('identificacion', null);
        $nombrePersona = request('nombres_persona', null);
        $apellidoPersona = request('apellidos_persona', null);
        $numeroTelefono = request('numero_telefono', null);
        $celular = request('celular', null);
        $email = request('email', null);
        $idGenero = request('id_genero', null);
        $direccion = request('direccion', null);
        $idEstado = request('id_estado', null);
        $nitEmpresa = request('nit_empresa', null);
        $nombreEmpresa = request('nombre_empresa', null);
        $telefonoEmpresa = request('telefono_empresa', null);

        // ================================================
        
        try {
            // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
            $empresaId = $request->input('empresa_actual');

            // 2. Buscar empresa completa usando el ID
            $empresaActual = Empresa::find($empresaId);
            
            // Configurar conexión tenant si hay empresa
            if ($empresaActual) {
                DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
            }

            Persona::create([
                'id_tipo_persona' => $idTipoPersona,
                'id_tipo_documento' => $idTipoDocumento,
                'identificacion' => $identificacion,
                'nombres_persona' => $nombrePersona,
                'apellidos_persona' => $apellidoPersona,
                'numero_telefono' => $numeroTelefono,
                'celular' => $celular,
                'email' => $email,
                'id_genero' => $idGenero,
                'direccion' => $direccion,
                'id_estado' => $idEstado,
                'nit_empresa' => $nitEmpresa,
                'nombre_empresa' => $nombreEmpresa,
                'telefono_empresa' => $telefonoEmpresa,
            ]);

            // ================================================

            // Restaurar conexión principal
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
    
            return response()->json(['success' => true]);
            
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
