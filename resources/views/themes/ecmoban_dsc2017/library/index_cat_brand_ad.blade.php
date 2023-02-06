
<div class="cate-brand">
    
@foreach($brands_ad['brands'] as $brand)

        <div class="img"><a href="{{ $brand['url'] }}" target="_blank" title="{{ $brand['brand_name'] }}"><img src="{{ $brand['brand_logo'] }}" /></a></div>
    
@endforeach

</div>
<div class="cate-promotion">
    
@foreach($brands_ad['ad_position'] as $pkey => $posti)

    <a href="{{ $posti['ad_link'] }}" target="_blank"><img width="{{ $posti['ad_width'] }}" height="{{ $posti['ad_height'] }}" src="{{ $posti['ad_code'] }}" /></a>
    
@endforeach

</div>
