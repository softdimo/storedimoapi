<?php

namespace App\Http\Responsable\productos;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Producto;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;

class ProductoStore implements Responsable
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
        
        $idTipoPersona = request('id_tipo_persona', null);
        $imagenProducto = request('imagen_producto', null);
        $nombreProducto = request('nombre_producto', null);
        $idCategoria = request('id_categoria', null);
        $precioUnitario = request('precio_unitario', null);
        $precioDetal = request('precio_detal', null);
        $precioPorMayor = request('precio_por_mayor', null);
        $descripcion = request('descripcion', null);
        $stockMinimo = request('stock_minimo', null);
        $idEstado = request('id_estado', null);
        $referencia = request('referencia', null);
        $fechaVencimiento = request('fecha_vencimiento', null);
        $idUnidadMedida = request('id_umd', null);
        $idProveedor = request('id_proveedor', null);

        // ================================================

        try {
            $nuevoProducto = Producto::create([
                'id_tipo_persona' => $idTipoPersona,
                'imagen_producto' => $imagenProducto,
                'nombre_producto' => $nombreProducto,
                'id_categoria' => $idCategoria,
                'precio_unitario' => $precioUnitario,
                'precio_detal' => $precioDetal,
                'precio_por_mayor' => $precioPorMayor,
                'descripcion' => $descripcion,
                'stock_minimo' => $stockMinimo,
                'id_estado' => $idEstado,
                'referencia' => $referencia,
                'fecha_vencimiento' => $fechaVencimiento,
                'id_umd' => $idUnidadMedida,
                'id_proveedor' => $idProveedor
            ]);
    
            if (isset($nuevoProducto) && !is_null($nuevoProducto) && !empty($nuevoProducto))
            {
                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                return response()->json(['success' => true]);
            }
        } catch (Exception $e)
        {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual)) {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json(['error_bd' => $e->getMessage()]);
        }
    }
}
