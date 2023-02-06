
@if($store_list)

<div class="collection-list-warp clearfix">
    <ul class="shop-ul">

@foreach($store_list as $store)

        <li>
            <div class="shop-left">
                <div class="shop-logo"><a href="{{ $store['url'] }}"><img src="{{ $store['brand_thumb'] }}"></a></div>
                <div class="shop-name"><a href="{{ $store['url'] }}" class="name">{{ $store['store_name'] }}</a>

                <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $store['ru_id'] }}" class="p-kefu">
                    <i class="iconfont icon-kefu"></i>
                </a>
                </div>
                <div class="dsc-store-item">
                    <div class="s-score">
                        <span class="score-icon"><span class="score-icon-bg" style="width:{{ $store['merch_cmt']['cmt']['all_zconments']['allReview'] }}%;"></span></span>
                        <span>{{ $store['merch_cmt']['cmt']['all_zconments']['score'] }}</span>
                        <i class="iconfont icon-down"></i>
                    </div>
                    <div class="s-score-info">
                        <div class="s-cover"></div>
                        <div class="g-s-parts">
                            <div class="parts-tit">
                                <span class="col1">{{ $lang['Detailed_score'] }}</span>
                                <span class="col2">&nbsp;</span>
                                <span class="col3">{{ $lang['industry_compare'] }}</span>
                            </div>
                            <div class="parts-item parts-goods">
                                <span class="col1">{{ $lang['goods'] }}</span>
                                <span class="col2 ftx-02">{{ $store['merch_cmt']['cmt']['commentRank']['zconments']['score'] }}</span>
                                <span class="col3">{{ $store['merch_cmt']['cmt']['commentRank']['zconments']['goodReview'] }}%<i class="iconfont icon-arrow-up"></i></span>
                            </div>
                            <div class="parts-item parts-goods">
                                <span class="col1">{{ $lang['service'] }}</span>
                                <span class="col2 ftx-02">{{ $store['merch_cmt']['cmt']['commentServer']['zconments']['score'] }}</span>
                                <span class="col3">{{ $store['merch_cmt']['cmt']['commentServer']['zconments']['goodReview'] }}%<i class="iconfont icon-arrow-up"></i></span>
                            </div>
                            <div class="parts-item parts-goods">
                                <span class="col1">{{ $lang['prescription'] }}</span>
                                <span class="col2 ftx-01">{{ $store['merch_cmt']['cmt']['commentDelivery']['zconments']['score'] }}</span>
                                <span class="col3">{{ $store['merch_cmt']['cmt']['commentDelivery']['zconments']['goodReview'] }}%<i class="iconfont icon-arrow-down"></i></span>
                            </div>
                        </div>
                        <div class="tel">{{ $lang['telephone'] }}：
@if($store['kf_tel'])
{{ $store['kf_tel'] }}
@else
{{ $lang['not_set_telephone'] }}
@endif
</div>
                    </div>
                </div>
                <div class="shop-btn">
                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $store['ru_id'] }}"  class="sc-btn">{{ $lang['con_cus_service'] }}</a>
                    <a href="{{ $store['url'] }}" class="sc-btn">{{ $lang['enter_the_shop'] }}</a>
                </div>
                <div class="p-oper"><a href="javascript:void(0)" onclick="get_collect_store(1, {{ $store['ru_id'] }});">{{ $lang['Cancel_attention'] }}</a></div>
            </div>

@if($store['new_goods'])

            <div class="shop-right">
                <div class="shop-bd">
                    <ul>

@foreach($store['new_goods'] as $goods)

                        <li>
                            <div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['goods_thumb'] }}"></a></div>
                            <div class="p-price">

@if($goods['promote_price'] != "")

                            {{ $goods['promote_price'] }}

@else

                            {{ $goods['shop_price'] }}

@endif

                            </div>
                        </li>

@endforeach

                    </ul>
                </div>

@if($store['new_goods_count'] > 3)

                <div class="shop-cont">
                    <span class="pageState"></span>
                    <a href="javascript:void(0);" class="prev"><i class="iconfont icon-left"></i></a>
                    <a href="javascript:void(0);" class="next"><i class="iconfont icon-right"></i></a>
                </div>

@endif

            </div>

@elseif ($store['hot_goods'])

			<div class="shop-right">
                <div class="shop-bd">
                    <ul>

@foreach($store['hot_goods'] as $goods)

                        <li>
                            <div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['goods_thumb'] }}"></a></div>
                            <div class="p-price">

@if($goods['promote_price'] != "")

                            {{ $goods['promote_price'] }}

@else

                            {{ $goods['shop_price'] }}

@endif

                            </div>
                        </li>

@endforeach

                    </ul>
                </div>

@if($store['new_goods_count'] > 3)

                <div class="shop-cont">
                    <span class="pageState"></span>
                    <a href="javascript:void(0);" class="prev"><i class="iconfont icon-left"></i></a>
                    <a href="javascript:void(0);" class="next"><i class="iconfont icon-right"></i></a>
                </div>

@endif

            </div>

@elseif ($store['common_goods'])

			<div class="shop-right">
                <div class="shop-bd">
                    <ul>

@foreach($store['common_goods'] as $goods)

                        <li>
                            <div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['goods_thumb'] }}"></a></div>
                            <div class="p-price">

@if($goods['promote_price'] != "")

                            {{ $goods['promote_price'] }}

@else

                            {{ $goods['shop_price'] }}

@endif

                            </div>
                        </li>

@endforeach

                    </ul>
                </div>

@if($store['new_goods_count'] > 3)

                <div class="shop-cont">
                    <span class="pageState"></span>
                    <a href="javascript:void(0);" class="prev"><i class="iconfont icon-left"></i></a>
                    <a href="javascript:void(0);" class="next"><i class="iconfont icon-right"></i></a>
                </div>

@endif

            </div>

@else

            <div class="no_records no_records_min">
                <i class="no_icon no_icon_two"></i>
                <div class="no_info"><h3>{{ $lang['no_store_goods'] }}</h3></div>
            </div>

@endif

        </li>

@endforeach

    </ul>
</div>

@else

<div class="no_records">
	<i class="no_icon"></i>
    <div class="no_info"><h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3></div>
</div>

@endif

