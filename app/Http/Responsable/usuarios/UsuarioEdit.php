<?php

namespace App\Http\Responsable\usuarios;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;

class UsuarioEdit implements Responsable
{
    protected $idUsuario;

    // =========================================

    public function __construct($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    // =========================================

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
        
        $idUsuario = $this->idUsuario;

        try {
            $usuario = Usuario::leftjoin('roles', 'roles.id', '=', 'usuarios.id_rol')
                ->leftjoin('estados', 'estados.id_estado', '=', 'usuarios.id_estado')
                // ->leftjoin('tipo_documento', 'tipo_documento.id_tipo_documento', '=', 'usuarios.id_tipo_documento')
                ->leftjoin('tipo_persona', 'tipo_persona.id_tipo_persona', '=', 'usuarios.id_tipo_persona')
                ->leftjoin('generos', 'generos.id_genero', '=', 'usuarios.id_genero')
                ->leftjoin('empresas', 'empresas.id_empresa', '=', 'usuarios.id_empresa')
                ->select(
                    'id_usuario',
                    'nombre_usuario',
                    'apellido_usuario',
                    'usuario',
                    'usuarios.id_tipo_documento',
                    // 'tipo_documento',
                    'identificacion',
                    'email',
                    'name AS rol',
                    'usuarios.id_rol',
                    'estado',
                    'usuarios.id_estado',
                    'usuarios.id_tipo_persona',
                    'tipo_persona',
                    'generos.id_genero',
                    'genero',
                    'numero_telefono',
                    'celular',
                    'direccion',
                    'fecha_contrato',
                    'fecha_terminacion_contrato',
                    'empresas.id_empresa',
                    'nombre_empresa'
                )
                ->where('id_usuario', $idUsuario)
                ->first();

            if (isset($usuario) && !is_null($usuario) && !empty($usuario)) {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                $tipoDocumento = DB::connection('mysql')
                        ->table('tipo_documento')
                        ->where('id_tipo_documento', $usuario->id_tipo_documento)
                        ->select('tipo_documento')
                        ->first();
                
                $usuario->tipo_documento = $tipoDocumento->tipo_documento ?? 'Sin Tipo de Documento';

                return response()->json($usuario);
                
            } else {
                return response()->json([
                    'message' => 'No existe usuario'
                ], 404);
            }
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'message' => 'Error consultando el usuario en BD',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}