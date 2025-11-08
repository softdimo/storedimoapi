<?php

namespace App\Http\Responsable\entradas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;

class EntradaIndex implements Responsable
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
            $entradas = Compra::leftjoin('proveedores','proveedores.id_proveedor','=','compras.id_proveedor')
                ->leftjoin('productos','productos.id_producto','=','compras.id_producto')
                ->leftjoin('estados','estados.id_estado','=','compras.id_estado')
                ->leftjoin('empresas','empresas.id_empresa','=','compras.id_empresa')
                ->select(
                    'compras.id_compra',
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
                    'estado',
                    'compras.id_producto',
                    'nombre_producto',
                    'cantidad',
                    'precio_unitario'
                )
                ->orderByDesc('fecha_compra')
                ->get();

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                // 3. Agregar nombre completo del usuario desde la base principal
                foreach ($entradas as $entrada) {
                    $usuario = DB::connection('mysql') // o la conexión principal que uses
                        ->table('usuarios')
                        ->where('id_usuario', $entrada->id_usuario)
                        ->select(DB::raw("CONCAT(nombre_usuario, ' ', apellido_usuario) as nombres_usuario"))
                        ->first();

                    $entrada->nombres_usuario = $usuario->nombres_usuario ?? 'Sin usuario';
                }

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
