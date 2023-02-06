

@if($ad_child)


@if($floor_style_tpl == 1)

	
@foreach($ad_child as $ad)

		<a href="{{ $ad['ad_link'] }}" 
@if($loop->iteration == 1)
class="mr10"
@endif
 target="_blank"><img src="{{ $ad['ad_code'] }}" width="232" height="280"></a>
    
@endforeach


@elseif ($floor_style_tpl == 2)

	
@foreach($ad_child as $ad)

		<a href="{{ $ad['ad_link'] }}" 
@if($loop->iteration == 1)
class="mb10"
@endif
 target="_blank"><img src="{{ $ad['ad_code'] }}" width="474" height="280"></a>
    
@endforeach


@elseif ($floor_style_tpl == 3)

	
@foreach($ad_child as $ad)

		<a href="{{ $ad['ad_link'] }}" 
@if($loop->iteration == 1)
class="mr10"
@endif
 target="_blank"><img src="{{ $ad['ad_code'] }}" width="232" height="280"></a>
    
@endforeach


@else

	
@foreach($ad_child as $ad)

		<a href="{{ $ad['ad_link'] }}" 
@if($loop->iteration == 1)
class="mb10"
@endif
 target="_blank"><img src="{{ $ad['ad_code'] }}"  width="232" height="280"></a>
    
@endforeach


@endif
	

@endif
