

@if($ad_child)


@foreach($ad_child as $ad)

<a class="login-banner" href="{{ $ad['ad_link'] }}" target="_blank" style="background:url({{ $ad['ad_code'] }}) center center no-repeat;"></a>

@endforeach


@endif

