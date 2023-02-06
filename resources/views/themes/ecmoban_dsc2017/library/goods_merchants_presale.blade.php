
<div class="seller-pop">
    <div class="seller-logo">

@if($goods['user_id'])


@if($goods['shopinfo']['brand_thumb'])

    		<a href="{{ $goods['store_url'] }}" target="_blank"><img src="{{ $goods['shopinfo']['brand_thumb'] }}" height="45" /></a>

@else

            <a href="{{ $goods['goods_brand_url'] }}" target="_blank">{{ $goods['goods_brand'] }}</a>

@endif


@else


@if($goods['brand']['brand_logo'])

        	<a href="{{ $goods['brand']['url'] }}" target="_blank"><img src="{{ $goods['brand']['brand_logo'] }}" height="45" /></a>

@else

            <a href="{{ $goods['goods_brand_url'] }}" target="_blank">{{ $goods['goods_brand'] }}</a>

@endif


@endif

    </div>
    <div class="seller-infor"><a href="
@if($goods['store_url'])
{{ $goods['store_url'] }}
@else
index.php
@endif
" title="{{ $goods['rz_shop_name'] }}" target="_blank" class="name">{{ $goods['rz_shop_name'] }}</a><i class="icon arrow-show-more"></i></div>

@if($goods['user_id'])


@if($goods['grade_name'])

    <dl class="seller-zf seller_grade_name">
        <dt>{{ $lang['seller_Grade'] }}：</dt>
        <dd>
        	<span title="{{ $goods['grade_introduce'] }}">{{ $goods['grade_name'] }}</span><img src="{{ $goods['grade_img'] }}" width="20" title="{{ $goods['grade_introduce'] }}"/>
        </dd>
    </dl>

@endif

    <dl class="seller-zf">
        <dt>{{ $lang['store_total'] }}：</dt>
        <dd>
            <span class="heart-white">
                <span class="heart-red h10" style="width:{{ $merch_cmt['cmt']['all_zconments']['allReview'] }}%;" title="{{ $lang['comprehensive'] }}({{ $merch_cmt['cmt']['all_zconments']['allReview'] }}%)"> </span>
            </span>
            <em class="evaluate-grade"><strong title="10"><a target="_blank" href="#">{{ $merch_cmt['cmt']['all_zconments']['score'] }}</a></strong>{{ $lang['分'] }}</em>
        </dd>
    </dl>
    <div class="seller-pop-box">
        <dl class="pop-score-detail">
            <dt class="score-title">
                <span class="col1">{{ $lang['score_detail'] }}</span>
                <span class="col2">{{ $lang['industry_compare'] }}</span>
            </dt>
            <dd class="score-infor">
                <div class="score-part">
                    <span class="score-desc">{{ $lang['goods'] }}<em title="{{ $merch_cmt['cmt']['commentRank']['zconments']['score'] }}" class="number">{{ $merch_cmt['cmt']['commentRank']['zconments']['score'] }}</em></span>
                    <span class="score-change"><em class="score-percent">{{ $merch_cmt['cmt']['commentRank']['zconments']['up_down'] }}%</em></span>
                	<span class="score-trend"><i class="sprite-
@if($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                </div>
                <div class="score-part">
                    <span class="score-desc">{{ $lang['service'] }}<em title="{{ $merch_cmt['cmt']['commentServer']['zconments']['score'] }}" class="number">{{ $merch_cmt['cmt']['commentServer']['zconments']['score'] }}</em></span>
                    <span class="score-change"><em class="score-percent">{{ $merch_cmt['cmt']['commentServer']['zconments']['up_down'] }}%</em></span>
                	<span class="score-trend"><i class="sprite-
@if($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                </div>
                <div class="score-part">
                    <span class="score-desc">{{ $lang['prescription'] }}<em title="{{ $merch_cmt['cmt']['commentDelivery']['zconments']['score'] }}" class="number">{{ $merch_cmt['cmt']['commentDelivery']['zconments']['score'] }}</em></span>
                    <span class="score-change"><em class="score-percent">{{ $merch_cmt['cmt']['commentDelivery']['zconments']['up_down'] }}%</em></span>
                	<span class="score-trend"><i class="sprite-
@if($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 1)
up
@elseif ($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 2)
average
@else
down
@endif
"></i></span>
                </div>
            </dd>
        </dl>
    </div>

@endif

    <div class="seller-address">
        <div class="item">

@if($goods['user_id'])

            <span class="label">{{ $lang['company'] }}：</span>

@else

            <span class="label">{{ $lang['brand_gm'] }}：</span>

@endif

            <span class="text">

@if($goods['user_id'])


@if($shop_info['companyName'])

                    {{ $shop_info['companyName'] }}

@else

                	{{ $basic_info['shop_name'] }}

@endif


@else

                {{ $goods['brand']['brand_name'] }}

@endif

            </span>
        </div>
        <div class="item">
            <span class="label">{{ $lang['seat_of'] }}：</span>
            <span class="text">

@if($adress['province'] && $adress['city'])

                    {{ $adress['province'] }}&nbsp;{{ $adress['city'] }}

@else

                    {{ $basic_info['province'] }}&nbsp;{{ $basic_info['city'] }}

@endif

            </span>
        </div>
    </div>
    <div class="seller-kefu">

        <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  goods_id="{{ $goods['goods_id'] }}" class="seller-btn"><i class="icon i-kefu"></i>{{ $lang['online_service'] }}</a>

    </div>

@if($goods['user_id'])

    <div class="pop-shop-enter">
        <a href="{{ $goods['store_url'] }}" class="btn-gray btn-shop-access">{{ $lang['Go_to_store'] }}</a>
        <a href="javascript:;" onClick="get_collect_store(2, {{ $goods['user_id'] }})" class="btn-gray btn-shop-followey">{{ $lang['follow_store'] }}</a>
        <input type="hidden" name="error" value="{{ $goods['error'] }}" id="error"/>
    </div>

@endif

</div>
