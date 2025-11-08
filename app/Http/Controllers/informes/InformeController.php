<?php

namespace App\Http\Controllers\informes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InformeCampo;
use App\Models\Informe;

class InformeController extends Controller
{
    public function index(Request $request)
    {
        $campos = InformeCampo::formulario($request['infCodigo']);
        $informe = Informe::where('informe_codigo',$request['infCodigo'])->first();

        return response()->json([
            'campos' => $campos,
            'informe' => $informe
        ]);
    }
}