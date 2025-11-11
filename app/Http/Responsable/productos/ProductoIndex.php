<?php

namespace App\Http\Responsable\productos;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Producto;
use App\Models\Empresa;
use App\Helpers\DatabaseConnectionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductoIndex implements Responsable
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
        
        try
        {
            $productos = Producto::leftJoin('categorias', 'categorias.id_categoria', '=', 'productos.id_categoria')
                // ->leftJoin('estados', 'estados.id_estado', '=', 'productos.id_estado')
                ->leftJoin('tipo_persona', 'tipo_persona.id_tipo_persona', '=', 'productos.id_tipo_persona')
                ->join('unidades_medida', 'unidades_medida.id', '=', 'productos.id_umd')
                ->leftJoin('proveedores', 'proveedores.id_proveedor', '=', 'productos.id_proveedor')
                ->select(
                    'id_producto',
                    'imagen_producto',
                    'nombre_producto',
                    'productos.id_categoria',
                    'id_umd',
                    'categorias.categoria',
                    'precio_unitario',
                    'precio_detal',
                    'precio_por_mayor',
                    'productos.descripcion',
                    'proveedores.id_proveedor',
                    'proveedores.nombres_proveedor',
                    'stock_minimo',
                    'productos.id_estado',
                    // 'estados.estado',
                    'cantidad',
                    'tipo_persona.id_tipo_persona',
                    'tipo_persona',
                    'referencia',
                    'fecha_vencimiento',
                    'unidades_medida.descripcion AS umd'
                )
                ->orderBy('id_producto', 'desc')
                ->get();

            if (isset($productos) && !is_null($productos) && !empty($productos))
            {
                //Agregar el estado de vencimiento a cada producto
                $productos = $productos->map(function ($producto) {
                    if (!empty($producto->fecha_vencimiento)) {
                        $hoy = Carbon::now();
                        $vencimiento = Carbon::parse($producto->fecha_vencimiento);
                        $diasRestantes = $hoy->diffInDays($vencimiento, false);

                        if ($diasRestantes < 0) {
                            $producto->estado_vencimiento = 'vencido';
                        } elseif ($diasRestantes <= 30) {
                            $producto->estado_vencimiento = 'próximo a vencer';
                        } else {
                            $producto->estado_vencimiento = 'vigente';
                        }
                    } else {
                        // Si no tiene fecha de vencimiento, se deja en null
                        $producto->estado_vencimiento = null;
                    }

                    return $producto;
                });

                // Restaurar conexión principal si se usó tenant
                if ($empresaActual) {
                    DatabaseConnectionHelper::restaurarConexionPrincipal();
                }

                $estados = DB::connection('mysql')
                    ->table('estados')
                    ->select('id_estado', 'estado')
                    ->get()
                    ->keyBy('id_estado');

                // Iterar las bajas sin hacer más consultas
                foreach ($productos as $producto) {
                    $producto->estado = $estados[$producto->id_estado]->estado ?? 'Sin estado';
                }

                // Retornar productos con su estado de vencimiento incluido
                return response()->json($productos);
            } else
            {
                return response()->json([
                    'message' => 'no hay productos'
                ], 404);
            }
        } catch (Exception $e) {
            // Asegurar restauración de conexión principal en caso de error
            if (isset($empresaActual))
            {
                DatabaseConnectionHelper::restaurarConexionPrincipal();
            }
            
            return response()->json([
                'message' => 'Error en la consulta de la base de datos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
