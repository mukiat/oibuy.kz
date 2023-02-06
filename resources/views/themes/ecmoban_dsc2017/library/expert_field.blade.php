

@if($ad_child)

<div class="master-channel" id="master">
	<div class="ftit"><h3>{{ $lang['darren_area'] }}</h3></div>
	<div class="master-con">
	
@foreach($ad_child as $ad)

		<div class="m-c-item m-c-i-{{ $loop->iteration }}"
@if($ad['ad_bg_code'])
 style="background:url({{ $ad['ad_bg_code'] }}) center center no-repeat;"
@endif
>
			<div class="m-c-main">
				<div class="title">
					<h3>{{ $ad['b_title'] }}</h3>
					<span>{{ $ad['s_title'] }}</span>
				</div>
				<a href="{{ $ad['ad_link'] }}" class="m-c-btn" target="_blank">{{ $lang['go_to_see'] }}</a>
			</div>
			<div class="img"><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" style="max-width:{{ $ad['ad_width'] }}px; max-height:{{ $ad['ad_height'] }}px;"></a></div>
		</div>
	
@endforeach
		
	</div>
</div>

@endif
