<div class="col-md-3">
    <div class="form-group m-b-40">
        <label for="{{$name}}_multiple">{{$data->informe_descripcion}}</label>
        <select name="filtro[{{$data->informe_codigo}}][]" id="{{$name}}_multiple"
                class="form-control multiple" multiple="multiple">
            @if($opciones->count() > 1)
                @forelse($opciones as $opcion)
                    <option value="{{$opcion['key']}}">{{$opcion['value']}}</option>
                @empty

                @endforelse
            @else
            {!! nl2br($opciones->get('opcion')) !!}
            @endif
        </select>
    </div>
</div>
