<?php

namespace App\Http\Responsable\usuarios;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Illuminate\Support\Facades\DB;


class UsuarioIndex implements Responsable
{
    public function toResponse($request)
    {
        try {
            // Obtener id_empresa_usuario del request
            $idEmpresaUsuario = $request->input('id_empresa_usuario');

            // 2. Buscar empresa completa usando el ID
            $empresaActual = Empresa::find($idEmpresaUsuario);
            
            // Configurar conexión tenant si hay empresa
            if ($empresaActual) {
                DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
            }

            $query = Usuario::leftjoin('roles', 'roles.id', '=', 'usuarios.id_rol')
                // ->leftjoin('estados', 'estados.id_estado', '=', 'usuarios.id_estado')
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
                    // 'estado',
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
                );

            // Si el usuario no es de Softdimo (id_empresa != 5), filtrar por su empresa
            if ($idEmpresaUsuario != 5) {
                $query->where('usuarios.id_empresa', $idEmpresaUsuario);
            }

            $usuarios = $query->orderBy('nombre_usuario')->get();

            // Restaurar conexión principal si se usó tenant
            if ($empresaActual) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }

            // ====================================
            // 🔹 Consultar tipo_documento y estados (base principal)
            // ====================================
            $tiposDocumento = DB::connection('mysql')
                ->table('tipo_documento')
                ->select('id_tipo_documento', 'tipo_documento')
                ->get()
                ->keyBy('id_tipo_documento');

            $estados = DB::connection('mysql')
                ->table('estados')
                ->select('id_estado', 'estado')
                ->get()
                ->keyBy('id_estado');

            // ====================================
            // 🔹 Recorrer los usuarios (una sola pasada)
            // ====================================
            
            foreach ($usuarios as $usuario) {
                $usuario->tipo_documento = $tiposDocumento[$usuario->id_tipo_documento]->tipo_documento ?? 'Sin Tipo de Documento';
                $usuario->estado = $estados[$usuario->id_estado]->estado ?? 'Sin Estado';
            }

            // 3. Agregar nombre completo del usuario desde la base principal
            // foreach ($usuarios as $usuario) {
            //     $tipoDocumento = DB::connection('mysql') // o la conexión principal que uses
            //         ->table('tipo_documento')
            //         ->where('id_tipo_documento', $usuario->id_tipo_documento)
            //         ->select('tipo_documento')
            //         ->first();

            //     $usuario->tipo_documento = $tipoDocumento->tipo_documento ?? 'Sin Tipo de Documento';
            // }

            return response()->json($usuarios);
            
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'message' => 'Error en la consulta de la base de datos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
