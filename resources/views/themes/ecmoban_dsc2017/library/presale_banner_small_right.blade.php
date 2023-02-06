

@if($ad_child)


@foreach($ad_child as $ad)

<div class="sign-right"><a href="{{ $ad['ad_link'] }}" target="_blank"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" /></a></div>

@endforeach


@endif
