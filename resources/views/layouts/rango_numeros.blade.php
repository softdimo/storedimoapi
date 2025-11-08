<div class="col-md-3">
    <div class="form-group">
        <input type="text" name="filtro[{{$data->informe_codigo}}][inicial]" id="{{$name}}_inicial"
                class="form-control rango_numeros" autocomplete="off">
        <label for="{{$name}}_inicial">{{$data->informe_descripcion}} Inicial</label>
    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <input type="text" name="filtro[{{$data->informe_codigo}}][final]"
                id="{{$name}}_final" class="form-control rango_numeros" autocomplete="off">
        <label for="{{$name}}_final">{{$data->informe_descripcion}} Final</label>
    </div>
</div>