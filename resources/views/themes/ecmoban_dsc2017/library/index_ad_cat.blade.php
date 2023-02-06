
@if($ad_child)

<div class="need-channel clearfix" id="h-need">

@foreach($ad_child as $ad)


@if($loop->iteration < 6)

<div class="channel-column" style="background:url({{ $ad['ad_bg_code'] }}) no-repeat;">
	<div class="column-title">
		<h3>{{ $ad['b_title'] }}</h3>
		<p>{{ $ad['s_title'] }}</p>
	</div>
	<div class="column-img"><img src="{{ $ad['ad_code'] }}"></div>
	<a href="{{ $ad['ad_link'] }}" target="_blank" class="column-btn">{{ $lang['go_see'] }}</a>
</div>

@endif


@endforeach

</div>

@endif

<input type="hidden" value="
@if($ad_child)
1
@else
0
@endif
" name="index_ad_cat"/>