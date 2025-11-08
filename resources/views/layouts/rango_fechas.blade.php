<div class="col-md-3">
    <div class="form-group">
        <label for="{{$data->informe_descripcion}}_{{$data->id}}_date">{{$data->informe_descripcion}} Inicial - Final</label>
        <input type="text" name="filtro[{{$data->id}}]"
                id="{{$data->informe_descripcion}}_{{$data->id}}_date"
                class="form-control rango_fecha" autocomplete="off" readonly="true">
        <span class="bar"></span>
    </div>
</div>
