<?php

namespace App\Http\Responsable\entradas;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Compra;
use App\Models\CompraProducto;
use App\Models\Producto;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class EntradaStore implements Responsable
{
    public function toResponse($request)
    {
        $idEmpresa = request('id_empresa', null);
        $facturaCompra = request('factura_compra', null);
        $fechaCompra = request('fecha_compra', null);
        $valorCompra = request('valor_compra', null);
        $idProveedor = request('id_proveedor', null);
        $usuLogueado = request('id_usuario', null);
        $idEstado = request('id_estado', null);
        $productos = request('productos', []);

        try {
            // 1. Obtener ID de empresa del request (antes era empresa_actual completo)
            $empresaId = $request->input('empresa_actual');

            // 2. Buscar empresa completa usando el ID
            $empresaActual = Empresa::find($empresaId);
            
            // Configurar conexión tenant si hay empresa
            if ($empresaActual) {
                DatabaseConnectionHelper::configurarConexionTenant($empresaActual->toArray());
            }

            $crearCompra = Compra::create([
                'id_empresa' => $idEmpresa,
                'factura_compra' => $facturaCompra,
                'fecha_compra' => $fechaCompra,
                'valor_compra' => $valorCompra,
                'id_proveedor' => $idProveedor,
                'id_usuario' => $usuLogueado,
                'id_estado' => $idEstado
            ]);
            
            if ($crearCompra) {

                $idCompra = $crearCompra->id_compra;

                foreach ($productos as $producto) {
                    CompraProducto::create([
                        'id_compra' => $idCompra,
                        'id_producto' => $producto['id_producto'],
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario_compra' => $producto['p_unitario'],
                        'subtotal' => $producto['subtotal']
                    ]);

                    $cantidadProducto = Producto::select('cantidad')
                        ->where('id_producto', $producto['id_producto'])
                        ->first();

                    if ( is_null($cantidadProducto) || empty($cantidadProducto) ) {
                        $cantidad = 0 + $producto['cantidad'];
                    } else {
                        $cantidad = $cantidadProducto->cantidad + $producto['cantidad'];
                    }

                    $producto = Producto::findOrFail($producto['id_producto']);

                    $producto->cantidad = $cantidad;
                    $producto->update();
                }

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
}
