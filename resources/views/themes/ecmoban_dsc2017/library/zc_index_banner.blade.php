

@if($ad_child)

<ul>
	
@foreach($ad_child as $ad)

	<li><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}"/></a></li>
	
@endforeach

</ul>

@endif
