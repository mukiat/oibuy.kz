
@if($ad_child)

<div class="street-banner-wp">
    
@foreach($ad_child as $key => $ad)

    <a href="{{ $ad['ad_link'] }}" style=" background:url({{ $ad['ad_code'] }}) center center no-repeat; width:100%; height:{{ $ad['ad_height'] }}px;" target="_blank"></a>
    
@endforeach

</div>

@endif
