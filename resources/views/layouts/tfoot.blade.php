@if($numeros)
<tfoot>
    <tr>
        @foreach($totales as $total)
        @if(!is_null($total))
        <td>{{number_format($total)}}</td>
        @else
        <td></td>
        @endif
        @endforeach
    </tr>
</tfoot>
@endif
