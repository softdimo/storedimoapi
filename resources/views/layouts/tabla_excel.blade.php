<table>
    <tbody>
    <tr>
        @foreach($titulos as $titulo)
            <th>{{$titulo}}</th>
        @endforeach
    </tr>
    {!! $filas !!}
    </tbody>
</table>