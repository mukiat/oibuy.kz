

@if($temp == 'floor_temp')

<ul class="p-list">

@foreach($goods_list as $goods)

<li class="opacity_img">
    <div class="product">
        <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="140" height="140"></a></div>
        <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
        <div class="p-price">
            <span class="shop-price">
                
@if($goods['promote_price'] != '')

                    {{ $goods['promote_price'] }}
                
@else

                    {{ $goods['shop_price'] }}
                
@endif

            </span>
            <span class="original-price">{{ $goods['market_price'] }}</span>
        </div>
    </div>
</li>

@endforeach

</ul>

@elseif ($temp == 'floor_temp_expand')


@foreach($goods_list as $goods)

<div class="p-list-item">
	<div class="product">
        <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="140" height="140"></a></div>
        <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
        <div class="p-price">
            <span class="shop-price">
                
@if($goods['promote_price'] != '')

                    {{ $goods['promote_price'] }}
                
@else

                    {{ $goods['shop_price'] }}
                
@endif

            </span>
            <span class="original-price">{{ $goods['market_price'] }}</span>
        </div>
    </div>
</div>

@endforeach


@endif

