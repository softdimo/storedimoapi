<table id="tabla" 
        class="table table-striped table-bordered w-100 mb-0 display compact" 
        width="100%">
    <thead>
        <tr class="header-table">
            @foreach($titulos as $titulo)
                <th>{{$titulo}}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        {!! $filas !!}
    </tbody>
</table>