
@if($ad_child)

<div class="banner brand-banner" style="background:{{ $ad['link_color'] }};">
	<div class="w w1200 relative">
		
@foreach($ad_child as $ad)

		<div class="brand-banner-wp">
			<a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}"></a>
		</div>
		
@endforeach

	</div>
</div>

@endif
