

@if($ad_child)

<ul>

@foreach($ad_child as $ad)

  <li style="background:{{ $ad['link_color'] }};"><div class="banner-width"><a style="background: url({{ $ad['ad_code'] }}) no-repeat;" href="{{ $ad['ad_link'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" ></a></div></li>

@endforeach

</ul>

@endif
