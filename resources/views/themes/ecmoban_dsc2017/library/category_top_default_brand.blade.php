

@if($ad_child)


@foreach($ad_child as $ad)

	<li 
@if($loop->iteration > 3)
class="reverse"
@endif
>
		<div class="cover"><a href="{{ $ad['ad_link'] }}"><img src="{{ $ad['ad_code'] }}" alt=""></a></div>
		<div class="info">
			<div class="info-logo"><a href="{{ $ad['ad_link'] }}"><img src="{{ $ad['ad_bg_code'] }}" alt=""></a></div>
			<div class="info-name"><a href="{{ $ad['ad_link'] }}">{{ $ad['b_title'] }}</a></div>
			<div class="info-intro">{{ $ad['s_title'] }}</div>
		</div>
	</li>

@endforeach


@endif

				