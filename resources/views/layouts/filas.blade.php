<tr>
    @for ($i = 0; $i <count($indices); $i++)
    @php
        $prop = $indices[$i];
        $sort = $is_date->$prop != null ? "data-sort='{$is_date->$prop}'": ''
    @endphp
        <td {{$sort}}>{!! $fila->$prop !!}</td>
    @endfor
</tr>