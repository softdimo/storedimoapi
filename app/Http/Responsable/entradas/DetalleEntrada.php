<?php

namespace App\Http\Responsable\entradas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;

class DetalleEntrada implements Responsable
{
    protected $idEntrada;

    public function __construct($idEntrada)
    {
        $this->idEntrada = $idEntrada;
    }

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
            $entradas = Compra::leftjoin('proveedores','proveedores.id_proveedor','=','compras.id_proveedor')
                ->leftjoin('productos','productos.id_producto','=','compras.id_producto')
                // ->leftjoin('estados','estados.id_estado','=','compras.id_estado')
                ->leftjoin('empresas','empresas.id_empresa','=','compras.id_empresa')
                ->select(
                    'compras.id_compra',
                    'factura_compra',
                    'fecha_compra',
                    DB::raw("CONCAT('$', FORMAT(valor_compra, 0, 'de_DE')) as valor_compra"),
                    'compras.id_proveedor',
                    'proveedores.proveedor_juridico',
                    'proveedores.nit_proveedor',
                    'proveedores.identificacion',
                    'proveedores.nombres_proveedor',
                    'proveedores.apellidos_proveedor',
                    'compras.id_usuario',
                    'empresas.id_empresa',
                    'empresas.nombre_empresa as empresa',
                    'compras.id_estado',
                    // 'estado',
                    'compras.id_producto',
                    'nombre_producto',
                    'cantidad',
                    'precio_unitario'
                )
                ->where('compras.id_compra', $this->idEntrada)
                ->orderByDesc('fecha_compra')
                ->first();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                // ==============================
                //   CONSULTAS EN BD PRINCIPAL
                // ==============================
                if ($entradas) {
                    // Obtener todos los usuarios (clave = id_usuario)
                    $usuarios = DB::connection('mysql')
                        ->table('usuarios')
                        ->select(
                            'id_usuario',
                            DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario")
                        )
                        ->get()
                        ->keyBy('id_usuario');

                    // Obtener todos los estados (clave = id_estado)
                    $estados = DB::connection('mysql')
                        ->table('estados')
                        ->select('id_estado', 'estado')
                        ->get()
                        ->keyBy('id_estado');

                    // Asignar nombres amigables
                    $entradas->nombres_usuario = $usuarios[$entradas->id_usuario]->nombres_usuario ?? 'Sin usuario';
                    $entradas->estado = $estados[$entradas->id_estado]->estado ?? 'Sin estado';
                }

                // 3. Agregar nombre completo del usuario desde la base principal
                // if ($entradas) {
                //     $usuario = DB::connection('mysql')
                //         ->table('usuarios')
                //         ->where('id_usuario', $entradas->id_usuario)
                //         ->select(DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario"))
                //         ->first();
                
                //     $entradas->nombres_usuario = $usuario->nombres_usuario ?? 'Sin usuario';
                // }

                return response()->json($entradas);

        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
