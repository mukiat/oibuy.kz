

@if($ad_child)


@foreach($ad_child as $ad)

<a href="{{ $ad['ad_link'] }}"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}"></a>

@endforeach


@endif

