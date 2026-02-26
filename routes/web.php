<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


// =====================================================================

/**
 * Rutas de Administración (BD Principal)
 * - Gestión de usuarios
 * - Permisos y roles
 * - Configuración de empresas
 */

$router->group(['prefix' => 'api/administracion'], function () use ($router) {
    // USUARIOS
    $router->get('usuarios_index', 'usuarios\UsuariosController@index');
    $router->post('query_identificacion', 'usuarios\UsuariosController@consultarId');
    $router->post('query_usuario', 'usuarios\UsuariosController@consultaUsuario');
    $router->post('usuario_store', 'usuarios\UsuariosController@store');
    $router->get('usuario_edit/{idUsuario}', 'usuarios\UsuariosController@edit');
    $router->post('query_usuario_update/{idUsuario}', 'usuarios\UsuariosController@queryUsuarioUpdate');
    $router->put('usuario_update/{idUsuario}', 'usuarios\UsuariosController@update');
    $router->post('cambiar_clave/{idUsuario}', 'usuarios\UsuariosController@cambiarClave');
    $router->post('consulta_recuperar_clave', 'usuarios\UsuariosController@consultaRecuperarClave');
    $router->post('inactivar_usuario/{idUsuario}', 'usuarios\UsuariosController@inactivarUsuario');
    $router->post('actualizar_clave_fallas/{idUsuario}', 'usuarios\UsuariosController@actualizarClaveFallas');
    $router->post('validar_email', 'usuarios\UsuariosController@validarEmail');
    $router->post('validar_identificacion', 'usuarios\UsuariosController@validarIdentificacion');
    $router->post('validar_email_login', 'usuarios\UsuariosController@validarEmailLogin');
    $router->get('consulta_usuario_logueado/{idUsuario}', 'usuarios\UsuariosController@consultaUsuarioLogueado');
    $router->get('consultar_session_token/{idUsuario}', 'usuarios\UsuariosController@consultarSessionToken');
    $router->post('actualizar_token_sesion/{idUsuario}', 'usuarios\UsuariosController@actualizarTokenSesion');

    // ========================================================================

    // Roles y Permisos
    $router->post('guardar_rol', 'roles_permisos\RolesPermisosController@crearRol');
    $router->post('guardar_permiso', 'roles_permisos\RolesPermisosController@crearPermiso');
    $router->post('crear_permiso_usuario', 'roles_permisos\RolesPermisosController@crearPermisosUsuario');
    $router->post('consultar_permisos', 'roles_permisos\RolesPermisosController@consultarPermisosPorUsuario');
    $router->get('permisos_por_usuario_trait/{idUsuario}', 'roles_permisos\RolesPermisosController@permisosPorUsuarioTrait');
    $router->get('permisos_trait', 'roles_permisos\RolesPermisosController@permisosTrait');
    $router->get('permisos_view_share_trait', 'roles_permisos\RolesPermisosController@permisosViewShareTrait');

    // ========================================================================

    // EMPRESAS
    $router->get('empresa_index', 'empresas\EmpresasController@index');
    $router->post('empresa_store', 'empresas\EmpresasController@store');
    $router->put('empresa_update/{idEmpresa}', 'empresas\EmpresasController@update');
    $router->post('consultar_empresa', 'empresas\EmpresasController@consultarEmpresa');
    $router->get('empresa_edit/{idEmpresa}', 'empresas\EmpresasController@edit');
    $router->post('validar_nit', 'empresas\EmpresasController@validar_nit');
    $router->post('validar_correo_empresa', 'empresas\EmpresasController@validarCorreoEmpresa');

    // Informes Gerenciales
    $router->post('informe_gerencial', 'informes\InformeController@index');

    // ========================================================================

    // SUSCRIPCIONES
    $router->get('suscripcion_index', 'suscripciones\SuscripcionesController@index');
    $router->post('suscripcion_store', 'suscripciones\SuscripcionesController@store');
    $router->get('suscripcion_edit/{idSuscripcion}', 'suscripciones\SuscripcionesController@edit');
    $router->put('suscripcion_update/{idSuscripcion}', 'suscripciones\SuscripcionesController@update');
    $router->get('suscripcion_empresa_estado_login/{idEmpresa}', 'suscripciones\SuscripcionesController@suscripcionEmpresaEstadoLogin');
    $router->post('suscripcion_actualizar_estado_automatico/{idSuscripcion}', 'suscripciones\SuscripcionesController@suscripcionActualizarEstadoAutomatico');

    // ========================================================================

    // PLANES
    $router->get('plan_index', 'planes\PlanesController@index');
    $router->post('plan_store', 'planes\PlanesController@store');
    $router->get('plan_edit/{idPlan}', 'planes\PlanesController@edit');
    $router->put('plan_update/{idPlan}', 'planes\PlanesController@update');

    // TRAITS - RUTA CONSOLIDADA (Para evitar timeouts de 60s)
    $router->get('empresas_disponibles_suscripcion/{id}', 'traits\TraitsController@getEmpresasSuscripcion');

    // ========================================================================

    // MÉTRICAS
    $router->post('metricas_store', 'metricas\MetricasController@store');
    $router->get('metricas_index', 'metricas\MetricasController@index');
    $router->post('query_total_absoluto', 'metricas\MetricasController@queryTotalAbsoluto');
    $router->post('query_subtotal_actividad', 'metricas\MetricasController@querySubtotalActividad');
    $router->post('query_movimiento_bd', 'metricas\MetricasController@queryMovimientoBd');
    $router->post('query_por_fuente', 'metricas\MetricasController@queryPorFuente');
    $router->post('query_ranking_tenants', 'metricas\MetricasController@queryRankingTenants');
    $router->post('query_monitoreo_errores', 'metricas\MetricasController@queryMonitoreoErrores');
    $router->post('query_rutas_utilizadas', 'metricas\MetricasController@queryRutasUtilizadas');
    $router->post('query_actividad_horas', 'metricas\MetricasController@queryActividadHoras');
    $router->post('borrar_registros', 'metricas\MetricasController@borrarRegistros');
}); // FIN api/administracion

// ========================================================================
// ========================================================================
// ========================================================================

$router->group(['prefix' => 'api'], function () use ($router) {
    // CATEGORIAS
    $router->get('categoria_index', 'categorias\CategoriasController@index');
    $router->post('categoria_store', 'categorias\CategoriasController@store');
    $router->put('categoria_update/{id}', 'categorias\CategoriasController@update');
    $router->post('consulta_categoria', 'categorias\CategoriasController@consultaCategoria');
    $router->post('cambiar_estado_categoria/{idCategoria}', 'categorias\CategoriasController@destroy');
    $router->get('categoria_edit/{idCategoria}', 'categorias\CategoriasController@edit');
    $router->get('categorias_trait', 'categorias\CategoriasController@categoriasTrait');
    $router->get('umd_trait', 'productos\ProductosController@consultarUmd');

    // ========================================================================

    // PRODUCTOS
    $router->get('producto_index', 'productos\ProductosController@index');
    $router->post('verificar_producto', 'productos\ProductosController@verificarProducto');
    $router->post('producto_store', 'productos\ProductosController@store');
    $router->post('producto_show/{idProducto}', 'productos\ProductosController@show');
    $router->post('producto_edit/{idProducto}', 'productos\ProductosController@edit');
    $router->put('producto_update/{idProducto}', 'productos\ProductosController@update');
    $router->post('cambiar_estado_producto/{idProducto}', 'productos\ProductosController@destroy');
    $router->post('query_producto/{idProducto}', 'productos\ProductosController@queryProducto');
    $router->get('query_producto_update/{idProducto}', 'productos\ProductosController@queryProductoUpdate');
    $router->get('reporte_productos_pdf', 'productos\ProductosController@reporteProductosPdf');
    $router->post('verificar_referencia', 'productos\ProductosController@referenceValidator');
    $router->get('productos_trait_ventas', 'productos\ProductosController@productosTraitVentas');
    $router->get('productos_trait_compras', 'productos\ProductosController@productosTraitCompras');
    $router->get('productos_trait_existencias', 'productos\ProductosController@productosTraitExistencias');
    $router->get('productos_por_proveedor/{idProveedor}', 'productos\ProductosController@productosPorProveedor');

    // ========================================================================

    // PERSONAS
    $router->get('personas_index', 'personas\PersonasController@index');
    $router->post('query_id_persona', 'personas\PersonasController@consultarIdPersona');
    $router->post('query_nit_empresa', 'personas\PersonasController@consultarNitEmpresa');
    $router->post('persona_store', 'personas\PersonasController@store');
    $router->put('persona_update/{idPersona}', 'personas\PersonasController@update');
    $router->get('persona_edit/{idPersona}', 'personas\PersonasController@edit');
    $router->get('clientes_trait', 'personas\PersonasController@personaTrait');

    // ========================================================================

    // PROVEEDORES
    $router->get('proveedores_index', 'proveedores\ProveedoresController@index');
    $router->post('query_identificacion_proveedor', 'proveedores\ProveedoresController@consultarIdentificacionProveedor');
    $router->post('query_nit_proveedor', 'proveedores\ProveedoresController@consultarNitProveedor');
    $router->post('proveedor_store', 'proveedores\ProveedoresController@store');
    $router->put('proveedor_update/{idProveedor}', 'proveedores\ProveedoresController@update');
    $router->get('proveedor_edit/{idProveedor}', 'proveedores\ProveedoresController@edit');
    $router->get('proveedores_trait', 'proveedores\ProveedoresController@proveedoresTrait');
    $router->post('validar_correo_proveedor', 'proveedores\ProveedoresController@validarCorreoProveedor');

    // ========================================================================

    // ENTRADAS
    $router->get('entrada_index', 'entradas\EntradasController@index');
    $router->post('entrada_store', 'entradas\EntradasController@store');
    $router->post('anular_compra/{idCompra}', 'entradas\EntradasController@anularCompra');
    $router->post('reporte_compras_pdf', 'entradas\EntradasController@reporteComprasPdf');
    $router->post('detalle_compra/{idCompra}', 'entradas\EntradasController@detalleCompra');
    $router->get('entrada/{idEntrada}', 'entradas\EntradasController@entrada');
    $router->post('detalle_compra_pdf/{idCompra}', 'entradas\EntradasController@detalleCompraProductoPdf');
    $router->get('entrada_dia_mes', 'entradas\EntradasController@entradaDiaMes');

    // ========================================================================

    // VENTAS
    $router->get('venta_index', 'ventas\VentasController@index');
    $router->get('venta/{idVenta}', 'ventas\VentasController@ventaDetalle');
    $router->post('venta_store', 'ventas\VentasController@store');
    $router->post('anular_venta/{idVenta}', 'ventas\VentasController@anularVenta');
    $router->post('reporte_ventas_pdf', 'ventas\VentasController@reporteVentasPdf');
    $router->post('detalle_venta/{idVenta}', 'ventas\VentasController@detalleVenta');
    $router->get('venta_dia_mes', 'ventas\VentasController@ventaDiaMes');

    // ========================================================================

    // EXISTENCIAS-BAJAS
    $router->get('baja_index', 'existencias\ExistenciasController@bajaIndex');
    $router->get('baja/{idBaja}', 'existencias\ExistenciasController@baja');
    $router->post('baja_store', 'existencias\ExistenciasController@bajaStore');
    $router->post('baja_detalle/{idBaja}', 'existencias\ExistenciasController@bajaDetalle');
    $router->post('reporte_bajas_pdf', 'existencias\ExistenciasController@reporteBajasPdf');
    $router->get('stock_minimo_index', 'existencias\ExistenciasController@stockMinimoIndex');
    $router->get('alerta_stock_minimo', 'existencias\ExistenciasController@alertaStockMinimo');
    
    $router->get('alerta_fecha_vencimiento', 'existencias\ExistenciasController@alertaFechaVencimiento');
    $router->get('fechas_vencimiento_index', 'existencias\ExistenciasController@fechasVencimientoIndex');

    // ========================================================================

    // PAGO EMPLEADOS
    $router->get('pago_empleado_index', 'pago_empleados\PagoEmpleadosController@index');
    $router->get('pago_empleado_create', 'pago_empleados\PagoEmpleadosController@create');
    $router->post('pago_empleado_store', 'pago_empleados\PagoEmpleadosController@store');

    // ========================================================================

    // PRESTAMOS
    $router->get('prestamo_index', 'prestamos\PrestamosController@index');
    $router->get('prestamo_create', 'prestamos\PrestamosController@create');
    $router->post('prestamo_store', 'prestamos\PrestamosController@store');
    $router->get('prestamo_vencer', 'prestamos\PrestamosController@prestamoVencer');

    // ========================================================================

    // UNIDADES DE MEDIDA
    $router->get('unidad_medida_index', 'unidades_medida\UnidadesMedidaController@index');
    $router->post('unidad_medida_store', 'unidades_medida\UnidadesMedidaController@store');
    $router->get('unidad_medida_edit/{idUmd}', 'unidades_medida\UnidadesMedidaController@edit');
    $router->put('unidad_medida_update/{idUmd}', 'unidades_medida\UnidadesMedidaController@update');
    $router->post('unidad_medida_destroy/{idUmd}', 'unidades_medida\UnidadesMedidaController@destroy');

    // ========================================================================

    // TRAITS - RUTA CONSOLIDADA (Para evitar timeouts de 60s)
    $router->get('config_inicial_trait', 'traits\TraitsController@getConfigInicial');
    // $router->get('empresas_disponibles_suscripcion/{id}', 'traits\TraitsController@getEmpresasSuscripcion');
}); // api

