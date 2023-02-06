
@if($ad_child)


@foreach($ad_child as $ad)

<div class="item"><i style="background:url({{ $ad['ad_code'] }}) center center no-repeat;width:{{ $ad['ad_width'] }};height:{{ $ad['ad_height'] }};display:display;float:left"></i><span>{{ $cat_name }}</span></div>

@endforeach


@endif
