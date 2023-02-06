
<div class="ss-warp">

@forelse($store_shop_list as $key => $shop)

    <div class="ss-item">
        <div class="ss-info">
            <div class="ss-i-info">
                <div class="ss-i-top">
                    <div class="logo"><a href="{{ $shop['shop_url'] }}"><img src="{{ $shop['logo_thumb'] }}"></a></div>
                    <div class="r-info">
                        <div class="ss-tit">{{ $shop['shopName'] }}

							<a  id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $shop['ru_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="p-kefu
@if($shop['ru_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>


						</div>
                        <div class="ss-desc">
                            <p>{{ $lang['Main_brand'] }}：

@foreach($shop['brand_list'] as $brand)


@if(!$loop->last)

                                    {{ $brand['brand_name'] }},

@else

                                    {{ $brand['brand_name'] }}

@endif


@endforeach

                            </p>
                            <p>{{ $lang['seat_of'] }}：{!! $shop['address'] !!}</p>
                        </div>
                        <div class="ss-btn">
                            <a onclick="get_collect_store(2, {{ $shop['ru_id'] }});" href="javascript:void(0);" class="btn">{{ $lang['follow_store'] }}</a>
                            <a href="{{ $shop['shop_url'] }}" class="btn">{{ $lang['enter_the_shop'] }}</a>
                        </div>
                    </div>
                </div>
                <div class="ss-i-bottom">
                    <div class="ss-contrast">
                        <div class="ssc-top">
                            <span class="col1">{{ $lang['Store_score'] }}</span>
                            <span class="col2">{{ $lang['goods'] }}</span>
                            <span class="col3">{{ $lang['service'] }}</span>
                            <span class="col4">{{ $lang['Deliver_goods'] }}</span>
                        </div>
                        <div class="ssc-content">
                            <span class="col1">&nbsp;</span>
                            <span class="col2">{{ $shop['merch_cmt']['cmt']['commentRank']['zconments']['score']}}</span>
                            <span class="col3">{{ $shop['merch_cmt']['cmt']['commentServer']['zconments']['score']}}</span>
                            <span class="col4">{{ $shop['merch_cmt']['cmt']['commentDelivery']['zconments']['score']}}</span>
                        </div>
                        <div class="ssc-bottom">
                            <span class="col1">{{ $lang['peer_comparison'] }}</span>
                            <span class="col2">{{ $shop['merch_cmt']['cmt']['commentRank']['zconments']['goodReview']}}%<i class="iconfont icon-arrow-down"></i></span>
                            <span class="col3">{{ $shop['merch_cmt']['cmt']['commentServer']['zconments']['goodReview']}}%<i class="iconfont icon-arrow-down"></i></span>
                            <span class="col4">{{ $shop['merch_cmt']['cmt']['commentDelivery']['zconments']['goodReview']}}%<i class="iconfont icon-arrow-up"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ss-i-goods" id="shop_goods_list_{{ $shop['ru_id'] }}">

@if($shop['goods_list'])

                <ul>

@foreach($shop['goods_list'] as $goods)


@if($loop->index < 4)

                    <li class="opacity_img">
                        <div class="p-img"><a href="{{ $goods['goods_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}"></a></div>
                        <div class="p-name"><a href="{{ $goods['goods_url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
                        <div class="p-price">

@if($goods['promote_price'] != '')

                                {{ $goods['promote_price'] }}

@else

                                {{ $goods['shop_price'] }}

@endif

                        </div>
                    </li>

@endif


@endforeach

                </ul>

@else

                <div class="no_records">
                    <i class="no_icon_two"></i>
                    <div class="no_info">
                        <h3>{{ $lang['information_null'] }}</h3>
                    </div>
                </div>

@endif

            </div>
        </div>
        <div class="s-more">
            <span class="sm-wrap"><a href="{{ $shop['store_shop_url'] }}" target="_blank">{{ $lang['More_options'] }}<i class="iconfont icon-down"></i></a></span>
        </div>
    </div>

@empty

    <div class="no_records">
        <i class="no_icon_two"></i>
        <div class="no_info">
            <h3>{{ $lang['information_null'] }}</h3>
        </div>
    </div>

@endforelse

</div>

@if($count > $size)

<div class="w1200 pagePtb">
    <div class="pages">
        {!! $pager !!}
    </div>
</div>

@endif


<script type="text/javascript">
    $(function(){

@if($store_shop_list)


@foreach($store_shop_list as $shop)

				shop_list({{ $shop['ru_id'] }});

@endforeach


@endif

	});
</script>
