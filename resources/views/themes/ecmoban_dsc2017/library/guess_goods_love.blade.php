
<div class="ftit"><h3>{{ $lang['guess_love'] }}</h3></div>
<ul>
    
@foreach($guess_goods as $goods)

    
@if($loop->iteration < 6)

    <li>
        <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="134" height="134"></a></div>
        <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['short_name'] }}">{{ $goods['short_name'] }}</a></div>
        <div class="p-price">{{ $goods['shop_price'] }}</div>
    </li>
    
@endif

    
@endforeach

</ul>