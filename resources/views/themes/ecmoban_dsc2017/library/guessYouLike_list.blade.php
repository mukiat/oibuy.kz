

@if($type == 'topic')

    
@foreach($goods_list as $key => $goods)

    <li>
        <div class="img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_img'] }}"></a></div>
        <div class="info">
            <div class="name"><a href="{{ $goods['url'] }}">{{ $goods['goods_name'] }}</a></div>
                <div class="price">
                    
@if($goods['promote_price'] != '')

                        {{ $goods['promote_price'] }}
                    
@else

                        {{ $goods['shop_price'] }}
                    
@endif

                </div>
            <div class="btn_hover"><a href="{{ $goods['url'] }}">{{ $lang['button_buy'] }}</a></div>
        </div>
    </li>
    
@endforeach


@elseif ($type == 'seller')

    
@foreach($goods_list as $key => $goods)

    <li>
            <div class="img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_img'] }}"></a></div>
            <div class="info">
                <div class="name"><a href="{{ $goods['url'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                <div class="price">
                        
@if($goods['promote_price'] != '')

                            {{ $goods['promote_price'] }}
                        
@else

                            {{ $goods['shop_price'] }}
                        
@endif

                </div>
                <div class="btn_hover"><a href="{{ $goods['url'] }}">{{ $lang['button_buy'] }}</a></div>
            </div>
        </li>
    
@endforeach


@elseif ($type == 'home_rank')

    
@if($goods_list)

    <div class="com-ul" >
        
@foreach($goods_list as $key => $goods)

        
@if($loop->iteration < 4)

        <div class="com-li">
            <a href="{{ $goods['url'] }}" target="_blank">
                <div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
                <div class="p-name">{{ $goods['goods_name'] }}</div>
                <div class="p-price">
                    
@if($activitytype == 'exchange')

                    {{ $goods['exchange_integral'] }}
                    
@else

                    
@if($goods['promote_price'] != '')

                    {{ $goods['promote_price'] }}
                    
@else

                    {{ $goods['shop_price'] }}
                    
@endif

                    
@endif

                </div>
                <i class="ph-icon ph-icon{{ $loop->iteration }}">{{ $loop->iteration }}</i>
            </a>
        </div>
        
@endif

        
@endforeach

    </div>
    <div class="com-ul">
        
@foreach($goods_list as $key => $goods)

        
@if($loop->iteration > 3)

        <div class="com-li">
            <a href="{{ $goods['url'] }}" target="_blank">
                <div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
                <div class="p-name">{{ $goods['goods_name'] }}</div>
                <div class="p-price">
                    
@if($activitytype == 'exchange')

                    {{ $goods['exchange_integral'] }}
                    
@else

                    
@if($goods['promote_price'] != '')

                    {{ $goods['promote_price'] }}
                    
@else

                    {{ $goods['shop_price'] }}
                    
@endif

                    
@endif

                </div>
                <i class="ph-icon ph-icon{{ $loop->iteration }}">{{ $loop->iteration }}</i>
            </a>
        </div>
        
@endif

        
@endforeach

    </div>
    
@else

    <div class="com-ul">
        <div class="com-li">
            <a href="#" target="_blank">
                <div class="p-img"><img src="{{ skin('data/gallery_album/visualDefault/zhanwei.png') }}"></div>
                <div class="p-name">【享12期免息】Apple iPhone X 64GB 深空灰 移动联通电信4G手机</div>
                <div class="p-price"><em>¥</em>8388.00</div>
                <i class="ph-icon ph-icon2">2</i>
            </a>
        </div>
        <div class="com-li">
            <a href="#" target="_blank">
                <div class="p-img"><img src="{{ skin('data/gallery_album/visualDefault/zhanwei.png') }}"></div>
                <div class="p-name">【享12期免息】Apple iPhone X 64GB 深空灰 移动联通电信4G手机</div>
                <div class="p-price"><em>¥</em>8388.00</div>
                <i class="ph-icon ph-icon2">2</i>
            </a>
        </div>
        <div class="com-li">
            <a href="#" target="_blank">
                <div class="p-img"><img src="{{ skin('data/gallery_album/visualDefault/zhanwei.png') }}"></div>
                <div class="p-name">【享12期免息】Apple iPhone X 64GB 深空灰 移动联通电信4G手机</div>
                <div class="p-price"><em>¥</em>8388.00</div>
                <i class="ph-icon ph-icon3">3</i>
            </a>
        </div>
    </div>
    <div class="com-ul">
        <div class="com-li">
            <a href="#" target="_blank">
                <div class="p-img"><img src="{{ skin('data/gallery_album/visualDefault/zhanwei.png') }}"></div>
                <div class="p-name">【享12期免息】Apple iPhone X 64GB 深空灰 移动联通电信4G手机</div>
                <div class="p-price"><em>¥</em>8388.00</div>
                <i class="ph-icon ph-icon4">4</i>
            </a>
        </div>
        <div class="com-li">
            <a href="#" target="_blank">
                <div class="p-img"><img src="{{ skin('data/gallery_album/visualDefault/zhanwei.png') }}"></div>
                <div class="p-name">【享12期免息】Apple iPhone X 64GB 深空灰 移动联通电信4G手机</div>
                <div class="p-price"><em>¥</em>8388.00</div>
                <i class="ph-icon ph-icon5">5</i>
            </a>
        </div>
        <div class="com-li">
            <a href="#" target="_blank">
                <div class="p-img"><img src="{{ skin('data/gallery_album/visualDefault/zhanwei.png') }}"></div>
                <div class="p-name">【享12期免息】Apple iPhone X 64GB 深空灰 移动联通电信4G手机</div>
                <div class="p-price"><em>¥</em>8388.00</div>
                <i class="ph-icon ph-icon6">6</i>
            </a>
        </div>
    </div>
    
@endif


@else

    
@foreach($goods_list as $key => $goods)

    <li class="opacity_img">
        <a href="{{ $goods['url'] }}" target="_blank">
            <div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
            <div class="p-name" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</div>
            <div class="p-price">
                <div class="shop-price">
                    
@if($goods['promote_price'] != '')

                    {{ $goods['promote_price'] }}
                    
@else

                    {{ $goods['shop_price'] }}
                    
@endif

                </div>
                <div class="original-price">{{ $goods['market_price'] }}</div>
            </div>
        </a>
    </li>
    
@endforeach


@endif

