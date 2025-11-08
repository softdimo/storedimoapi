<?php

namespace App\Models;

use App\Models\Informe;
use App\Models\InformeInnerJoin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable; // Interfaz
use OwenIt\Auditing\Auditable as AuditableTrait; // Trait

class InformeCampo extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    protected $connection = 'mysql';
    protected $table = 'informexcampo';
    protected $primaryKey = 'id';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    public static function formulario($id)
    {
            
        $informeCampos = InformeCampo::where('estado_campo', true)
                            ->whereInformeCodigo($id)
                            ->orderBy('informe_descripcion', 'ASC')
                            ->get();

        $resultado = [
            'inputs'=>[],
            'checks'=>[],
        ];

        foreach ($informeCampos as $infoCampo)
        {
            $check = View::make('layouts.checkbox', ['data' => $infoCampo]);
            array_push($resultado['checks'], $check->render());
            if ($infoCampo->campo_filtro)
            {
                $nombreDB = InformeCampo::obtenerNombreCampo($infoCampo);
                switch ($infoCampo->filtro_tipo) {
                    case 1:
                        $resultado = InformeCampo::select($infoCampo, $resultado, $nombreDB);
                    case 2:
                        # code...
                    break;
                    case 3:
                        $resultado = InformeCampo::rangoNumeros($infoCampo, $resultado, $nombreDB);
                    break;
                    case 4:
                    case 8:
                        $resultado = InformeCampo::text($infoCampo, $resultado, $nombreDB);
                    break;
                    case 5:
                    case 6:
                    case 9:
                        $resultado = InformeCampo::rangoFechas($infoCampo, $resultado, $nombreDB);
                    break;
                    case 7:
                        $resultado = InformeCampo::selectMultiple($infoCampo, $resultado, $nombreDB);
                    break;
                }
            }

        }
        return $resultado;
    }

    /**
     * combierte el array que contiene un objeto dentro en una collecion
     * @param  stdclass $array array de objetos resultado de la consulta a la base de datos
     * @param  InformeCampo $infoCampo fila de la tabla informexcampo_new
     * @return collection        coleccion con key value para llenar las opciones de los select
     */
    public static function arrayObjectToCollection($array, $infoCampo)
    {
        $collect = collect();
        if (is_array($array)) {
            $array = json_decode(json_encode($array), true);

            foreach ($array as $value) {
                $collect->push([
                    'key'=>$value[$infoCampo->option_value], 'value'=>$value[$infoCampo->option_contenido]
                ]);
            }
        }
        return $collect;
    }

    /**
     * obtiene el nombre del campo buscandolo en la columna cam_select_sql
     * @param  InformeCampo $infoCampo fila de la tabla informexcampo_new
     * @return [type]        [description]
     */
    public static function obtenerNombreCampo($infoCampo)
    {
        if (($pos = strpos($infoCampo->select_sql, ' AS ')) == 0) {
            $nombreDB = $infoCampo->select_sql;
        } else {
            $nombreDB = substr($infoCampo->select_sql, $pos + 4);
        }
        return $nombreDB;
    }

    /**
     * [select description]
     * @param  InformeCampo $infoCampo fila de la tabla informexcampo_new
     * @param  array $resultado contiene los checks, inputs, selects
     * @param  string $nombreDB  nombre de la columna, se le asigna al campo
     * @return array $resultado contiene los checks, inputs, selects
     */
    public static function select($infoCampo, $resultado, $nombreDB)
    {
        if (is_null($infoCampo->filtro_value))
        {

            $result = DB::select($infoCampo->filtro_sql);
            $opciones = InformeCampo::arrayObjectToCollection($result, $infoCampo);
           
        }else
        {
            $opciones = collect([ 'opcion' => $infoCampo->filtro_value]);
        }
        
        $input = View::make('layouts.select', ['data' => $infoCampo,'name'=>$nombreDB, 'opciones'=>$opciones]);
        array_push($resultado['inputs'], $input->render());
        return $resultado;
    }

    /**
     * [selectMultiple description]
     * @param  InformeCampo $infoCampo fila de la tabla informexcampo_new
     * @param  array $resultado contiene los checks, inputs, selects
     * @param  string $nombreDB  nombre de la columna, se le asigna al campo
     * @return array $resultado contiene los checks, inputs, selects
     */
    public static function selectMultiple($infoCampo, $resultado, $nombreDB)
    {
        if (is_null($infoCampo->filtro_value))
        {
  
            $result = DB::select($infoCampo->filtro_sql);
            
            $opciones = InformeCampo::arrayObjectToCollection($result, $infoCampo);
        } else
        {
            $opciones = collect(['opcion'=>$infoCampo->filtro_value]);
        }
        $input = View::make('layouts.select_multiple', ['data' => $infoCampo,'name'=>$nombreDB, 'opciones'=>$opciones]);
        array_push($resultado['inputs'], $input->render());
        return $resultado;
    }

    /**
     * [rangoFechas description]
     * @param  InformeCampo $infoCampo fila de la tabla informexcampo_new
     * @param  array $resultado contiene los checks, inputs, selects
     * @param  string $nombreDB  nombre de la columna, se le asigna al campo
     * @return array $resultado contiene los checks, inputs, selects
     */
    public static function rangoFechas($infoCampo, $resultado, $nombreDB)
    {
        $input = View::make('layouts.rango_fechas', ['data' => $infoCampo, 'name'=>$nombreDB ]);
        array_push($resultado['inputs'], $input->render());
        return $resultado;
    }

    /**
     * [rangoNumeros description]
     * @param  InformeCampo $infoCampo fila de la tabla informexcampo_new
     * @param  array $resultado contiene los checks, inputs, selects
     * @param  string $nombreDB  nombre de la columna, se le asigna al campo
     * @return array $resultado contiene los checks, inputs, selects
     */
    public static function rangoNumeros($infoCampo, $resultado, $nombreDB)
    {
        $input = View::make('layouts.rango_numeros', ['data' => $infoCampo, 'name'=>$nombreDB ]);
        array_push($resultado['inputs'], $input->render());
        return $resultado;
    }

    /**
     * [text description]
     * @param  InformeCampo $infoCampo fila de la tabla informexcampo_new
     * @param  array $resultado contiene los checks, inputs, selects
     * @param  string $nombreDB  nombre de la columna, se le asigna al campo
     * @return array $resultado contiene los checks, inputs, selects
     */
    public static function text($infoCampo, $resultado, $nombreDB)
    {
        $input = View::make('layouts.text', ['data' => $infoCampo, 'name'=>$nombreDB ]);
        array_push($resultado['inputs'], $input->render());
        return $resultado;
    }
}