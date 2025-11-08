<?php

namespace App\Http\Controllers\unidades_medida;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Responsable\unidades_medida\UnidadMedidaIndex;
use App\Http\Responsable\unidades_medida\UnidadMedidaStore;
use App\Http\Responsable\unidades_medida\UnidadMedidaEdit;
use App\Http\Responsable\unidades_medida\UnidadMedidaUpdate;
use App\Http\Responsable\unidades_medida\UnidadMedidaDestroy;
use App\Models\UnidadMedida;
use App\Helpers\DatabaseConnectionHelper;
use App\Models\Empresa;

class UnidadesMedidaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new UnidadMedidaIndex();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new UnidadMedidaStore();
    }

    // ======================================================================
    // ======================================================================

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        //
    }

    // ======================================================================
    // ======================================================================

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($idUmd)
    {
        return new UnidadMedidaEdit($idUmd);
    }

    // ======================================================================
    // =====================================================================c=

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idUmd)
    {
        return new UnidadMedidaUpdate($request, $idUmd);
    }

    // ======================================================================
    // ======================================================================

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUmd)
    {
        // return new UnidadMedidaDestroy($idUmd);
    }
}
