<!doctype html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="{{ $keywords }}" />
<meta name="Description" content="{{ $description }}" />

<title>{{ $page_title }}</title>



<link rel="shortcut icon" href="favicon.ico" />
@include('frontend::library/js_languages_new')

@if($store_id)
<link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}" />
@endif

<link rel="stylesheet" type="text/css" href="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.css') }}" />
<style type="text/css">
.ou{
    display: block !important;
}

/* 社区驿站 star*/
.community_post {
    position: relative;
    margin-top: 30px;
    padding: 20px 0 20px 22px;
    border: 1px dashed #d2d2d2;
}
.community_post p:first-child {
    font-size: 14px;
    margin-bottom: 10px;
}
.community_post p:nth-child(2) {
    color: #8c8c8c;
}
.community_post a {
    color: #438cded1;
    margin-left: 20px;
}
.community_post .icon {
    display: none;
}
.community_post.cs-selected .icon {
    position: absolute;
    right: -1px;
    bottom: -1px;
    width: 20px;
    height: 20px;
    background-position: 0 -50px;
    display: block;
}
/* 对话框 star*/
.post_dialog_box {
    position: absolute;
    top: 0;
    left: 0;
    display: none;
}
.post_dialog_content {
    position: fixed;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 1140px;
    top: 100px;
    left: 50%;
    bottom: 100px;
    transform: translateX(-50%);
    background-color: #fff;
    z-index: 2000;
}
.post_dialog_mask {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0px;
    left: 0px;
    opacity: 0.2;
    overflow: hidden;
    background-color: rgb(0, 0, 0);
    z-index: 1999;
}
.post_dialog_title {
    display: flex;
    justify-content: space-between;
    padding: 5px 0 5px 15px;
    font-size: 18px;
}
.post_dialog_title a {
    font-size: 28px;
    width: 30px;
    line-height: 1;
    text-align: center;
    color: #ccc;
}
.post_dialog_body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 30px 30px 0;
}
.post_nav_box {
    padding-bottom: 20px;
}
.place_order::before {
    content: '1';
}
.pick_up_code::before {
    content: '2';
}
.pick_up::before {
    content: '3';
}
.post_nav_box span::before {
    display: inline-block;
    width: 20px;
    height: 20px;
    text-align: center;
    color: #fff;
    margin-right: 15px;
    border-radius: 50%;
    background-color: #ff8f23;
}
.post_nav_box span::after {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-top: 2px solid #ccc;
    border-right: 2px solid #ccc;
    transform: rotate(45deg);
    margin: 0 15px;
}
.post_nav_box .pick_up::after {
    border: none;
}
.post_main {
    flex: 1;
    display: flex;
    flex-direction: column;
    border-top: 1px dashed #ccc;
    border-bottom: 1px dashed #ccc;
    padding-top: 5px;
}
.post_list_map {
    flex: 1;
    display: flex;
}
.post_list {
    position: relative;
    width: 300px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-right: none;
}
.post_scroll_box {
    position: absolute;
    top: -1px;
    left: -1px;
    right: 0;
    bottom: -1px;
    width: 100%;
    height: auto;
    overflow-y: auto;
}
.post_list_item {
    display: flex;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid transparent;
}
.post_list_item:hover {
    cursor: pointer;
}
.current_post {
    border-color: #FF5809;
}
.post_item_left {
    position: relative;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1px solid #ccc;
    box-sizing: border-box;
}
.post_item_current {
    border-color: #FF5809;
}
.post_item_current::before {
    content: '';
    display: inline-block;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #FF5809;
}
.post_item_right {
    flex: 1;
    margin-left: 10px;
}
.post_item_title {
    font-size: 14px;
    font-weight: 700;
}
.post_current_title {
    color: #FF5809;
}
.post_item_title span {
    float: right;
    color: #999;
}
.post_map {
    flex: 1;
}
.post_consignee_info {
    display: flex;
    align-items: center;
    padding: 15px 0;
}
.post_consignee_info div {
    font-size: 14px;
}
.post_consignee_info div::before {
    content: '*';
    display: inline;
    margin-right: 3px;
    color: #f00;
}
.post_consignee_info p {
    color: #999999;
}
.post_consignee_info span {
    display: inline-block;
    width: 120px;
    border: 1px solid #999;
    padding-left: 5px;
    color: #999;
    background-color: #eee;
}
.consignee_phone {
    margin: 0 30px 0 70px;
}
.post_dialog_footer {
    display: flex;
    padding: 20px 0 20px 80px;
}
.post_dialog_footer div:hover {
    cursor: pointer;
}
.post_dialog_footer .affirm_btn {
    padding: 6px 20px;
    margin-right: 20px;
    color: #fff;
    background-color: #f42424;
}
.post_dialog_footer .cancel_btn {
    box-sizing: border-box;
    padding: 6px 20px;
    color: #ff8f23;
    border: 1px solid #ff8f23;
}
/* 对话框 end*/

/* 地图窗体覆盖物文本标记样式 star */
.amap-marker-label{
  border: 0;
}
.marker_info_label {
  position: relative;
  padding: 5px;
}
.marker_info_label span {
  color: #000;
}
.marker_info_label div {
  position: absolute;
  top: 50%;
  left: -10px;
  width: 10px;
  height: 10px;
  transform: rotate(45deg) translateY(-50%);
  background-color: #fff;
}
/* 地图窗体覆盖物文本标记样式 end */

/* 社区驿站 end*/

</style>
</head>

<body class="bg-ligtGary">

    <div class="post_dialog_box">
        <div class="post_dialog_mask"></div>
        <div class="post_dialog_content">
            <div class="post_dialog_title">选择社区驿站代收点 <a href="javascript:;">×</a></div>
            <div class="post_dialog_body">
                <div class="post_nav_box">
                    <span class="place_order">选择社区驿站并下单</span>
                    <span class="pick_up_code">订单详情显示提货码</span>
                    <span class="pick_up">到社区驿站提货</span>
                </div>
                <div class="post_main">
                    <div class="post_list_map">
                        <div class="post_list">
                           <div class="post_scroll_box"></div>
                        </div>
                        <div class="post_map" id="container"></div>
                    </div>
                    <div class="post_consignee_info">
                        <div class="consignee">收货人姓名 : <span></span></div>
                        <div class="consignee_phone">手机 : <span></span></div>
                        <p>提示：根据收货地址显示收货人信息，如需修改可修改对应收货地址信息</p>
                    </div>
                </div>
            </div>
            <div class="post_dialog_footer">
                <div class="affirm_btn">确认</div>
                <div class="cancel_btn">取消</div>
            </div>
        </div>
    </div>



    @include('frontend::library/page_header_flow')

@if($step == "cart")

    <div class="container">
        <div class="w w1200">

@if($goods_list)

            <div class="cart-warp">
                <div class="cart-filter">
                    <div class="cart-stepflex">
                        <div class="cart-step-item curr">
                            <span>1.{{ $lang['my_cart'] }}</span>
                            <i class="iconfont icon-arrow-right-alt"></i>
                        </div>
                        <div class="cart-step-item">
                            <span>2.{{ $lang['fillin_order_info'] }}</span>
                            <i class="iconfont icon-arrow-right-alt"></i>
                        </div>
                        <div class="cart-step-item">
                            <span>3.{{ $lang['submit_order_success'] }}</span>
                        </div>
                    </div>
                    <div class="sp-area store-selector">
                        <div class="text-select" id="area_address" ectype="areaSelect">
                            <span class="txt">{{ $lang['Distribution_to'] }}</span>
                            <div class="selector">
								@include('frontend::library/goods_delivery_area')
                                <input type="hidden" value="{{ $flow_region }}" id="region_id" name="region_id">
                                <input type="hidden" value="" id="good_id" name="good_id">
                                <input type="hidden" value="{{ $user_id }}" id="user_id" name="user_id">
                                <input type="hidden" value="{{ $area_id }}" id="area_id" name="area_id">
                                <input type="hidden" value="{{ $province_row['region_id'] }}" id="province_id" name="province_region_id">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cart-table">
                    <div class="cart-head">
                        <div class="column c-checkbox">
                            <div class="cart-checkbox cart-all-checkbox" ectype="ckList">
                                <input type="checkbox" id="cart-selectall" class="ui-checkbox checkboxshopAll" ectype="ckAll" />
                                <label for="cart-selectall" class="ui-label-14">{{ $lang['checkd_all'] }}</label>
                            </div>
                        </div>
                        <div class="column c-goods">{{ $lang['goods'] }}</div>
                        <div class="column c-props"></div>
                        <div class="column c-price">{{ $lang['Unit_price'] }}</div>
                        <div class="column c-quantity">{{ $lang['number_to'] }}</div>
                        <div class="column c-sum">{{ $lang['ws_subtotal'] }}</div>
                        <div class="column c-action">{{ $lang['handle'] }}</div>
                    </div>
                    <div class="cart-tbody" ectype="cartTboy">

@foreach($goods_list as $goodsRu)

                        <div class="cart-item" ectype="shopItem">
                            <div class="shop">
                                <div class="cart-checkbox" ectype="ckList">
                                    <input type="checkbox" id="shopid_{{ $goodsRu['ru_id'] }}" name="checkShop" class="ui-checkbox CheckBoxShop" ectype="ckShopAll" data-ruid="{{ $goodsRu['ru_id'] }}" />
                                    <label for="shopid_{{ $goodsRu['ru_id'] }}" class="ui-label-14">&nbsp;</label>
                                </div>
                                <div class="shop-txt">

@if($goodsRu['ru_id'] == 0)

									<a href="javascript:;" class="shop-name self-shop-name">{{ $goodsRu['ru_name'] }}</a>

@else

                                    <a href="{{ $goodsRu['url'] }}" class="shop-name" target="_blank"><strong>{{ $goodsRu['ru_name'] }}</strong></a>

@endif


@if($cross_border_version)


@if($can_buy != '')


@foreach($can_buy as $cb)

                                    <span style="color:#F00;font-size:12px;margin-left:5px;" id="shop_{{ $goodsRu['ru_id'] }}">
@if($cb == $goodsRu['ru_id'])
{{ $lang['exceeding_the_ceiling'] }}
@endif
</span>

@endforeach


@else

                                    <span style="color:#F00;font-size:12px;margin-left:5px;" id="shop_{{ $goodsRu['ru_id'] }}"></span>

@endif


@endif


                                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goodsRu['ru_id'] }}" class="p-kefu
@if($goodsRu['ru_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>

                                </div>
                            </div>
                            <div class="item-list" ectype="itemList">

@foreach($goodsRu['new_list'] as $key => $activity)


@if($activity['act_id'] > 0)

                                <div class="item-single" ectype="promoItem" id="product_promo_{{ $goodsRu['ru_id'] }}_{{ $activity['act_id'] ?? 0 }}" data-actid="{{ $activity['act_id'] ?? 0 }}" data-ruid="{{ $goodsRu['ru_id'] }}">
                                    <div class="item-full">
                                    <div class="item-header" ectype="prpmoHeader">

@if($activity['act_type'] == 0)

                                        <div class="f-txt">
                                            <span class="full-icon">{{ $activity['act_type_txt'] }}<i class="i-arrow"></i></span>

@if($activity['act_type_ext'] == 0)


@if($activity['available'])

                                                <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-03" target="_blank">{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['activity'] }}， {{ $lang['receive_gifts'] }}
@if($activity['cart_favourable_gift_num'] > 0)
，{{ $lang['Already_receive'] }}{{ $activity['cart_favourable_gift_num'] }}{{ $lang['jian'] }}
@endif
&gt;</a>
                                                <a href="javascript:void(0);" data-actid="{{ $activity['act_id'] ?? 0 }}" data-ruid="{{ $goodsRu['ru_id'] }}" id="select-gift-{{ $activity['act_id'] ?? 0 }}" class="f-btn" ectype="tradeBtn">{{ $lang['receive_gift'] }}</a>


@else

                                                <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-03" target="_blank">{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}，{{ $lang['receive_gifts'] }} &gt; </a>
                                                <a href="javascript:void(0);" data-actid="{{ $activity['act_id'] ?? 0 }}" data-ruid="{{ $goodsRu['ru_id'] }}" id="select-gift-{{ $activity['act_id'] ?? 0 }}" class="f-btn" ectype="tradeBtn">{{ $lang['see_gift'] }}</a>
												<a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-05" target="_blank">&nbsp;
@if($activity['available'])
{{ $lang['look_around'] }}
@else
{{ $lang['gather_together'] }}
@endif
&nbsp;></a>

@endif


@else


@if($activity['available'])

                                                <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-03" target="_blank">{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }} ，{{ $lang['receive_gifts'] }}{{ $activity['act_type_ext'] }}{{ $lang['jian'] }} ，{{ $lang['receive_gifts_again'] }}{{ $activity['left_gift_num'] }}{{ $lang['jian'] }} &gt; </a>
                                                <a href="javascript:void(0);" data-actid="{{ $activity['act_id'] ?? 0 }}" data-ruid="{{ $goodsRu['ru_id'] }}" id="select-gift-{{ $activity['act_id'] ?? 0 }}" class="f-btn" ectype="tradeBtn">{{ $lang['receive_gift'] }}</a>

@else

                                                <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-03" target="_blank">{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}，{{ $lang['receive_gifts'] }}{{ $activity['act_type_ext'] }}{{ $lang['jian'] }} &gt; </a>
                                                <a href="javascript:void(0);" data-actid="{{ $activity['act_id'] ?? 0 }}" data-ruid="{{ $goodsRu['ru_id'] }}" id="select-gift-{{ $activity['act_id'] ?? 0 }}" class="f-btn" ectype="tradeBtn">{{ $lang['see_gift'] }}</a>
												<a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-05" target="_blank">&nbsp;
@if($activity['available'])
{{ $lang['look_around'] }}
@else
{{ $lang['gather_together'] }}
@endif
&nbsp;></a>

@endif


@endif

                                            <span class="full-txt">{{ $goods['act_name'] }}</span>
                                            <span class="f-price">
@if($activity['cart_fav_amount'])
{{ $activity['cart_fav_amount_format'] }}
@endif
</span>
                                        </div>

@elseif ($activity['act_type'] == 1)

                                        <div class="f-txt">
                                            <span class="full-icon"><i class="i-left"></i>{{ $activity['act_type_txt'] }}<i class="i-right"></i></span>

@if($activity['available'])

                                            {{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}（<span class="ftx-01">{{ $lang['been_reduced'] }}{{ $activity['act_type_ext_format'] }}{{ $lang['yuan'] }}</span>）

@else

                                            <span>{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['activity_notes_two'] }}</span>

@endif

                                            <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-05" target="_blank">
@if($activity['available'])
&gt;{{ $lang['look_around'] }}
@else
 &gt;{{ $lang['gather_together'] }}
@endif
&nbsp;</a>
                                            <span class="full-txt">{{ $goods['act_name'] }}</span>
                                            <span class="f-price"><div class="ml">
@if($activity['cart_fav_amount'])
{{ $activity['cart_fav_amount_format'] }}
@endif
</div>

@if($activity['available'])
<div  class="ftx-01 ml mt5">{{ $lang['been_reduced'] }}{{ $activity['act_type_ext_format'] }}</div>
@endif
</span>
                                        </div>

@elseif ($activity['act_type'] == 2)

                                        <div class="f-txt">
                                            <span class="full-icon"><i class="i-left"></i>{{ $activity['act_type_txt'] }}<i class="i-right"></i></span>

@if($activity['available'])

                                            {{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }} （{{ $lang['Already_enjoy'] }}{{ $activity['act_type_ext_format'] }}{{ $lang['percent_off_Discount'] }}）

@else

                                            {{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['zhekouxianzhi'] }}

@endif

                                            <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" target="_blank" class="ftx-05"> &gt;
@if($activity['available'])
{{ $lang['look_around'] }}
@else
{{ $lang['gather_together'] }}
@endif
</a>
                                            <span class="full-txt">{{ $goods['act_name'] }}</span>
                                            <span class="f-price"><div class="ml">
@if($activity['cart_fav_amount'])
{{ $activity['cart_fav_amount_format'] }}
@endif
</div>
@if($activity['available'])
<div  class="ftx-01 ml mt5">{{ $lang['been_reduced'] }}{{ $activity['goods_fav_amount_format'] }}</div>
@endif
</span>

                                        </div>

@endif

                                    </div>


@foreach($activity['act_goods_list'] as $goods)

                                    <div class="item-item" ectype="item" data-recid="{{ $goods['rec_id'] }}" data-goodsid="{{ $goods['goods_id'] }}">
                                        <div class="item-form">
                                            <div class="cell s-checkbox">
                                                <div class="cart-checkbox" ectype="ckList">
                                                    <input type="checkbox" id="checkItem_{{ $goods['rec_id'] }}" value="{{ $goods['rec_id'] }}" name="checkItem" class="ui-checkbox" ectype="ckGoods"
@if($goods['is_invalid'])
 disabled="disabled"
@endif
 data-ruid='{{ $goods['ru_id'] }}' data-actid="{{ $activity['act_id'] ?? 0 }}" />
                                                    <label for="checkItem_{{ $goods['rec_id'] }}" class="ui-label-14">&nbsp;</label>
                                                </div>
                                            </div>
                                            <div class="cell s-goods">
                                                <div class="goods-item">
                                                    <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70"></a></div>
                                                    <div class="item-msg">
                                                        <div class="p-name">
                                                            <a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">
                                                                @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                                                {{ $goods['goods_name'] }}
                                                            </a>
                                                        </div>

@if($goods['is_chain'])

                                                        <p class="mt5"><strong>{!! $lang['flow_store_notic'] !!}</strong></p>

@endif

                                                        <div class="gds-types a1">

@if($goods['stages_qishu'] != -1)

                                                            <em class="gds-type gds-type-stages">{{ $lang['by_stages'] }}</em>

@endif

    @if($goods['group_id'] && $goods['parent_id'] != 0)
        <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
    @endif

@if($goods['is_invalid'])
<span class="red">（{{ $lang['expired'] }}）</span>
@endif

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cell s-props">

@if($goods['goods_attr'])
{!! nl2br($goods['goods_attr']) !!}
@else
&nbsp;
@endif

                                            </div>
                                            <div class="cell s-price relative">
                                                <strong id="goods_price_{{ $goods['rec_id'] }}">{{ $goods['goods_price_format'] }}</strong>

@if($goods['favourable_list'])

                                                <div class="promotion-info" ectype="promInfo">
                                                    <a href="javascript:void(0);" class="sales-promotion" ectype="c-promotion">{{ $lang['modules_txt_promo'] }}<i class="iconfont icon-down"></i></a>
                                                    <div class="promotion-tips" ectype="promTips">
                                                        <ul>

@foreach($goods['favourable_list'] as $key => $fav)

                                                            <li>
                                                                <input type="radio" class="ui-radio" data-aid="{{ $fav['act_id'] }}" data-gid="{{ $goods['goods_id'] }}" data-rid="{{ $goods['rec_id'] }}" name="fav_{{ $goods['goods_id'] }}" id="{{ $goods['goods_id'] }}_{{ $fav['act_id'] }}" ectype="changeFav"
@if($fav['act_id'] == $activity['act_id'])
checked
@endif
>
                                                                <label for="{{ $goods['goods_id'] }}_{{ $fav['act_id'] }}" class="ui-radio-label">{{ $fav['act_name'] }}</label>
                                                            </li>

@endforeach

                                                        </ul>
                                                    </div>
                                                </div>

@endif

                                            </div>
                                            <div class="cell s-quantity">
                                                <div class="amount-warp">

@if($goods['goods_id'] > 0 && $goods['is_gift'] == 0 && $goods['parent_id'] == 0 && $goods['extension_code'] != 'package_buy')

                                                    <input type="text" value="{{ $goods['goods_number'] }}" name="goods_number[{{ $goods['rec_id'] }}]" id="goods_number_{{ $goods['rec_id'] }}" onchange="change_goods_number({{ $goods['rec_id'] }},this.value, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, '', {{ $activity['act_id'] ?? 0 }})" class="text buy-num" ectype="number" defaultnumber="{{ $goods['goods_number'] }}">
                                                    <div class="a-btn">
                                                        <a href="javascript:void(0);" onclick="changenum({{ $goods['rec_id'] }}, 1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, {{ $activity['act_id'] ?? 0 }})"  class="btn-add"><i class="iconfont icon-up"></i></a>
                                                        <a href="javascript:void(0);" onclick="changenum({{ $goods['rec_id'] }}, -1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, {{ $activity['act_id'] ?? 0 }})" class="btn-reduce
@if($goods['goods_number'] == 1)
btn-disabled
@endif
"><i class="iconfont icon-down"></i></a>
                                                    </div>

@else

                                                    <div class="tc" id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}">{{ $goods['goods_number'] }}</div>

@endif

                                                </div>

@if($goods['attr_number'])


@if($goods['attr_number'] >= $goods['goods_number'])

                                                    <div class="tc ftx-03">{{ $lang['Have_goods'] }}</div>

@else

                                                    <div class="tc ftx-01">{{ $lang['Stock_goods_null'] }}</div>

@endif


@else

                                                <div class="tc ftx-01">{{ $lang['No_goods'] }}</div>

@endif

                                            </div>
                                            <div class="cell s-sum">
                                                <strong id="goods_subtotal_{{ $goods['rec_id'] }}"><font id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}_subtotal">{{ $goods['formated_subtotal'] }}</font></strong>
                                                <div class="cuttip
@if($goods['dis_amount'] == 0 && !$rate_arr)
hide
@endif
">

@if($goods['dis_amount'] > 0)

                                                	<span class="tit">{{ $lang['youhui'] }}</span>
                                                	<span class="price" id="discount_amount_{{ $goods['rec_id'] }}">{{ $goods['discount_amount'] }}</span>

@endif


@if($cross_border_version)


@foreach($rate_arr as $ra)


@if($ra['rec_id'] == $goods['rec_id'])

                                                        <span id="rate_price_{{ $goods['rec_id'] }}" style="font-size:12px;color:#666;">{{ $lang['tax_fee'] }}:{{ $ra['format_rate_price'] }}</span>

@endif


@endforeach


@endif

                                                </div>
                                            </div>
                                            <div class="cell s-action">
                                                <a href="javascript:void(0);" id="remove_{{ $goods['rec_id'] }}" ectype="cartOperation" data-value='{"divId":"cart_remove","url":"flow.php?step=drop_goods&amp;id={{ $goods['rec_id'] }}","cancelUrl":"flow.php?step=drop_to_collect&amp;id={{ $goods['rec_id'] }}","recid":{{ $goods['rec_id'] }},"title":"{{ $lang['drop'] }}"}' class="cart-remove">{{ $lang['drop'] }}</a>
                                                <a href="javascript:void(0);" id="store_{{ $goods['rec_id'] }}" ectype="cartOperation" data-value='{"divId":"cart_collect","url":"flow.php?step=drop_to_collect&amp;id={{ $goods['rec_id'] }}","recid":{{ $goods['rec_id'] }},"title":"{{ $lang['follow'] }}"}' class="cart-store">{{ $lang['collect'] }}</a>
                                            </div>
                                        </div>

@if($loop->iteration > 1)

                                        <div class="item-line"></div>

@endif

                                    </div>

@endforeach



@foreach($activity['act_cart_gift'] as $goods)

                                    <div class="item-item zp" ectype="item" data-recid="{{ $goods['rec_id'] }}">
                                        <div class="item-form">
                                            <div class="cell s-checkbox">&nbsp;
                                                <div class="cart-checkbox hide {{ $goods['group_id'] }}" ectype="ckList">
                                                    <input type="checkbox" id="checkItem_{{ $goods['rec_id'] }}" value="{{ $goods['rec_id'] }}" name="checkItem" class="ui-checkbox" ectype="ckGoods"
@if($goods['is_invalid'])
 disabled="disabled"
@endif
 data-ruid='{{ $goods['ru_id'] }}' data-actid="{{ $activity['act_id'] ?? 0 }}" />
                                                    <label for="checkItem_{{ $goods['rec_id'] }}" class="ui-label-14">&nbsp;</label>
                                                </div>
                                            </div>
                                            <div class="cell s-goods">
                                                <div class="goods-item">
                                                    <div class="p-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                                                        <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70" /></a>

@else

                                                        <a href="javascript:void(0);" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70" /></a>

@endif

                                                    </div>
                                                    <div class="item-msg">

@if($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

                                                        <div class="p-name package-name">{{ $goods['goods_name'] }}<span class="red">（{{ $lang['remark_package'] }}）</span></div>
                                                        <div class="package_goods" id="suit_{{ $goods['goods_id'] }}">
                                                            <div class="title">{{ $lang['contain_goods'] }}：</div>
                                                            <ul>

@foreach($goods['package_goods_list'] as $package_goods_list)

                                                                <li>
                                                                	<div class="goodsName"><a href="goods.php?id={{ $package_goods_list['goods_id'] }}" target="_blank">{{ $package_goods_list['goods_name'] }}</a></div>
                                                                    <div class="goodsNumber">x{{ $package_goods_list['goods_number'] }}</div>
                                                                </li>

@endforeach

                                                            </ul>
                                                        </div>

@else

                                                        <a href="{{ $goods['url'] }}" target="_blank">
                                                            @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                                            {{ $goods['goods_name'] }}
                                                        </a>

                                                            @if($goods['is_chain'])
                                                            <p class="mt5"><strong>{!! $lang['flow_store_notic'] !!}</strong></p>
                                                            @endif


@endif


                                                        <div class="gds-types">
                                                            <em class="gds-type gds-type-stages">{{ $lang['largess'] }}</em>

                                                            @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                                <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                            @endif

                                                            @if($goods['is_invalid'])
                                                                <span class="red">（{{ $lang['expired'] }}）</span>
                                                            @endif

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cell s-props">

@if($goods['goods_attr'])
{!! nl2br($goods['goods_attr']) !!}
@else
&nbsp;
@endif

                                            </div>
                                            <div class="cell s-price">
                                                <strong id="goods_price_{{ $goods['rec_id'] }}">{{ $goods['goods_price_format'] }}</strong>
                                            </div>
                                            <div class="cell s-quantity">
                                                <div class="amount-warp">

@if($goods['goods_id'] > 0 && $goods['is_gift'] == 0 && $goods['parent_id'] == 0 && $goods['extension_code'] != 'package_buy')

                                                    <input type="text" value="{{ $goods['goods_number'] }}" name="goods_number[{{ $goods['rec_id'] }}]" id="goods_number_{{ $goods['rec_id'] }}" onchange="change_goods_number({{ $goods['rec_id'] }},this.value, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, '', {{ $activity['act_id'] ?? 0 }})" class="text buy-num" ectype="number" defaultnumber="{{ $goods['goods_number'] }}">
                                                    <div class="a-btn">
                                                        <a href="javascript:void(0);" onclick="changenum({{ $goods['rec_id'] }}, 1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, {{ $activity['act_id'] ?? 0 }})"  class="btn-add"><i class="iconfont icon-up"></i></a>
                                                        <a href="javascript:void(0);" onclick="changenum({{ $goods['rec_id'] }}, -1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, {{ $activity['act_id'] ?? 0 }})" class="btn-reduce
@if($goods['goods_number'] == 1)
btn-disabled
@endif
"><i class="iconfont icon-down"></i></a>
                                                    </div>

@else

                                                    <input type="text" value="{{ $goods['goods_number'] }}" class="noeidx" ectype="number" readonly id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}" />

@endif

                                                </div>
                                            </div>
                                            <div class="cell s-sum">
                                                <strong id="goods_subtotal_{{ $goods['rec_id'] }}"><font id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}_subtotal">{{ $goods['formated_subtotal'] }}</font></strong>
                                                <div class="cuttip
@if($goods['dis_amount'] == 0 && !$rate_arr)
hide
@endif
">

@if($goods['dis_amount'] > 0)

                                                	<span class="tit">{{ $lang['youhui'] }}</span>
                                                    <span class="price" id="discount_amount_{{ $goods['rec_id'] }}">{{ $goods['discount_amount'] }}</span>

@endif


@if($cross_border_version)


@foreach($rate_arr as $ra)


@if($ra['rec_id'] == $goods['rec_id'])

                                                        <span id="rate_price_{{ $goods['rec_id'] }}" style="font-size:12px;color:#666;">{{ $lang['tax_fee'] }}:{{ $ra['format_rate_price'] }}</span>

@endif


@endforeach


@endif

                                                </div>
                                            </div>
                                            <div class="cell s-action">
                                                <a href="javascript:void(0);" id="remove_{{ $goods['rec_id'] }}" ectype="cartOperation" data-value='{"divId":"cart_remove","url":"flow.php?step=drop_goods&amp;id={{ $goods['rec_id'] }}","cancelUrl":"flow.php?step=drop_to_collect&amp;id={{ $goods['rec_id'] }}","recid":{{ $goods['rec_id'] }},"title":"{{ $lang['drop'] }}"}' class="cart-remove">{{ $lang['drop'] }}</a>
                                                <a href="javascript:void(0);" id="store_{{ $goods['rec_id'] }}" ectype="cartOperation" data-value='{"divId":"cart_collect","url":"flow.php?step=drop_to_collect&amp;id={{ $goods['rec_id'] }}","recid":{{ $goods['rec_id'] }},"title":"{{ $lang['follow'] }}"}' class="cart-store">{{ $lang['collect'] }}</a>
                                            </div>
                                        </div>
                                    </div>

@endforeach



@if($activity['act_gift_list'])

                                    <div class="gift-box" ectype="giftBox" id="gift_box_list_{{ $activity['act_id'] ?? 0 }}_{{ $goods['ru_id'] }}" style="display:none;">
                                        @include('frontend::library/cart_gift_box')
                                    </div>

@endif

                                    </div>
                                </div>

@else

                                <div class="item-single">

@foreach($activity['act_goods_list'] as $goods)

                                	<div class="item-item
@if($goods['group_id'] && $goods['parent_id'] != 0)
 zp {{ $goods['group_id'] }}
@endif
" ectype="item" id="product_{{ $goods['goods_id'] }}" data-recid="{{ $goods['rec_id'] }}" data-goodsid="{{ $goods['goods_id'] }}">
                                        <div class="item-form">
                                            <div class="cell s-checkbox">
                                                <div class="cart-checkbox
@if($goods['group_id'] && $goods['parent_id'] != 0)
 hide
@endif
" ectype="ckList">
                                                    <input type="checkbox" id="checkItem_{{ $goods['rec_id'] }}" value="{{ $goods['rec_id'] }}" name="checkItem" class="ui-checkbox" ectype="ckGoods"
@if($goods['is_invalid'])
 disabled="disabled"
@endif
 data-ruid='{{ $goods['ru_id'] }}' data-actid="{{ $activity['act_id'] ?? 0 }}" />
                                                    <label for="checkItem_{{ $goods['rec_id'] }}" class="ui-label-14">&nbsp;</label>
                                                </div>
                                            </div>
                                            <div class="cell s-goods">
                                                <div class="goods-item">
                                                    <div class="p-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                                                        <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70" /></a>

@else

                                                        <a href="javascript:void(0);" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70" /></a>

@endif

                                                    </div>
                                                    <div class="item-msg">

@if($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

                                                        <div class="p-name package-name">{{ $goods['goods_name'] }}<span class="red">（{{ $lang['remark_package'] }}）</span></div>
                                                        <div id="suit_{{ $goods['goods_id'] }}" class="package_goods">
                                                        	<div class="title">{{ $lang['contain_goods'] }}：</div>
                                                            <ul>

@foreach($goods['package_goods_list'] as $package_goods_list)

                                                                <li>
                                                                	<div class="goodsName"><a href="goods.php?id={{ $package_goods_list['goods_id'] }}" target="_blank">{{ $package_goods_list['goods_name'] }}</a></div>
                                                                    <div class="goodsNumber">x{{ $package_goods_list['goods_number'] }}</div>
                                                                </li>

@endforeach

                                                            </ul>
                                                        </div>

@else

                                                        <a href="{{ $goods['url'] }}" target="_blank">
                                                            @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                                            {{ $goods['goods_name'] }}
                                                        </a>

@if($goods['is_chain'])

                                                            <p class="mt5"><strong>{!! $lang['flow_store_notic'] !!}</strong></p>

@endif


@endif

                                                        <div class="gds-types">

                                                            @if($goods['stages_qishu'] != -1)
                                                            <em class="gds-type gds-type-stages">{{ $lang['by_stages'] }}</em>
                                                            @endif

                                                            @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                                <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                            @endif

                                                            @if($goods['is_invalid'])
                                                            <span class="red">（{{ $lang['expired'] }}）</span>
                                                            @endif

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cell s-props">

@if($goods['goods_attr'])
{!! nl2br($goods['goods_attr']) !!}
@else
&nbsp;
@endif

                                            </div>
                                            <div class="cell s-price">
                                                <strong id="goods_price_{{ $goods['rec_id'] }}">{{ $goods['goods_price_format'] }}</strong>
                                            </div>
                                            <div class="cell s-quantity">
                                                <div class="amount-warp">

@if($goods['goods_id'] > 0 && $goods['is_gift'] == 0 && $goods['parent_id'] == 0)


@if($goods['extension_code'] == 'package_buy')

                                                    <input type="text" value="{{ $goods['goods_number'] }}" name="goods_number[{{ $goods['rec_id'] }}]" id="goods_number_{{ $goods['rec_id'] }}" onchange="addPackageToCartFlow({{ $goods['goods_id'] }}, {{ $goods['rec_id'] }}, this.value, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, 2)" class="text buy-num" ectype="number" defaultnumber="{{ $goods['goods_number'] }}">

@else

                                                    <input type="text" value="{{ $goods['goods_number'] }}" name="goods_number[{{ $goods['rec_id'] }}]" id="goods_number_{{ $goods['rec_id'] }}" onchange="change_goods_number({{ $goods['rec_id'] }}, this.value, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }})" class="text buy-num" ectype="number" defaultnumber="{{ $goods['goods_number'] }}">

@endif

                                                    <div class="a-btn">

@if($goods['extension_code'] == 'package_buy')

                                                        <a href="javascript:void(0);" onclick="addPackageToCartFlow({{ $goods['goods_id'] }}, {{ $goods['rec_id'] }}, 1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, 1)"  class="btn-add"><i class="iconfont icon-up"></i></a>
                                                        <a href="javascript:void(0);" onclick="addPackageToCartFlow({{ $goods['goods_id'] }}, {{ $goods['rec_id'] }}, -1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }}, 1)" class="btn-reduce
@if($goods['goods_number'] == 1)
btn-disabled
@endif
"><i class="iconfont icon-down"></i></a>

@else

                                                        <a href="javascript:void(0);" onclick="changenum({{ $goods['rec_id'] }}, 1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }})"  class="btn-add"><i class="iconfont icon-up"></i></a>
                                                        <a href="javascript:void(0);" onclick="changenum({{ $goods['rec_id'] }}, -1, {{ $goods['warehouse_id'] }}, {{ $goods['area_id'] }})" class="btn-reduce
@if($goods['goods_number'] == 1)
btn-disabled
@endif
"><i class="iconfont icon-down"></i></a>

@endif

                                                    </div>

@else

                                                    <div class="tc" id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}">{{ $goods['goods_number'] }}</div>

@endif

                                                </div>

@if($goods['attr_number'] || $goods['extension_code'] == 'package_buy')


@if($goods['attr_number'] >= $goods['goods_number'])

                                                    <div class="tc ftx-03">{{ $lang['Have_goods'] }}</div>

@else

                                                    <div class="tc ftx-01">{{ $lang['Stock_goods_null'] }}</div>

@endif


@else

                                                <div class="tc ftx-01">{{ $lang['No_goods'] }}</div>

@endif

                                            </div>
                                            <div class="cell s-sum">
                                                <strong id="goods_subtotal_{{ $goods['rec_id'] }}"><font id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}_subtotal">{{ $goods['formated_subtotal'] }}</font></strong>
                                                <div class="cuttip
@if($goods['dis_amount'] == 0 && !$rate_arr)
hide
@endif
">

@if($goods['dis_amount'] > 0)

                                                	<span class="tit">{{ $lang['youhui'] }}</span>
                                                    <span class="price" id="discount_amount_{{ $goods['rec_id'] }}">{{ $goods['discount_amount'] }}</span>

@endif


@if($cross_border_version)


@foreach($rate_arr as $ra)


@if($ra['rec_id'] == $goods['rec_id'])

                                                        <span id="rate_price_{{ $goods['rec_id'] }}" style="font-size:12px;color:#666;">{{ $lang['tax_fee'] }}:{{ $ra['format_rate_price'] }}</span>

@endif


@endforeach


@endif

                                                </div>
                                            </div>
                                            <div class="cell s-action">
                                                <a href="javascript:void(0);" id="remove_{{ $goods['rec_id'] }}" ectype="cartOperation" data-value='{"divId":"cart_remove","url":"flow.php?step=drop_goods&amp;id={{ $goods['rec_id'] }}","cancelUrl":"flow.php?step=drop_to_collect&amp;id={{ $goods['rec_id'] }}","recid":{{ $goods['rec_id'] }},"title":"{{ $lang['drop'] }}"}' class="cart-remove">{{ $lang['drop'] }}</a>
                                                <a href="javascript:void(0);" id="store_{{ $goods['rec_id'] }}" ectype="cartOperation" data-value='{"divId":"cart_collect","url":"flow.php?step=drop_to_collect&amp;id={{ $goods['rec_id'] }}","recid":{{ $goods['rec_id'] }},"title":"{{ $lang['follow'] }}"}' class="cart-store">{{ $lang['collect'] }}</a>
                                            </div>
                                        </div>
                                    </div>

@endforeach

                                </div>

@endif


@endforeach

                            </div>
                        </div>

@endforeach

                    </div>

@if($total['store_goods_number'] > 0)

                    <div>
                        <p class="tr mt10">
                            <span class="mr10">{{ $lang['store_order_notic'] }}</span>
                            <a class="btn mr0" ectype="store_order"><i class="iconfont icon-store-alt"></i> {{ $lang['private_store_order'] }}</a>
                        </p>
                    </div>

@endif

                    <div class="cart-tfoot">
                    	<div class="cart-toolbar" ectype="tfoot-toolbar">
                        	<div class="w w1200">
                            <div class="cart-checkbox cart-all-checkbox" ectype="ckList">
                                <input type="checkbox" id="toggle-checkboxes-down" class="ui-checkbox checkboxshopAll" ectype="ckAll" />
                                <label for="toggle-checkboxes-down" class="ui-label-14">{{ $lang['checkd_all'] }}</label>
                            </div>

@if($total['stages_qishu'])

                            <div class="select-stores-all">
                                <div class="cart-store-checkbox">
                                    <input type="checkbox" name="stages-checkboxs-all" id="stages-checkboxs-all" class="ui-checkbox stages-checkboxs-all" />
                                    <label for="stages-checkboxs-all">{{ $lang['by_stages'] }}</label>
                                </div>
                            </div>

@endif

                            <div class="operation">
                                <a href="javascript:void(0);" class="cart-remove-batch" data-dialog="remove_collect_dialog" data-divid="cart-remove-batch" data-removeurl="ajax_dialog.php?act=delete_cart_goods" data-collecturl="ajax_dialog.php?act=drop_to_collect" data-title="{{ $lang['drop'] }}">{{ $lang['remove_checked_goods'] }}</a>
                                <a href="javascript:void(0);" class="cart-follow-batch" data-dialog="remove_collect_dialog" data-divid="cart-collect-batch" data-removeurl="ajax_dialog.php?act=delete_cart_goods" data-collecturl="ajax_dialog.php?act=drop_to_collect" data-title="{{ $lang['follow'] }}">{{ $lang['Move_my_collection'] }}</a>
                            </div>
                            <div class="toolbar-right">
                                <div class="comm-right">
                                    <div class="btn-area">
                                        <form name="formCart" method="post" action="flow.php" onsubmit="return get_toCart();">
                                            <input name="goPay" type="submit" class="submit-btn"
@if($cross_border_version)
value="{{ $lang['go_pay'] }}"
@else
value="{{ $lang['submit_goods'] }}"
@endif

@if(!$user_id)
 id="go_pay" data-url="flow.php"
@endif
/>
                                            <input name="step" value="checkout" type="hidden" />
                                            <input name="store_seller" value="" type="hidden" id="cart_store_seller" />
                                            <input name="cart_value" id="cart_value" value="{{ $cart_value ?? 0 }}" type="hidden" />
											<input name="goods_ru" id="goods_ru" value="" type="hidden" />
                                            <input name="store_order" id="store_order" value="0" type="hidden" />
                                        @csrf </form>
                                    </div>
                                    <div class="price-sum" id="total_desc">
                                        <span class="txt">{{ $lang['title_count'] }}：</span>
                                        <span class="price sumPrice" id="cart_goods_amount" ectype="goods_total"></span>

@if($cross_border_version)

                                        <span class="txt" id="rate_desc"
@if($cart_total_rate == 0)
style="display:none;"
@endif
>{{ $lang['tax_fee'] }}：</span>
                                        <span class="price sumPrice"><em id="cart_total_rate"
@if($cart_total_rate == 0)
style="display:none;"
@endif
>{{ $cart_total_rate }}</em></span>

@endif

                                    </div>
                                    <div class="reduce-sum">
                                        <span class="txt">{{ $lang['Already_save'] }}：</span>
                                        <span class="price totalRePrice" id="save_total_amount" ectype="save_total"></span>
                                    </div>
                                    <div class="amount-sum">{{ $lang['choose'] }}<em class="cart_check_num" ectype="cartNum">0</em>{{ $lang['jian'] }}{{ $lang['goods'] }}</div>
                                </div>
                            </div>
                            </div>
                    	</div>
                    </div>
                </div>
            </div>

@else

            <div class="cart-empty">
                <div class="cart-message">
                    <div class="txt">{{ $lang['shopping_cart_notice'] }}</div>
                    <div class="info">
                        <a href="{{ url('/') }}" class="btn sc-redBg-btn">{{ $lang['go_around'] }}</a>

@if(!$user_id)
<a href="javascript:void(0);" id="go_pay" data-url="flow.php" class="login">{{ $lang['login_cart'] }}</a>
@endif

                    </div>
                </div>
            </div>

@endif

            <div class="p-panel-main c-history">
                <div class="ftit ftit-delr"><h3>{{ $lang['guess_love'] }}</h3></div>
                <div class="gl-list clearfix">
                    <ul class="clearfix">

@foreach($guess_goods as $goods)


@if($loop->index < 6)

                        <li class="opacity_img">
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="190" height="190"></a></div>
                            <div class="p-price">{{ $goods['shop_price'] }}</div>
                            <div class="p-name">
                                <a href="{{ $goods['url'] }}" title="{{ $goods['short_name'] }}" target="_blank">
                                    @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                    {{ $goods['short_name'] }}
                                </a>
                            </div>
                            <div class="p-num">{{ $lang['sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                        </li>

@endif


@endforeach

                    </ul>
                </div>
            </div>
        </div>
    </div>

@elseif ($step == "checkout")

    <div class="container">
        <form action="flow.php" method="post" name="doneTheForm" class="validator" id="theForm">
            <div class="w w1200">
        	<div class="checkout-warp">


@if($is_virtual == 0)


                <div class="ck-step" id="consignee_list">
                	<div class="ck-step-tit">
                        <h3>{{ $lang['consignee_info'] }}</h3>
                        <a href="user_address.php?act=address_list" class="extra-r" target="_blank">{{ $lang['flow_consignee'] }}</a>
                    </div>
                    <div class="ck-step-cont" id="consignee-addr">
                    @include('frontend::library/consignee_flow')
                    </div>
                </div>



@if($seller_store || $store_seller == 'store_seller')

                <div class="ck-step">
                    <div class="ck-step-tit">
                    	<h3>{{ $lang['offline_store_information'] }}</h3>
                    </div>
                    <div class="ck-step-cont">
                        <div class="store-warp" ectype="storeWarp">
                            <div class="item
@if($store_seller)
 hide
@endif
" ectype="seller_address">

@if(!$store_seller)

                            	<div class="item_label">{{ $lang['offline_store_information'] }}：</div>
                                <div class="item_value">
                                    <span class="sp ftxt-01">{{ $seller_store['stores_name'] }}</span>
                                    <span class="sp">({{ $seller_store['country'] }}&nbsp;{{ $seller_store['stores_address'] }})</span>
                                    <a href="javascript:void(0);" id="store_button" ectype="storeBtn">{{ $lang['edit'] }}</a>
                                </div>

@endif

                            </div>
                            <div class="item
@if(!$store_seller)
 hide
@endif
" ectype="get_seller_sotre">
                            	<div class="item_label">{{ $lang['offline_store_information'] }}：</div>
                                <div class="item_value" ectype="regionLinkage">
                                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="selProvinces">
                                        <dt>
                                            <span class="txt" ectype="txt">{{ $lang['please_select'] }}</span>
                                            <input type="hidden" value="{{ $consignee['province'] }}" name="province">
                                        </dt>
                                        <dd ectype="layer">
                                            <div class="option" data-value="0">{{ $lang['please_select'] }}</div>

@foreach($provinces as $province)

                                            <div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

                                        </dd>
                                    </dl>
                                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="selCities">
                                        <dt>
                                            <span class="txt" ectype="txt">{{ $lang['please_select'] }}</span>
                                            <input type="hidden" value="{{ $consignee['city'] }}" name="city">
                                        </dt>
                                        <dd ectype="layer">
                                            <div class="option" data-value="0">{{ $lang['please_select'] }}</div>

@foreach($cities as $city)

                                            <div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

                                        </dd>
                                    </dl>
                                    <dl class="mod-select mod-select-small" ectype="smartdropdown" data-store="1" id="selDistricts" style="display:none;">
                                        <dt>
                                            <span class="txt" ectype="txt">{{ $lang['please_select'] }}</span>
                                            <input type="hidden" value="{{ $consignee['district'] }}" name="district">
                                        </dt>
                                        <dd ectype="layer">
                                            <div class="option" data-value="0">{{ $lang['please_select'] }}</div>

@foreach($city as $district)

                                            <div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

                                        </dd>
                                    </dl>
                                    <dl class="mod-select mod-select-small" ectype="smartdropdown" id="seller_soter" style="display:none;">
                                    	<dt><span class="txt" ectype="txt">{{ $lang['select_store'] }}</span></dt>
                                        <dd ectype="layer"></dd>
                                    </dl>
                                    <span class="error" for="region"></span>
                                </div>
                            </div>
                            <div class="item">
                            	<div class="item_label">{{ $lang['time_shop'] }}：</div>
                            	<div class="item_value">
                                	<div class="text_time"><input name="take_time" id="time_shop" type="text" class="text" data-val="{{ $store_info['take_time'] }}" value="{{ $store_info['take_time'] }}" readonly></div>
                                </div>
                            </div>
                            <div class="item">
                            	<div class="item_label">{{ $lang['phone_con'] }}：</div>
                            	<div class="item_value"><input type="text" class="text text-2" data-val="{{ $store_info['store_mobile'] }}" value="{{ $store_info['store_mobile'] }}" name='store_mobile' placeholder="{{ $lang['store_take_mobile'] }}"></div>
                            </div>
                        </div>
                    </div>

                    @if($cross_border_version)
                    <div class="ck-step-cont @if($is_kj == 0) hide @endif" id="consignee-addr">
                        <input type="hidden" id="is_kj" value="{{ $is_kj }}" >
                        @if($is_kj == 1)
                            <div class="ck-step">
                                <div class="ck-step-tit">
                                    <h3>{{ $lang['certification'] }}</h3>
                                </div>
                                <div class="ck-step-cont">
                                    <span>{{ $lang['real_name'] }}：<input type="text" id="rel_name" name="rel_name" class="text" value="{{ $consignee['rel_name'] }}" placeholder="{{ $lang['input_name'] }}" /></span><span style="margin-left:30px;">{{ $lang['id_card'] }}：<input type="text" id="id_num" name="id_num" class="text" value="{{ $consignee['id_num'] }}" placeholder="{{ $lang['input_user_id'] }}" /></span>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
@endif


@endif


                <input name="is_virtual" type="hidden" value="{{ $is_virtual ?? 0 }}">



@if($is_exchange_goods != 1 || $total['real_goods_count'] != 0)

                <div class="ck-step checkout">
                	<div class="ck-step-tit">
                    	<h3>{{ $lang['payment_method'] }}</h3>
                    </div>
                    <div class="ck-step-cont">
                    	<div class="payment-warp">
                            <div class="payment-list" ectype="paymentType">

@if($is_presale_goods != 1 && !$store_id)


@foreach($payment_list as $payment)


@if($loop->count == 1)

									<div class="p-radio-item payment-item item-selected" data-value='{"type":"{{ $payment['pay_code'] }}","payid":"{{ $payment['pay_id'] }}","allow":"{{ $allow_use_surplus }}"}'>
										<span>
                                            <input type="radio" checked isCod="{{ $payment['is_cod'] }}" name="payment" class="ui-radio" value="{{ $payment['pay_id'] }}" />
                                            <input type="radio" checked name="pay_code" class="ui-radio" value="{{ $payment['pay_code'] }}" />
                                            {{ $payment['pay_name'] }}
                                        </span>
                                        <b></b>
                                    </div>

@else

									<div class="p-radio-item payment-item
@if($order['pay_id'] == $payment['pay_id'] || ($payment['pay_code'] == 'onlinepay' && empty($order['pay_id'])))
 item-selected
@if($cod_disabled && $payment['is_cod'] == 1)
 hide
@endif

@else

@if($cod_disabled && $payment['is_cod'] == 1)
 hide
@endif

@endif
" data-value='{"type":"{{ $payment['pay_code'] }}","payid":"{{ $payment['pay_id'] }}","allow":"{{ $allow_use_surplus }}"}'>
										<span>
                                            <input type="radio"
@if($order['pay_id'] == $payment['pay_id'] || ($payment['pay_code'] == 'onlinepay' && empty($order['pay_id'])))
checked
@endif
 isCod="{{ $payment['is_cod'] }}" name="payment" class="ui-radio" value="{{ $payment['pay_id'] }}" />
                                            <input type="radio"
@if($order['pay_id'] == $payment['pay_id'] || ($payment['pay_code'] == 'onlinepay' && empty($order['pay_id'])))
checked
@endif
 name="pay_code" class="ui-radio" value="{{ $payment['pay_code'] }}" />
                                            {{ $payment['pay_name'] }}
                                        </span>
                                        <b></b>
                                    </div>

@endif


@endforeach


@else


@foreach($payment_list as $payment)


@if($payment['pay_code'] == 'onlinepay' || $payment['pay_code'] == 'balance')

                                            <div class="p-radio-item payment-item
@if($order['pay_id'] == $payment['pay_id'] || ($payment['pay_code'] == 'onlinepay' && empty($order['pay_id'])))
 item-selected
@if($cod_disabled && $payment['is_cod'] == 1)
hide
@endif

@else

@if($cod_disabled && $payment['is_cod'] == 1)
hide
@endif

@endif
" data-value='{"type":"{{ $payment['pay_code'] }}","payid":"{{ $payment['pay_id'] }}"}'>
                                                <span>
                                                    <input type="radio"
@if($order['pay_id'] == $payment['pay_id'] || ($payment['pay_code'] == 'onlinepay' && empty($order['pay_id'])))
checked
@endif
 isCod="{{ $payment['is_cod'] }}" name="payment" class="ui-radio" value="{{ $payment['pay_id'] }}" />
                                                    <input type="radio"
@if($order['pay_id'] == $payment['pay_id'] || ($payment['pay_code'] == 'onlinepay' && empty($order['pay_id'])))
checked
@endif
 name="pay_code" class="ui-radio" value="{{ $payment['pay_code'] }}" />
                                                    {{ $payment['pay_name'] }}
                                                </span>
                                                <b></b>
                                            </div>

@endif


@endforeach


@endif

                            </div>
                        </div>
                    </div>
                </div>

@endif




                <div class="ck-step">
                	<div class="ck-step-tit">
                    	<h3>{{ $lang['goods_info'] }}</h3>

@if($order['extension_code'] == '')

                        <a href="flow.php" class="extra-r">{{ $lang['back_cart'] }}</a>

@else

                        <a href="javascript:history.go(-1);" class="extra-r">{{ $lang['back_up_page'] }}</a>

@endif

                    </div>
                    <div class="ck-step-cont">
                    	<div class="ck-goods-warp" id="goods_inventory">
                            @include('frontend::library/flow_cart_goods')
                        </div>
                    </div>
                </div>

@if($config['can_invoice'] && $is_exchange_goods != 1)

                <div class="ck-step">
                	<div class="ck-step-tit">
                    	<h3>{{ $lang['Invoice_information'] }}</h3>

@if($is_kj!=1)

                        <div class="tips-new-white">
                            <b></b><span><i></i>{{ $lang['Invoice_information_notice'] }}</span>
                        </div>

@endif

                    </div>
                    <div class="ck-step-cont" id='inv_content'>
                    	<div class="invoice-warp">

@if($is_kj==1)

                                {{ $lang['Invoice_information_no'] }}

@else

                            <div class="invoice-part">
                                <span>
                                	<em class="invoice_type">{{ $lang['Ordinary_invoice'] }}</em>
                                    <em class="inv_payee">{{ $lang['personal'] }}</em>
                                    <em class="inv_content">{{ $inv_content }}</em>
                                </span>
                                <a href="javascript:void(0);" class="i-edit" ectype="invEdit" data-value='{"divid":"edit_invoice","url":"ajax_dialog.php?act=edit_invoice","title":"{{ $lang['Invoice_information'] }}"}'>{{ $lang['edit'] }}</a>
                                <input type="hidden" name="inv_payee" value="{{ $lang['personal'] }}">
                                <input type="hidden" name="inv_content" value="{{ $inv_content }}">
								<input type="hidden" name="invoice_type" value="0">
								<input type="hidden" name="tax_id" value="">
                            </div>

@endif

                        </div>
                    </div>
                </div>

@endif




                <div class="ck-step">
                	<div class="ck-step-tit">
                    	<h3>{{ $lang['Other_information'] }}</h3>
                    </div>
                    <div class="ck-step-cont">
                    	<div class="other-warp">
                            <div class="other-list">

@if($allow_use_surplus && $is_stages)

                                <div class="qt_item" id="qt_balance"
@if($disable_surplus)
 style="display:none"
@endif
>
                                    <div class="item_label">{{ $lang['use_surplus'] }}：</div>
                                    <div class="item_value">
                                        <input type="text" class="qt_text" name="surplus" id="ECS_SURPLUS" size="10" value="{{ $order['surplus'] }}" data-yoursurplus="{{ $your_surplus ?? 0 }}" onblur="changeSurplus(this.value)" />
                                        <input type="hidden" class="sur" value="{{ $total['goods_price_formated'] }}" />
                                        <input type="hidden" class="shipping" value="{{ $total['shipping_fee_formated'] }}" />
                                        <div class="sp"><span>{{ $lang['current'] }}{{ $lang['your_surplus'] }}</span><em>{{ config('shop.currency_format', '¥') }}{{ $your_surplus ?? 0 }}</em></div>
                                        <div class="sp ftx-01" id="ECS_SURPLUS_NOTICE"></div>
                                    </div>
                                </div>

@endif



@if($allow_use_integral && session('flow_type') != 5)

                                <div class="qt_item" id="qt_integral">
                                    <div class="item_label">{{ $lang['use_integral'] }}：</div>
                                    <div class="item_value">
                                        <input type="text" class="qt_text" name="integral" id="ECS_INTEGRAL" data-maxinteg="{{ $order_max_integral }}" onblur="changeIntegral(this.value)" value="{{ $order['integral'] ?? 0 }}" size="10"/>
                                        <div class="sp">
                                            <span>{{ $lang['can_use_integral'] }}</span>
                                            <span>{{ $your_integral ?? 0 }}</span>
                                            <span>，{{ $lang['noworder_can_integral'] }}</span>
                                            <span>{{ $order_max_integral }}</span>
                                        </div>
                                        <div class="sp ftx-01" id="ECS_INTEGRAL_NOTICE"></div>
                                    </div>
                                </div>

@endif



@if($open_pay_password)

                                <div class="qt_item" id="qt_onlinepay"
@if($order['surplus'] == 0)
 style="display: none;"
@endif
 >
                                    <div class="item_label">{{ $lang['pay_password'] }}：</div>
                                    <div class="item_value">
                                        <input type="password" style="display:none" autocomplete="off" />
                                        <input type="password" class="qt_text" name="pay_pwd" size="20" value="" autocomplete="off" />
                                        <input name="pay_pwd_error" type="hidden" id="pwd_error" value="{{ $pay_pwd_error }}" autocomplete="off" />
                                        <div class="sp ftx-01" id="ECS_PAY_PAYPWD"></div>
                                    </div>
                                </div>

@endif



@if($how_oos_list)

                                <div class="qt_item">
                                    <div class="item_label">{{ $lang['booking_process'] }}：</div>
                                    <div class="item_value">

@foreach($how_oos_list as $how_oos_id => $how_oos_name)


                                        <div class="radio-item">
                                            <input type="radio" name="how_oos" class="ui-radio" value="{{ $how_oos_id }}"
@if($order['how_oos'] == $how_oos_id)
checked
@endif
 id="checkbox_{{ $how_oos_id }}" onclick="changeOOS(this)" autocomplete="off" />
                                            <label for="checkbox_{{ $how_oos_id }}" class="ui-radio-label">{{ $how_oos_name }}</label>
                                        </div>

@endforeach

                                    </div>
                                </div>

@endif

                            </div>
                        </div>
                    </div>
                </div>




@if($value_card_list && $is_value_cart != 0 || $user_coupons || $coupons_list || $bonus_list || $no_bonuslist)

                <div class="ck-step ck-step-other">
                	<div class="ck-step-tit ck-toggle ck-toggle-off" ectype="ck-toggle">
                    	<h3>{{ $lang['favourable_card_coupons'] }}</h3>
                        <i class="iconfont icon-down"></i>

@if($user_coupons || $value_card_list && $is_value_cart == 1 || $bonus_list)
<span class="tag">{{ $lang['available'] }}</span>
@endif

                    </div>
                    <div class="ck-step-cont" style="display:none;">
                    	<div class="order-virtual-tabs">
                        	<ul>

@if($user_coupons || $coupons_list)
<li class="curr"><span>{{ $lang['preferential'] }}</span>
@if($user_coupons)
<b></b>
@endif
</li>
@endif


@if($allow_use_value_card && $is_value_cart == 1)
<li><span>{{ $lang['value_card'] }}</span>
@if($value_card_list)
<b></b>
@endif
</li>
@endif


@if($bonus_list || $no_bonuslist)
<li><span>{{ $lang['bonus'] }}</span>
@if($bonus_list)
<b></b>
@endif
</li>
@endif

                            </ul>
                        </div>

                        <div class="toggle-panel-main">

@if($user_coupons || $coupons_list)

                            <div class="toggle-panl-warp panl-coupon" ectype='order_coupoms_list'>
                                @include('frontend::library/order_coupoms_list')
                            </div>

@endif


@if($allow_use_value_card && $is_value_cart == 1)

                            <div class="toggle-panl-warp panl-stored">
                            	<div class="panl-warp">

@foreach($value_card_list as $value_card)

                                    <div class="panl-item" ectype="panlItem" data-ucid="{{ $value_card['vc_id'] }}"data-type="value_card" style="height: 131px;">
                                        <p style="margin-top: 5px;text-align: left;height: 15px;">NO：{{ $value_card['value_card_sn'] }}</p>

                                    	<p class="tit" style="margin-top: 2px;">{{ $value_card['name'] }}</p>
                                    	<strong style="font-size:28px;">{{ $value_card['card_money_formated'] }}</strong>

@if($value_card['vc_dis'])

                                        <p style="margin-top: 2px;height: 10px;">{{ $value_card['vc_dis'] }}</p>

@else

                                        <p style="margin-top: 2px;height: 10px;"></p>

@endif

                                    	<p class="tit" style="margin-top:10px;">{{ $lang['end_date'] }}:{{ $value_card['end_time'] }}</p>
                                        <span class="p-total">{{ $value_card['card_limit'] }}</span>
                                        <b></b>
                                    </div>

@endforeach

                                    <input name="value_card_psd" id="value_card_psd" type="text" class="qt_text2 order_vc_psd hide" size="15" value="" autocomplete="off" />
                                    <input name="validate_value_card" type="button" class="hide" id="bnt_bonus_sn" value="{{ $lang['bind_value_card'] }}" onclick="validateVcard($('.order_vc_psd').val())" autocomplete="off" />
                                    <div class="panl-item panl-item-new">
                                    	<a href="user.php?act=value_card" target="_blank" class="add-new-stored">
                                        	<i class="iconfont icon-jia"></i>
                                            <em>{{ $lang['add_stored_value_card'] }}</em>
                                        </a>
                                    </div>
                                </div>
                            </div>

@endif



@if($bonus_list || $no_bonuslist)

                            <div class="toggle-panl-warp panl-redBag" ectype='order_bonus_list'>

@if($bonus_list)

                            	<div class="panl-top">
                                	<div class="panl-items">

@foreach($bonus_list as $bonus)

                                	<div class="panl-item
@if($loop->iteration % 4 == 0)
 panl-item-last
@endif
" ectype="panlItem" data-ucid="{{ $bonus['bonus_id'] }}" data-type="bonus" title="{{ $lang['full'] }}{{ $bonus['min_goods_amount_formated'] }}{{ $lang['keyong'] }}">
                                        <strong>{{ $bonus['bonus_money_formated'] }}</strong>
                                        <p>{{ $lang['bonus_card_number'] }}：{{ $bonus['type_name'] }}</p>
                                        <p>{{ $lang['overdue_time'] }}：{{ $bonus['use_end_date'] }}</p>
                                        <b></b>
                                    </div>

@endforeach

                                    </div>
                                </div>

@endif


@if($no_bonuslist)

                                <div class="panl-bot">
                                	<div class="panl-items">

@foreach($no_bonuslist as $bonus)

                                    <div class="panl-item panl-item-disabled
@if($loop->iteration % 4 == 0)
 panl-item-last
@endif
" title="满{{ $bonus['min_goods_amount_old'] }}{{ config('shop.currency_format', '¥') }}{{ $lang['keyong'] }}">
                                         <strong>{{ $bonus['type_money'] }}</strong>
                                        <p>{{ $lang['bonus_card_number'] }}：{{ $bonus['type_name'] }}</p>
                                        <p>{{ $lang['overdue_time'] }}：{{ $bonus['use_enddate'] }}</p>
                                        <b></b>
                                    </div>

@endforeach

                                    </div>
                                </div>

@endif

                            </div>

@endif

                        </div>
                        <input type="hidden" name="uc_id" id="uc_id" value="0" autocomplete="off">
                        <input type="hidden" name="not_freightfree" id="not_freightfree" value="0" autocomplete="off">

                        <input type="hidden" name="bonus" id="bonus_id" value="0" autocomplete="off">
                        <input type="hidden" name="vc_id" id="ECS_VALUE_CARD" value="0" autocomplete="off">
                    </div>
                </div>

@endif


            </div>
            <div id="ECS_ORDERTOTAL">
			@include('frontend::library/order_total')
            </div>

            <input type="hidden" name="user_id" value="{{ $user_id }}" autocomplete="off" />
            <input type="hidden" name="done_cart_value" value="{{ $cart_value }}" autocomplete="off" />
            <input type="hidden" name="step" value="done" autocomplete="off" />
            <input type="hidden" name="is_address" value="0" autocomplete="off" />
        </div>
        @csrf </form>
    </div>

@endif



@if($step == "order_reload" )

    <div class="container">
        <div class="w w1200">

@if($is_onlinepay && !$is_presale_goods)


            <div class="payOrder-warp" id="pay_main">
                <div class="payOrder-desc">
                    <div class="pay-top">
                        <h3>
@if($order['order_amount'] > 0)
{{ $lang['checkout_success_notic'] }}
@else
{{ $lang['checkout_success_end'] }}
@endif
</h3>
                        <div class="pay-total">
                            <span>{{ $lang['total_amount_payable'] }}：</span>
                            <div class="pay-price">{{ $order['format_order_amount'] }}</div>
                        </div>
                    </div>
                    <div class="pay-txt">
                        <p>{{ $lang['order_number'] }}：<em id="nku">{{ $order['order_sn'] }}</em></p>
                        <p>{{ $lang['Select_payment'] }}：<span id="paymode">{{ $order['pay_name'] }}</span></p>

@if($stores_info)

                        <p>{{ $lang['store_name'] }}：<span>{{ $stores_info['stores_name'] }}</span></p>

@else


@if($child_order == 0)


@if($order['shipping_name'] && $order['shipping_isarr'] == 0)

                            <p>{{ $lang['shipping_method'] }}: <span id="express">{{ $order['shipping_name'] }}</span></p>

@endif


@else

                            <p><span class="txt">{{ $lang['checkout_success_six'] }}<em>{{ $child_order }}</em>{{ $lang['checkout_success_three'] }}：</span></p>

@endif


@endif

                    </div>

@if($child_order == 0)

                    <div class="o-list">
                        <div class="o-list-info">
                            <span id="shdz">{{ $lang['Send_to'] }}：{{ $order['region'] }}&nbsp;&nbsp;{{ $order['address'] }}</span>
                            <span id="shr">{{ $lang['Consignee'] }}：{{ $order['consignee'] }}</span>
                            <span id="mobile">{{ $order['mobile'] }}</span>
                        </div>
                    </div>

@endif

                    <a href="flow.php?step=pdf&order={{ $order['order_id'] }}" target="_blank" class="orderPrint ftx-05">{{ $lang['orders_print'] }}</a>&nbsp;&nbsp;
                    <a href="{{ url('/') }}" target="_blank" class="orderPrint ftx-05">{{ $lang['pay_qt'] }}</a>
                </div>


@if($child_order != 0)

                <div class="w1200" style="height:10px; overflow:hidden;">&nbsp;</div>
                <div class="w1200" style="background:#FFF;">
                    <div class="shopend-info-many mt10" style="text-align:center; padding-top:20px;">
                        <div class="shopend-info-items">

@foreach($child_order_info as $c_order)

                        <div class="shopend-info-item">
                            <p>{{ $lang['order_number'] }}：<em class="nku" id="nku">{{ $c_order['order_sn'] }}</em></p>


@if($c_order['order_amount'] > 0)

                            <p>{{ $lang['order_amount'] }}：<em>{{ $c_order['amount_formated'] }}</em></p>

@endif



@if($c_order['pay_total'] > 0)

                            <p>{{ $lang['amount_paid'] }}：<em>{{ $c_order['total_formated'] }}</em></p>

@endif



@if(!$is_group_buy)

                            <p>{{ $lang['shipping_method'] }}：<em>{{ $c_order['shipping_name'] }}</em>&nbsp;&nbsp;&nbsp;&nbsp;{{ $lang['freight'] }}：<em>{{ $c_order['shipping_fee_formated'] }}</em></p>

@endif


@if($stores_info)

                            <p>{{ $lang['store_name'] }}：<em>{{ $stores_info['stores_name'] }}</em></p>

@else

                            <p>{{ $lang['Consignee'] }}：<span id="username">{{ $order['consignee'] }}</span><span id="tel" class="ml20">{{ $order['mobile'] }}</span></p>
                            <p>{{ $lang['Send_to'] }}：<span id="address">{{ $address_info }}&nbsp;{{ $order['address'] }}</span></p>

@endif

                        </div>

@endforeach

                        </div>
                    </div>
                </div>

@endif



@if($is_onlinepay)

                <div class="payOrder-mode">

@if($stages_info && $stages_info['stages_qishu'] && $is_chunsejinrong)

                        <div class="payOrder-list">
                            <div class="p-mode-list">
                                <div class="p-mode-item chunsejinrong " flag="chunsejinrong" >
                                    <a href="flow.php?step=done&act=chunsejinrong&order_sn={{ $order['order_sn'] }}" id="chunsejinrong" order_sn="{{ $order['order_sn'] }}" class="fl" >{{ $lang['ious_pay'] }}</a>
                                </div>
                                <div class="bt-desc">
                                	<p>
                                        <span class="mr10">{{ $lang['Available_Credit'] }}：{{ $stages_info['baitiao'] }}{{ $lang['yuan'] }}</span>
                                        <span>{{ $lang['next_repayment_day'] }}：{{ $stages_info['repay_date'] }}</span>
                                    </p>
                                	<p class="qishu">{{ $stages_info['stages_qishu'] }}{{ $lang['qi'] }} | {{ $stages_info['stages_one_price'] }}{{ $lang['yuan'] }}/{{ $lang['qi'] }}</p>
                                </div>
                            </div>
                        </div>

@else

                        <div class="payOrder-list">
                            <div class="p-mode-tit">
                                <h3>{{ $lang['payment_is_online'] }}</h3>
                            </div>
                            <div class="p-mode-list">


@foreach($payment_list as $key => $vo)


                                        <div class="p-mode-item {{ $vo['pay_code'] }}" pay_fee="{{ $vo['pay_fee'] }}" order_id="{{ $order['order_id'] }}" pay_id="{{ $vo['pay_id'] }}" flag="{{ $vo['pay_code'] }}">
                                            <input type="button" value="{{ $vo['pay_code'] }}" class="btn btn-info " >

@if($vo['pay_fee'] > 0)

                                            <span>{{ $lang['service_charge'] }}<i>{{ $vo['format_pay_fee'] }}</i></span>

@endif

                                            <b></b>
                                        </div>


@endforeach


                            </div>
                        </div>

@endif

                    <div class="single-btn" id="pay_button">{{ $lang['immediately_pay'] }} </div>
                </div>



@else

                    <div class="single-btn">

@if($pay_online)


                        {{ $pay_online }}

@endif

                    </div>

@endif


            </div>

@else


            <div class="shopend-warp">


@if($child_order != 0)

                <div class="shopend-info-many">
                    <div class="shopend-info">
                        <div class="s-i-left"><i class="ico-success"></i></div>
                        <div class="s-i-right">
                            <h3>

@if($order['pay_code'] == 'cod')

                                {{ $lang['pay_code_notic_one'] }}

@elseif ($order['pay_code'] == 'bank')

                                {{ $order['bank_message'] }}

@else


@if($order['pay_code'] == 'balance')

                                {{ $lang['payment_Success'] }}

@else

                                {{ $lang['pay_code_notic_three'] }}

@endif


@endif

                            </h3>
                            <span class="txt">{{ $lang['checkout_success_six'] }}<em>{{ $child_order }}</em>{{ $lang['checkout_success_three'] }}：</span>
                        </div>
                    </div>
                    <div class="shopend-info-items">

@foreach($child_order_info as $c_order)

                    <div class="shopend-info-item">
                        <p>{{ $lang['order_number'] }}：<em class="nku" id="nku">{{ $c_order['order_sn'] }}</em></p>


@if($c_order['order_amount'] > 0)

                        <p>{{ $lang['order_amount'] }}：<em>{{ $c_order['amount_formated'] }}</em></p>

@endif



@if($c_order['pay_total'] > 0)

                        <p>{{ $lang['amount_paid'] }}：<em>{{ $c_order['total_formated'] }}</em></p>

@endif



@if(!$is_group_buy)

                        <p>{{ $lang['shipping_method'] }}：<em>{{ $c_order['shipping_name'] }}</em>&nbsp;&nbsp;&nbsp;&nbsp;{{ $lang['freight'] }}：<em>{{ $c_order['shipping_fee_formated'] }}</em></p>

@endif


@if($stores_info)

                        <p>{{ $lang['store_name'] }}：<em>{{ $stores_info['stores_name'] }}</em></p>

@else

                        <p>{{ $lang['Consignee'] }}：<span id="username">{{ $order['consignee'] }}</span><span id="tel" class="ml20">{{ $order['mobile'] }}</span></p>
                        <p>{{ $lang['Send_to'] }}：<span id="address">{{ $address_info }}&nbsp;{{ $order['address'] }}</span></p>

@endif

                    </div>

@endforeach

                    </div>

@if($order['pay_code'] == 'bank')

                    <h3 style='margin-top:10px'>{{ $lang['remittance_info'] }}</h3>
                    <div class="s-i-tit">

@foreach($order['pay_config'] as $bankinfo)

                        <p>{{ $bankinfo['name'] }}：<em id="username">{{ $bankinfo['value'] }}</em></p>

@endforeach

                    </div>

@endif

                    <div class="clear"></div>
                    <div class="s-i-btn">
                        <a href="
@if($is_zc_order)
user.php?act=crowdfunding
@else
user_order.php?act=order_list
@endif
" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>
                        <a href="{{ url('/') }}" class="ftx-05">{{ $lang['back_home'] }}</a>
                    </div>
                </div>

@else

                <div class="shopend-info">
                    <div class="s-i-left"><i class="ico-success"></i></div>
                    <div class="s-i-right">
                    	<h3>

@if($order['pay_code'] == 'cod')

                            {{ $lang['pay_code_notic_one'] }}

@elseif ($order['pay_code'] == 'bank')

                            {{ $order['bank_message'] }}

@else


@if($order['pay_code'] == 'balance' || $order['order_amount'] == 0)

                            {{ $lang['payment_Success'] }}

@else

                            {{ $lang['pay_code_notic_three'] }}

@endif


@endif

                        </h3>
                        <div class="s-i-tit">
                            <p>{{ $lang['order_number'] }}：<em id="nku">{{ $order['order_sn'] }}</em></p>

@if($stores_info)

                            <p>{{ $lang['shipping_method'] }}：<em>{{ $lang['private_store'] }}</em></p>
                            <p>{{ $lang['store_name'] }}：<em>{{ $stores_info['stores_name'] }}</em></p>

@else


@if(!$is_group_buy)

                            <p>{{ $lang['shipping_method'] }}：<em>{{ $order['shipping_name'] }}</em></p>
                            <p>{{ $lang['freight'] }}：<em>{{ $order['format_shipping_fee'] }}</em></p>

@endif

                            <p>{{ $lang['Total_amount_payable'] }}：<em>{{ $order['format_order_amount'] }}</em></p>
                            <p>{{ $lang['Consignee'] }}：<span id="username">{{ $order['consignee'] }}</span><span id="tel" class="ml20">{{ $order['mobile'] }}</span></p>
                            <p>{{ $lang['Send_to'] }}： <span id="address">{{ $address_info }}&nbsp;{{ $order['address'] }}</span></p>

@endif

                        </div>

@if($order['pay_code'] == 'bank')

                        <h3 style='margin-top:10px'>{{ $lang['remittance_info'] }}</h3>
                        <div class="s-i-tit">

@foreach($order['pay_config'] as $bankinfo)

                            <p>{{ $bankinfo['name'] }}：<em id="username">{{ $bankinfo['value'] }}</em></p>

@endforeach

                        </div>

@endif

                        <div class="s-i-btn">

@if($is_zc_order)

                            <a href="user.php?act=crowdfunding" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@else

                            <a href="user_order.php?act=order_list" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@endif

                            <a href="{{ url('/') }}" class="ftx-05">{{ $lang['back_home'] }}</a>
                        </div>
                    </div>
                </div>

@endif

            </div>

@endif



@if($goods_buy_list)

            <div class="p-panel-main c-history">
                <div class="ftit ftit-delr"><h3>{{ $lang['ftit_delr'] }}</h3></div>
                <div class="gl-list clearfix">
                    <ul class="clearfix">

@foreach($goods_buy_list as $goods)


@if($loop->iteration < 7 )


@if($goods['goods_id'])

                        <li class="opacity_img">
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="190" height="190"></a></div>
                            <div class="p-price">

@if($goods['promote_price'] == '')

                                    {{ $goods['shop_price'] }}

@else

                                    {{ $goods['promote_price'] }}

@endif

                            </div>
                            <div class="p-name">
                                <a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">
                                    @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                    {{ $goods['goods_name'] }}
                                </a>
                            </div>
                            <div class="p-num">{{ $lang['sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                        </li>

@endif


@endif


@endforeach

                    </ul>
                </div>
            </div>

@endif

        </div>
    </div>
    <div id="pay_Dialog" class="hide">
    	<div class="pat">{{ $lang['pat'] }}</div>
        <div class="paydia-warp">
        	<i></i>
            <div class="con">
            	<div class="con-warp con-success">
                    <h3>{{ $lang['pay_dialog_success'] }}</h3>
                    <a href="user_order.php?act=order_list" class="ftx-05">{{ $lang['order_detail'] }}></a>
                </div>
                <div class="con-warp con-fail">
                	<h3>{{ $lang['pay_dialog_fail'] }}</h3>
                	<a href="article.php?id=17" class="ftx-05">{{ $lang['pay_problem'] }}></a>
                    <a href="{{ url('/') }}" class="ftx-05">{{ $lang['pay_qt'] }}></a>
                </div>
            </div>
        </div>
    </div>

@if($ajax_send_mail)

	<script type="text/javascript">
		var send_time = '{{ $send_time }}';
		var order_id = '{{ $order_id }}';
		Ajax.call('ajax_dialog.php?act=ajax_send_mail', 'send_time=' + send_time + '&order_id=' + order_id, '', 'POST','JSON');
	</script>

@endif


@endif




@if($step == "pay_success" )

    <div class="shopend-warp">


@if($child_order != 0)

        <div class="shopend-info-many">
        	<div class="shopend-info">
                <div class="s-i-left"><i class="ico-success"></i></div>
                <div class="s-i-right">
                    <h3>{{ $lang['payment_Success'] }}</h3>
                    <span class="txt">{{ $lang['checkout_success_six'] }}<em>{{ $child_order }}</em>{{ $lang['checkout_success_three'] }}：</span>
                </div>
            </div>
            <div class="shopend-info-items">

@foreach($child_order_info as $c_order)

            <div class="shopend-info-item">
                <p>{{ $lang['order_number'] }}：<em class="nku" id="nku">{{ $c_order['order_sn'] }}</em></p>
                <p>{{ $lang['Total_amount_payable'] }}：<em>{{ $c_order['order_amount'] }}</em></p>

@if(!$is_group_buy)

                <p>{{ $lang['shipping_method'] }}：<em>{{ $c_order['shipping_name'] }}</em>&nbsp;&nbsp;&nbsp;&nbsp;{{ $lang['freight'] }}：<em>{{ config('shop.currency_format', '¥') }}{{ $c_order['shipping_fee'] }}</em></p>

@endif


@if($stores_info)

                <p>{{ $lang['store_name'] }}：<em>{{ $stores_info['stores_name'] }}</em></p>

@else

                <p>{{ $lang['Consignee'] }}：<span id="username">{{ $order['consignee'] }}</span><span id="tel" class="ml20">{{ $order['mobile'] }}</span></p>
                <p>{{ $lang['Send_to'] }}：<span id="address">{{ $address_info }}&nbsp;{{ $order['address'] }}</span></p>

@endif

            </div>

@endforeach

            </div>
            <div class="clear"></div>
            <div class="s-i-btn">

@if($is_zc_order)

                <a href="user_crowdfund.php?act=crowdfunding" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@else

                <a href="user_order.php?act=order_list" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@endif

                <a href="{{ url('/') }}" class="ftx-05">{{ $lang['back_home'] }}</a>
            </div>
        </div>

@else

        <div class="shopend-info">
            <div class="s-i-left"><i class="ico-success"></i></div>
            <div class="s-i-right">
                <h3>{{ $lang['payment_Success'] }}</h3>
                <div class="s-i-tit">
                    <p>{{ $lang['order_number'] }}：<em id="nku">{{ $order['order_sn'] }}</em></p>
                    <p>{{ $lang['Total_amount_payable'] }}：<em>{{ $order['order_amount'] }}</em></p>

@if($stores_info)

                    <p>{{ $lang['shipping_method'] }}：<em>{{ $lang['private_store'] }}</em></p>
                    <p>{{ $lang['store_name'] }}：<em>{{ $stores_info['stores_name'] }}</em></p>

@else


@if(!$is_group_buy)

                    <p>{{ $lang['shipping_method'] }}：<em>{{ $order['shipping_name'] }}</em></p>
                    <p>{{ $lang['freight'] }}：<em>{{ config('shop.currency_format', '¥') }}{{ $order['shipping_fee'] }}</em></p>

@endif

                    <p>{{ $lang['Consignee'] }}：<span id="username">{{ $order['consignee'] }}</span><span id="tel" class="ml20">{{ $order['mobile'] }}</span></p>
                    <p>{{ $lang['Send_to'] }}： <span id="address">{{ $address_info }}&nbsp;{{ $order['address'] }}</span></p>

@endif

                </div>
                <div class="s-i-btn">

@if($is_zc_order)

                    <a href="user_crowdfund.php?act=crowdfunding" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@else

                    <a href="user_order.php?act=order_list" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@endif

                    <a href="{{ url('/') }}" class="ftx-05">{{ $lang['back_home'] }}</a>
                </div>
            </div>
        </div>

@endif



@if($goods_buy_list)

        <div class="p-panel-main c-history">
            <div class="ftit ftit-delr"><h3>{{ $lang['ftit_delr'] }}</h3></div>
            <div class="gl-list clearfix">
                <ul class="clearfix">

@foreach($goods_buy_list as $goods)


@if($loop->iteration < 7 )

                    <li class="opacity_img">
                        <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="190" height="190"></a></div>
                        <div class="p-price">

@if($goods['promote_price'] == '')

                                {{ $goods['shop_price'] }}

@else

                                {{ $goods['promote_price'] }}

@endif

                        </div>
                        <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                        <div class="p-num">{{ $lang['sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                    </li>

@endif


@endforeach

                </ul>
            </div>
        </div>

@endif

    </div>


@endif


    @include('frontend::library/cart_html')

    @include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/warehouse.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.nyroModal.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/lib_ecmobanFunc.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>

@if($store_id)

    <link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}" />
    <script type="text/javascript" src="{{ url('/') }}/calendar.php?lang={{ $cfg_lang }}"></script>

@endif

	<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/region.js') }}"></script>


@if($step == "cart")


@if($goods_list)
<script type="text/javascript" src="{{ skin('js/checkAll.js') }}"></script>
@endif

    <script type="text/javascript">
    	function changenum(rec_id, diff, warehouse_id, area_id, favourable_id){
            var cValue = $("#cart_value").val();
            var goods_number = Number($('#goods_number_' + rec_id).val()) + Number(diff);    
            if(goods_number < 1){
				pbDialog(json_languages.Purchase_restrictions,"",0)
            }else{
                change_goods_number(rec_id,goods_number, warehouse_id, area_id, cValue, favourable_id);
            }
        }

        function change_goods_number(rec_id, goods_number, warehouse_id, area_id, cValue, favourable_id){
            if(cValue != "" || cValue == 'undefined'){
               var cValue = $("#cart_value").val(); 
            }
            if(goods_number == 0){
                //pbDialog("数量不能为0","",0);
                goods_number = 1;
            }

            var items = $("#checkItem_" +rec_id).parents(".item-single");
            var input = items.find("*[ectype='ckGoods']");
            var str ='';
            var arr = [];
            input.each(function(){
                if($(this).prop('checked')== true){
                    var val = $(this).val();
                    str += val + ',';
                    arr.push(val);
                }
            });

            str = str.substring(str.length-1,0);

            if(str == ""){
                pbDialog(json_languages.checked_goods_number,"",0);
                return false;
            }else{
                if(arr.indexOf(String(rec_id)) == -1){
                    pbDialog(json_languages.checked_goods_number,"",0);
                    return false;
                }

                if(items.attr("ectype") == "promoItem"){
                    var promoStr = "";
                    input.each(function(){
                        var val = $(this).val();
                        promoStr += val + ',';
                    })

                    promoStr = promoStr.substring(promoStr.length-1,0);
                }
            }

            Ajax.call('ajax_flow_goods.php?step=ajax_update_cart', 'rec_id=' + rec_id + '&sel_id=' + str + '&pro_sel_id=' + promoStr + '&sel_flag=' + 'cart_sel_flag' +'&goods_number=' + goods_number +'&cValue=' + cValue +'&warehouse_id=' + warehouse_id +'&area_id=' + area_id +'&favourable_id=' + favourable_id, change_goods_number_response, 'POST','JSON');                
        }

        function change_goods_number_response(result)
        {
            var rec_id = result.rec_id;           
            if(result.error == 0){
                $('#goods_number_' +rec_id).val(result.goods_number);//更新数量

                $('#rate_price_' +rec_id).html('');//清空税率显示
                $('#goods_subtotal_' +rec_id).html(result.goods_subtotal);//更新小计

				if(result.dis_amount > 0){
					$('#discount_amount_' +rec_id).parents('.cuttip').removeClass("hide");
				}else{
					$('#discount_amount_' +rec_id).parents('.cuttip').addClass("hide");
				}

				$('#discount_amount_' +rec_id).html(result.discount_amount);//商品优惠价格

                if(result.goods_number == 1){
                    $('#goods_number_' +rec_id).parents('.amount-warp').find('.btn-reduce').addClass("btn-disabled");
                }else{
                    $('#goods_number_' +rec_id).parents('.amount-warp').find('.btn-reduce').removeClass("btn-disabled");
                }
                if(result.goods_number <= 0){
                    $('#tr_goods_' +rec_id).style.display = 'none'; //数量为零则隐藏所在行
                    $('#tr_goods_' +rec_id).innerHTML = '';
                }
                $('#total_desc').html(result.flow_info);//更新合计
                if ($('ECS_CARTINFO')){
                    $('#ECS_CARTINFO').html(result.cart_info); //更新购物车数量
                }

                if(result.can_buy){
                    $.each(result.can_buy,function(i,e){
                        if(e.err == 1){
                            $("#shop_" + e.ru_id).html('该店商品超过限定金额');
                        }else{
                            $("#shop_" + e.ru_id).html('');
                        }
                    });
                }

                if(result.group.length > 0){
                    for(var i=0; i<result.group.length; i++){
                        $("#" + result.group[i].rec_group).html(result.group[i].rec_group_number);//配件商品数量
                        $("#" + result.group[i].rec_group_talId).html(result.group[i].rec_group_subtotal);//配件商品金额
                    }
                }

                $("#goods_price_" + rec_id).html(result.goods_price);
                $("*[ectype='save_total']").html(result.save_total_amount); //优惠节省总金额
                $("*[ectype='cartNum']").html(result.subtotal_number); //商品总数

                // 如果是优惠活动内的商品，更新优惠活动局部 qin
                if (result.act_id){
                    $("#product_promo_" + result.ru_id + "_" + result.act_id).html(result.favourable_box_content);
                }
            }else if(result.message != ''){
				//更新数量
                $('#goods_number_' +rec_id).val(result.cart_Num);
				pbDialog(result.message," ",0,"",90,10);
            }                
        }

@if($goods_list)

		//购物车底边悬浮栏
		tfootScroll();

@endif

		//超值礼包
		$(".package_goods ul").perfectScrollbar("destroy");
		$(".package_goods ul").perfectScrollbar();
    </script>


@elseif ($step == "checkout")

	<script type="text/javascript">
		$(function(){
			/* 门店订单显示信息 start*/
			if($("*[ectype='storeWarp']").length > 0){
				$("#consignee_list,.d-address").addClass("hide");
				$("input[name='is_address']").val(1);
			}else{
				$("#selProvinces").val(0);
				$("#store_id").val(0);
			}
			/* 门店订单显示信息 end*/

			/* 优惠券/储值卡/红包切换 */
			$(".ck-step-cont").slide({titCell:".order-virtual-tabs li",mainCell:".toggle-panel-main",titOnClassName:"curr",trigger:"click"});

			//点击查看图片
			$('.nyroModal').nyroModal();

			// goods_flow_type = 100 有虚拟商品
            var goods_flow_type = $("input[name='goods_flow_type']").val();
            if (goods_flow_type != 100) {
                /*结算页面 匹配用户收货地址*/
                Ajax.call('ajax_flow_address.php?step=match_user_consignee', '', function (result) {

                    if (result.code == 2) {
                        pbDialog(result.msg, "", 0, 630, "", "", true, function () {
                            // 关闭弹窗
                            $('.cboxContent .pb-x').click();
                            // 点击新建收货地址
                            return $('.add-new-address').click();
                        }, '{{ __('flow.add_address') }}');
                    }

                    $('#consignee-addr').html(result.content);

                    if ($("*[ectype='cs-w-item']").hasClass("cs-selected")) {
                        $(".cs-selected").click();
                    }

                }, 'POST', 'JSON');
            }


@if($seller_store || $store_seller == 'store_seller')

			$.levelLink(1);

@endif

		});

		//门店时间选择

@if($store_id)

		var opts1 = {
			'targetId':'time_shop',
			'triggerId':['time_shop'],
			'alignId':'time_shop',
			'zIndex':999999,
			'format':'-',
            'min':'{{ $now_time }}' //最小时间
		}
		xvDate(opts1);

@endif


		$(".panl-items").perfectScrollbar("destroy");
		$(".panl-items").perfectScrollbar();

		//超值礼包
		$(".package_goods ul").perfectScrollbar("destroy");
		$(".package_goods ul").perfectScrollbar();
    </script>


@elseif ($step == "order_reload")

    <script type="text/javascript">
    	$(function(){
            var divs = $('.p-mode-item');
            // 应付金额
            var pay_price = $(".pay-price").text();

            // 点击切换支付方式
            $(".p-mode-list .p-mode-item").click(function(e){

                for (var i = 0; i < divs.length; i++) {
                     $(divs[i]).find("b").removeClass("ou");
                }
                $(this).addClass("item-selected").siblings().removeClass("item-selected");
                $(this).find("b").addClass("ou");

                var pay_id = $(this).attr('pay_id');
                var pay_code = $(this).attr('flag');
                var order_id = $(this).attr('order_id');
                var pay_fee = $(this).attr('pay_fee');

                // 白条支付
                if (pay_code && pay_code == 'chunsejinrong') {
                    var button = $(this).html();
                    $('#pay_button').html(button);
                    $('#pay_button a').text('{{ $lang['immediately_pay'] }}');
                    return false;
                }

                var pay_fee_format = $(this).children("span").children("i").text();

                var pay_fee_content = '<div style="text-align:center;padding: 20px 10px 0;">{{ $lang['commission_reminder'] }}'+pay_fee_format+'"{{ $lang['commission_reminderon'] }}"</div>'

                $('#pay_button').html('');

                $.post("ajax_flow_pay.php?step=change_payment", {pay_id: pay_id, pay_code:pay_code, order_id: order_id}, function (data) {
                        // 有手续费弹窗
                        if (pay_fee > 0) {
                            pb({
                                id:"payDialog1",
                                title:'',
                                width:300,
                                height:100,
                                content:pay_fee_content,
                                drag:true,
                                foot:false
                            });
                        }

                        // 切换支付方式成功返回
                        if (data.code == 0){
                            // 应付金额
                            $(".pay-price").text(data.order_amount_format);
                            $('#pay_button').html(data.button);

                            if (pay_code == 'wxpay') {
                                //微信支付定时查询订单状态
                                checkOrder();
                            }else{
                                clearCheckOrder();
                            }

                            $("#pay_button").css('background','#f42424')
                            $('#pay_button input[type="button"]').val('{{ $lang['immediately_pay'] }}');
                            $('#pay_button a').text('{{ $lang['immediately_pay'] }}');
                        } else {
                            // 失败返回
                            // pbDialog(data.msg,"",0);
                            $("#pay_button").css('background','#999')
                            $('#pay_button input[type="button"]').val('{{ $lang['dispose_money'] }}');
                            $('#pay_button').text('{{ $lang['dispose_money'] }}');
                            return false;
                        }
                }, 'json');

                return false;
            });

            // 正在支付提示弹窗
			$("#pay_button input").click(function(){
				var content = $("#pay_Dialog").html();
				pb({
					id:"payDialog",
					title:json_languages.payTitle,
					width:550,
					height:300,
					content:content,
					drag:false,
					foot:false
				});
			});


			//微信支付扫码
			$(document).on('click', "[data-type='wxpay']", function(){
				var content = $("#wxpay_dialog").html();
				pb({
					id: "scanCode",
					title: "",
					width: 716,
					content: content,
					drag: true,
					foot: false,
					cl_cBtn: false,
					cBtn: false
				});
			});

		});

		var timer;
		function checkOrder(){
			var pay_name = "{{ $order['pay_name'] }}";
			var pay_status = "{{ $order['pay_status'] }}";
			var url = "ajax_flow_pay.php?step=checkorder&order_id={{ $order['order_id'] }}";
			var log_id = "{{ $order['log_id'] }}";
			if(pay_name == json_languages.payment_is_online && pay_status == 0){
				$.get(url, {}, function(data){
					//已付款
					if(data.code > 0 && data.pay_code == 'wxpay'){
						clearTimeout(timer);
						location.href = "respond.php?code=" + data.pay_code + "&status=1&log_id=" + log_id;
					}
				},'json');
			}
			timer = setTimeout("checkOrder()", 5000);
		}

        function clearCheckOrder(){
            window.clearInterval(timer)
        }
    </script>

@endif


    <script type="text/javascript">
        $(function(){
			$("input[name='store_order']").val(0);

            $(document).on('click', "[ectype='store_order']", function(){
                var i = 0;
                $("*[ectype='ckShopAll']").each(function(){
                    var t = $(this);
                    if(t.prop("checked") == true){
                        i++
                    }
                });

                if(i > 1){
                    pbDialog(json_languages.not_seller_batch_goods_num,"",0,'','',55);
                }else{
                    $("input[name='store_order']").val(1);
                    $("form[name='formCart']").submit();
                }
            });
        })
    </script>
</body>
</html>
