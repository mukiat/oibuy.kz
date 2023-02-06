


@if($ad_child)

<ul>

@if($floor_style_tpl == 1)

	
@foreach($ad_child as $ad)

    <li><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="474" height="280"></a></li>
    
@endforeach


@elseif ($floor_style_tpl == 3)

	
@foreach($ad_child as $ad)

    <li><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="474" height="280"></a></li>
    
@endforeach


@else

	
@foreach($ad_child as $ad)

    <li><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="232" height="570"></a></li>
    
@endforeach


@endif

</ul>

@endif
