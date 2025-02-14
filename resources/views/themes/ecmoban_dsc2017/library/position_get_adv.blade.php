

@foreach($ad_posti as $key => $posti)


@if($posti['posti_type'] == 'floor_banner')

	<div class="full-banner">
		<a href="{{ $posti['ad_link'] }}" style="max-width:{{ $posti['ad_width'] }}px; max-height:{{ $posti['ad_height'] }}px;" target="_blank"><img src="{{ $posti['ad_code'] }}" width="{{ $posti['ad_width'] }}" height="{{ $posti['ad_height'] }}" /></a>
	</div>

@elseif ($posti['posti_type'] == 'top_banner')

    <div class="top-banner" 
@if($posti['link_color'])
style="background:{{ $posti['link_color'] }};"
@endif
>
        <div class="module w1200">
            <a href="{{ $posti['ad_link'] }}" target="_blank"><img width="{{ $posti['ad_width'] }}" height="{{ $posti['ad_height'] }}" src="{{ $posti['ad_code'] }}" /></a>
            <i class="iconfont icon-cha" ectype="close"></i>
        </div>
    </div>

@else

<a href="{{ $posti['ad_link'] }}" target="_blank"><img width="{{ $posti['ad_width'] }}" height="{{ $posti['ad_height'] }}" src="{{ $posti['ad_code'] }}" /></a>

@endif


@endforeach
