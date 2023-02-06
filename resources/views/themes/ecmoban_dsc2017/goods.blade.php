<!doctype html>
<html lang="zh-Hans">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="Keywords" content="{{ $keywords }}"/>
    <meta name="Description" content="{{ $description }}"/>

    <title>{{ $page_title }}</title>


    <link rel="shortcut icon" href="favicon.ico"/>
    @include('frontend::library/js_languages_new')
    <link rel="stylesheet" type="text/css" href="{{ skin('css/goods_fitting.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ skin('css/suggest.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.css') }}"/>
</head>

<body class="goods-css">
@include('frontend::library/page_header_common')
<div class="full-main-n">
    <div class="w w1200 relative">
        @include('frontend::library/ur_here')
    </div>
</div>
<div class="container">
    <div class="w w1200">
        <div class="product-info mt20">
            @include('frontend::library/goods_gallery')
            <div class="product-wrap
@if($goods['user_id'])
                    product-wrap-min
@endif
                    ">
                <form action="javascript:addToCart({{ $goods['goods_id'] }})" method="post" name="ECS_FORMBUY"
                      id="ECS_FORMBUY">

                    @if (!empty($goods['country_icon']) && $cross_border_version)
                    <div class="name" style="font-size: 12px; font-weight: normal; margin-bottom: 3px; color: #7a45e5">
                        <img src="{{$goods['country_icon']}}" width="20px" /> <em style="position: relative; top: 1px;">{{$goods['country_name']}}</em>
                    </div>
                    @endif

                    <div class="name">
@forelse ($goods['goods_label'] as $key=>$label)
<a @if($label['label_url']) href="{{ $label['label_url'] }}" target="_blank" @else href="javascript:;" style="cursor: auto;" @endif class="label_url"><img src="{{ $label['formated_label_image'] }}"></a>
@empty
@endforelse
                    {!! $goods['goods_style_name'] !!}</div>

                    @if($goods['goods_brief'])

                        <div class="newp">{{ $goods['goods_brief'] }}</div>

                    @endif


                    @if($goods['gmt_end_time'])

                        <div class="activity-title"
                             @if($promo_count >1)
                             style="display:none;"
                                @endif
                        >
                            <div class="activity-type"><i class="icon icon-promotion"></i>{{ $lang['promotion_time'] }}
                            </div>
                            <div class="sk-time-cd">
                                <div class="sk-cd-tit">{{ $lang['distance_end'] }}</div>
                                <div class="cd-time" ectype="time" data-time="{{ $goods['promote_end_time'] }}">
                                    <div class="days">00</div>
                                    <span class="split">:</span>
                                    <div class="hours">00</div>
                                    <span class="split">:</span>
                                    <div class="minutes">00</div>
                                    <span class="split">:</span>
                                    <div class="seconds">00</div>
                                </div>
                            </div>
                        </div>

                    @endif

                    <div class="summary">
                        <div class="summary-price-wrap">
                            <div class="s-p-w-wrap">
                                <div class="summary-item si-shop-price">
                                    <div class="si-tit">
                                        @if($goods['gmt_end_time'])
                                            {{ $lang['promote_price'] }}
                                        @else
                                            {{ $lang['shop_price'] }}
                                        @endif
                                    </div>
                                    <div class="si-warp">
                                        <strong class="shop-price" id="ECS_SHOPPRICE" ectype="SHOP_PRICE"></strong>
                                        <span class="price-notify" data-userid="{{ $user_id }}"
                                              data-goodsid="{{ $goods['goods_id'] }}"
                                              ectype="priceNotify">{{ $lang['price_notice'] }}</span>
                                    </div>
                                </div>


                                @if($goods['is_show_drp'] == 1 && $goods['is_drp'] == 1)

                                    <div class="summary-item si-shop-price" id="vip_register">
                                        <div class="si-warp on_vip_register">
                                            <i class="icon icon-vip"></i>
                                            <span>{{ $lang['membership_card_can_discount'] }} </span>
                                            <span class="color-red"
                                                  id="card_discount_price">{{ $goods['membership_card_discount_price_formated'] }}</span>

                                            <span class="onVipRegister">{{ $lang['immediately_opened'] }} >></span>

                                        </div>
                                    </div>

                                @endif



                                @if($cfg['show_marketprice'])

                                    <div class="summary-item si-market-price">
                                        <div class="si-tit">{{ $lang['market_prices'] }}</div>
                                        <div class="si-warp">
                                            <div class="m-price" id="ECS_MARKETPRICE"></div>
                                        </div>
                                    </div>

                                @endif


                                @if($cross_border_version)

                                    <div class="summary-item si-cross-price">
                                        <div class="si-tit">{{ $lang['import_tax'] }}</div>
                                        <div class="si-warp">
                                            <div id="import_tax"></div>
                                        </div>
                                    </div>

                                @endif

                                <div class="si-info">
                                    <div class="si-cumulative">{{ $lang['Cumulative_evaluation'] }}
                                        <em>{{ $comment_all['allmen'] }}</em></div>
                                    <div class="si-cumulative">{{ $lang['Cumulative_Sales'] }}
                                        <em>{{ $goods['sales_volume'] }}</em></div>
                                </div>

                                @if($two_code)

                                    <div class="si-phone-code">
                                        <div class="qrcode-wrap">
                                            <div class="qrcode_tit">{{ $lang['summary_phone'] }}<i
                                                        class="iconfont icon-qr-code"></i></div>
                                            <div class="qrcode_pop">
                                                <div class="mobile-qrcode"><img src="{{ $weixin_img_url }}"
                                                                                alt="{{ $lang['two_code'] }}"
                                                                                title="{{ $weixin_img_text }}"></div>
                                            </div>
                                        </div>
                                    </div>

                                @endif


                                @if($goods_coupons)

                                    <div class="summary-item si-coupon">
                                        <div class="si-tit">{{ $lang['Collar_ticket_alt'] }}</div>
                                        <div class="si-warp">

                                            @foreach($goods_coupons as $vo)

                                                <a class="J-open-tb" href="#none" data-goodsid="{{ $goods_id }}">
                                                    <div class="quan-item"><i
                                                                class="i-left"></i>{{ $lang['man'] }}{{ $vo['cou_man'] }}{{ $lang['minus'] }}{{ $vo['cou_money'] }}
                                                        <i class="i-right"></i></div>
                                                </a>

                                            @endforeach

                                        </div>
                                    </div>

                                @endif



                                @if($promotion || $goods['consumption'])

                                    <div class="summary-item si-promotion"
                                         @if($promo_count >2)
                                         ectype="view-prom"
                                         @endif

                                         @if($promo_count <2)
                                         style="height:24px;"
                                            @endif
                                    >
                                        <div class="si-tit">{{ $lang['promotion_alt'] }}</div>
                                        <div class="si-warp">
                                            <div class="prom-items">

                                                @foreach($promotion as $key => $item)


                                                    @if($item['type'] == "favourable")

                                                        <div class="prom-item">

                                                            @if($item['act_type'] == 0)

                                                                <em class="prom-tip">{{ __('common.With_a_gift') }}</em>

                                                            @elseif ($item['act_type'] == 1)

                                                                <em class="prom-tip">{{ $lang['Stand_minus'] }}</em>

                                                            @elseif ($item['act_type'] == 2)

                                                                <em class="prom-tip">{{ $lang['discount'] }}</em>

                                                            @endif

                                                            {{ $item['act_name'] }}
                                                        </div>

                                                    @elseif ($item['type'] == "group_buy")

                                                        <div class="prom-item">
                                                            <a href="{!! $item['url'] !!}"
                                                               title="{{ $lang['group_buy'] }}" class="prom-tip"
                                                               title="{{ $lang['group_buy'] }}">{{ $lang['group_buy'] }}</a>
                                                        </div>

                                                    @elseif ($item['type'] == "auction")

                                                        <div class="prom-item">
                                                            <a href="{!! $item['url'] !!}" title="{{ $lang['auction'] }}"
                                                               class="prom-tip"
                                                               title="{{ $lang['auction'] }}">{{ $lang['auction'] }}</a>
                                                        </div>

                                                    @elseif ($item['type'] == "snatch")

                                                        <div class="prom-item">
                                                            <a href="{!! $item['url'] !!}" title="{{ $lang['snatch'] }}"
                                                               class="prom-tip"
                                                               title="{{ $lang['snatch'] }}">{{ $lang['snatch'] }}</a>
                                                        </div>

                                                    @endif


                                                @endforeach


                                                @if($goods['consumption'])

                                                    <div class="prom-item">
                                                        <a href="javascript:;" class="prom-tip"
                                                           title="{{ $lang['Full_reduction'] }}">{{ $lang['Full_reduction'] }}</a>
                                                        <em class="h1_red" title="
@foreach($goods['consumption'] as $con)
                                                        {{ $lang['man'] }}{{ $con['cfull'] }}{{ $lang['minus'] }}{{ $con['creduce'] }}
                                                        @if(!$loop->last)
                                                                ，
@endif

                                                        @endforeach
                                                                ">
                                                            @foreach($goods['consumption'] as $con)
                                                                {{ $lang['man'] }}{{ $con['cfull'] }}{{ $lang['minus'] }}{{ $con['creduce'] }}
                                                                @if(!$loop->last)
                                                                    ，
                                                                @endif

                                                            @endforeach
                                                        </em>
                                                    </div>

                                                @endif

                                            </div>

                                            @if($promo_count >2)

                                                <div class="view-all-promotions">
                                                    <span class="prom-sum">{{ $lang['total'] }}<em
                                                                class="prom-number">{{ $promo_count }}</em>{{ $lang['Item_promotion'] }}</span>
                                                    <i class="iconfont icon-down"></i>
                                                </div>

                                            @endif

                                        </div>
                                    </div>

                                @endif

                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="summary-basic-info">

                            @if($volume_price_list )

                                <div class="summary-item is-ladder">
                                    <div class="si-tit">{{ $lang['Preferential_steps'] }}</div>
                                    <div class="si-warp">
                                        <a href="javascript:void(0);" class="link-red"
                                           ectype="view_priceLadder">{{ $lang['View_price_ladder'] }}</a>
                                        <dl class="priceLadder" ectype="priceLadder">
                                            <dt>
                                                <span>{{ $lang['number_to'] }}</span>
                                                <span>{{ $lang['preferences_price'] }}</span>
                                            </dt>

                                            @foreach($volume_price_list as $price_key => $price_list)

                                                <dd>
                                                    <span>{{ $price_list['number'] }}</span>
                                                    <span>{{ $price_list['format_price'] }}</span>
                                                </dd>

                                            @endforeach

                                        </dl>
                                    </div>
                                </div>

                            @endif



                            @if($rank_prices && $cfg['show_rank_price'] && !$goods['gmt_end_time'])

                                <div class="summary-item is-ladder">
                                    <div class="si-tit">{{ $lang['rank_prices'] }}</div>
                                    <div class="si-warp">
                                        <a href="javascript:void(0);" class="link-red"
                                           ectype="view_priceLadder">{{ $lang['View_price_ladder'] }}</a>
                                        <dl class="priceLadder goods_rank_prices" ectype="priceLadder">
                                            <dt>
                                                <span>{{ $lang['rank'] }}</span>
                                                <span>{{ $lang['prices'] }}</span>
                                            </dt>

                                            @foreach($rank_prices as $price_key => $rank)

                                                <dd>
                                                    <span>{{ $rank['rank_name'] }}</span>
                                                    <span>{{ $rank['price'] }}</span>
                                                </dd>

                                            @endforeach

                                        </dl>
                                    </div>
                                </div>

                            @endif


                            <div class="summary-item is-stock">
                                <div class="si-tit">{{ $lang['distribution'] }}</div>
                                <div class="si-warp">
                                        <span class="initial-area">

@if($adress['city'])

                                                {{ $adress['city'] }}

                                            @else

                                                {{ $basic_info['city'] }}

                                            @endif

                                        </span>
                                    <span>{{ $lang['as_for'] }}</span>
                                    <div class="store-selector">
                                        <div class="text-select" id="area_address" ectype="areaSelect"></div>
                                    </div>
                                    <div class="store-warehouse">
                                        <div class="store-warehouse-info"></div>
                                        <div id="isHas_warehouse_num" class="store-prompt"></div>
                                    </div>
                                </div>
                            </div>

                                @if($goods['cross_warehouse_name'])
                            <div class="summary-item is-service">
                                <div class="si-tit">{{ $lang['deliver_goods'] }}</div>
                                <div class="si-warp">
                                    {{ $goods['cross_warehouse_name']  }}
                                </div>
                            </div>
                                @endif

                            <div class="summary-item is-service">
                                <div class="si-tit">{{ $lang['service'] }}</div>
                                <div class="si-warp">
                                    <div class="fl">

                                        @if($goods['user_id'] > 0)

                                            {{ $lang['you'] }} <a href="{!! $goods['store_url'] !!}" class="link-red" target="_blank">{{ $goods['rz_shop_name'] }}</a> {{ $lang['After_sale_service'] }}

                                        @else

                                            {{ $lang['you'] }} <a href="javascript:void(0)" class="link-red">{{ $goods['rz_shop_name'] }}</a> {{ $lang['After_sale_service'] }}

                                        @endif

                                    </div>
                                    <div class="fl pl10" id="user_area_shipping"></div>
                                </div>
                            </div>

                            @if($cfg['show_brand'] && $goods['brand_name'])

                                <div class="summary-item is-brand">
                                    <div class="si-tit">{{ $lang['brand'] }}</div>
                                    <div class="si-warp"><a href="{!! $goods['goods_brand_url']!!}" target="_blank">{{ $goods['brand_name'] }}</a></div>
                                </div>

                            @endif


                            @if($cfg['use_integral'])

                                <div class="summary-item is-integral">
                                    <div class="si-tit">{{ $lang['keyong_integral'] }}</div>
                                    <div class="si-warp"><span class="integral">{{ $goods['integral'] }}</span></div>
                                </div>

                            @endif


                            @if($goods['give_integral'] && $cfg['show_give_integral'])

                                <div class="summary-item is-integral">
                                    <div class="si-tit">{{ $lang['is_integral'] }}</div>
                                    <div class="si-warp"><span class="integral">{{ $goods['give_integral'] }}</span>
                                    </div>
                                </div>

                            @endif

                            @if($goods['is_xiangou'] == 1 && $xiangou == 1)

                                <div class="summary-item is-xiangou">
                                    <div class="si-tit">{{ $lang['limit_shop'] }}</div>
                                    <div class="si-warp">
                                        <em id="restrict_amount" ectype="restrictNumber"
                                            data-value="{{ $goods['xiangou_num'] }}">{{ $goods['xiangou_num'] }}</em>
                                        <span>
@if($goods['goods_unit'])
                                                {{ $goods['goods_unit'] }}
                                            @elseif ($goods['measure_unit'])
                                                {{ $goods['measure_unit'] }}
                                            @endif
</span>
                                        <span>（{{ $lang['Already_buy'] }}：<em id="orderG_number" ectype="orderGNumber" data-value="{{ $orderG_number ?? 0 }}">{{ $orderG_number ?? 0 }}</em>
                                            @if($goods['goods_unit'])
                                                {{ $goods['goods_unit'] }}
                                            @elseif ($goods['measure_unit'])
                                                {{ $goods['measure_unit'] }}
                                            @endif
                                            ）</span>
                                    </div>
                                </div>

                            @endif



                            @if($goods['is_minimum'] == 1)

                                <div class="summary-item is_minimum">
                                    <div class="si-tit">{{ $lang['minimum'] }}</div>
                                    <div class="si-warp">
                                        <em id="restrict_amount" ectype="minimumNumber"
                                            data-value="{{ $goods['minimum'] }}">{{ $goods['minimum'] }}</em>
                                        <span>
@if($goods['goods_unit'])
                                                {{ $goods['goods_unit'] }}
                                            @elseif ($goods['measure_unit'])
                                                {{ $goods['measure_unit'] }}
                                            @endif
</span>
                                    </div>
                                </div>

                            @endif


                            <div class="summary-item is-since" ectype="list-store-pick" style="display:none">
                                <div class="si-tit">{{ $lang['self_lifting'] }}</div>
                                <div class="si-warp">
                                    <a href="javascript:void(0);" ectype="seller_store" class="link-red"><i
                                                class="iconfont icon-store-alt"></i>{{ $lang['select_store_info'] }}</a>
                                    <span class="ml10">{{ $lang['select_store_info_notic'] }}</span>
                                </div>
                            </div>


                            @if($specification)


                                @foreach($specification as $spec_key => $spec)


                                    @if($spec['values'])

                                        <div class="summary-item is-attr goods_info_attr" ectype="is-attr"
                                             @if($spec['attr_type'] == 1)
                                             data-type="radio"
                                             @else
                                             data-type="checkbox"
                                                @endif
                                        >
                                            <div class="si-tit">{{ $spec['name'] }}</div>

                                            @if($cfg['goodsattr_style'] == 1)

                                                <div class="si-warp">
                                                    <ul>

                                                        @foreach($spec['values'] as $key => $value)


                                                            @if($spec['is_checked'] > 0)

                                                                <li class="item
@if($value['checked'] == 1)
                                                                        selected
@endif
                                                                        " data-name="{{ $value['id'] }}">
                                                                    <b></b>
                                                                    <a

@if($value['attr_img_site'])
href="{{ $value['attr_img_site'] }}"
@else
href="javascript:void(0);"
@endif

                                                                    >

                                                                        @if($value['img_flie'])

                                                                            <img src="{{ $value['img_flie'] }}"
                                                                                 width="24" height="24"/>

                                                                        @endif

                                                                        <i>{{ $value['label'] }}</i>
                                                                        <input id="spec_value_{{ $value['id'] }}"
                                                                               @if($spec['attr_type'] == 2)
                                                                               type="checkbox"
                                                                               @else
                                                                               type="radio"
                                                                               @endif

                                                                               @if($spec['attr_type'] == 2)
                                                                               data-attrtype="2"
                                                                               @else
                                                                               data-attrtype="1"
                                                                               @endif
                                                                               name="spec_{{ $spec_key }}"
                                                                               value="{{ $value['id'] }}"
                                                                               autocomplete="off" class="hide"/>

                                                                        @if($value['checked'] == 1)

                                                                            <script type="text/javascript">
                                                                                $(function () {
                                                                                    $("#spec_value_{{ $value['id'] }}").prop("checked", true);
                                                                                });
                                                                            </script>

                                                                        @endif

                                                                    </a>
                                                                </li>

                                                            @else

                                                                <li class="item
@if($key == 0)
                                                                        selected
@endif
                                                                        " data-name="{{ $value['id'] }}">
                                                                    <b></b>
                                                                    <a href="javascript:void(0);"
                                                                       name="{{ $value['id'] }}" class="noimg">
                                                                        <i>{{ $value['label'] }}</i>
                                                                        <input id="spec_value_{{ $value['id'] }}"
                                                                               @if($spec['attr_type'] == 2)
                                                                               type="checkbox"
                                                                               @else
                                                                               type="radio"
                                                                               @endif

                                                                               @if($spec['attr_type'] == 2)
                                                                               data-attrtype="2"
                                                                               @else
                                                                               data-attrtype="1"
                                                                               @endif
                                                                               name="spec_{{ $spec_key }}"
                                                                               value="{{ $value['id'] }}"
                                                                               autocomplete="off" class="hide"/></a>

                                                                    @if($key == 0)

                                                                        <script type="text/javascript">
                                                                            $(function () {
                                                                                $("#spec_value_{{ $value['id'] }}").prop("checked", true);
                                                                            });
                                                                        </script>

                                                                        @endif

                                                                        </a>
                                                                </li>

                                                            @endif


                                                        @endforeach

                                                    </ul>
                                                </div>

                                            @else

                                                ...

                                            @endif

                                            <input type="hidden" name="spec_list" value="{{ $spec_key }}"
                                                   @if($spec['attr_type'] == 1)
                                                   data-spectype="attr-radio"
                                                   @else
                                                   data-spectype="attr-check"
                                                    @endif
                                            />
                                        </div>

                                    @endif


                                @endforeach


                            @endif



                            @if($stages)

                                <div class="summary-item is-ious" ectype="is-ious">
                                    <div class="si-tit">{{ $lang['biaotiao'] }}</div>
                                    <div class="si-warp">

                                        @foreach($stages as $k => $vo)


                                            @if($k == 1)

                                                <div class="item" data-value="{{ $k }}">
                                                    <b></b>
                                                    <a href="javascript:void(0);"><i>{{ $lang['noFree_30'] }}</i></a>
                                                    <div class="baitiao-tips">
                                                        <span>{{ $lang['nofree'] }}</span>
                                                    </div>
                                                </div>

                                            @else

                                                <div class="item" data-value="{{ $k }}">
                                                    <b></b>
                                                    <a href="javascript:void(0);"><i>{{ config('shop.currency_format', '¥') }}{{ $vo['stages_one_price'] }}
                                                            ×{{ $k }}{{ $lang['qi'] }}</i></a>
                                                    <div class="baitiao-tips">
                                                        <span>{{ $lang['free_desc'] }}{{ $goods['stages_rate'] }}
                                                            %，{{ config('shop.currency_format', '¥') }}{{ $vo['stages_one_price'] }}
                                                            ×{{ $k }}{{ $lang['qi'] }}</span>
                                                    </div>
                                                </div>

                                            @endif


                                        @endforeach

                                        <div class="bt-info-tips">
                                            <a href="javascript:void(0);"><i class="question"></i></a>
                                            <div class="tips">
                                                <div class="sprite-arrow"></div>
                                                <div class="content">
                                                    <p>{{ $lang['bt_stages_one'] }}</p>
                                                    <p>{{ $lang['bt_stages_two'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="stages_qishu" value=""/>
                                    </div>
                                </div>

                            @endif

                            <div class="summary-item is-number">
                                <div class="si-tit">{{ $lang['number_to'] }}</div>
                                <div class="si-warp">
                                    <div class="amount-warp">
                                        <input class="text buy-num" ectype="quantity"
@if($goods['is_minimum'] == 1)
value="{{ $goods['minimum'] }}"
@else
value="1"
@endif name="number" defaultnumber="1">
                                        <div class="a-btn">
                                            <a href="javascript:void(0);" class="btn-add" ectype="btnAdd"><i
                                                        class="iconfont icon-up"></i></a>
                                            <a href="javascript:void(0);" class="btn-reduce btn-disabled"
                                               ectype="btnReduce"><i class="iconfont icon-down"></i></a>
                                            <input type="hidden" name="perNumber" id="perNumber" ectype="perNumber"
                                                   value="0">
                                            <input type="hidden" name="perMinNumber" id="perMinNumber"
                                                   ectype="perMinNumber" value="
@if($goods['is_minimum'] == 1)
                                            {{ $goods['minimum'] }}
                                            @else
                                                    1
@endif
                                                    ">
                                        </div>
                                        <input name="confirm_type" id="confirm_type" type="hidden" value="3"/>
                                    </div>

                                    @if($cfg['show_goodsnumber'])

                                        <span>{{ $lang['goods_inventory'] }}&nbsp;<em id="goods_attr_num"
                                                                                      ectype="goods_attr_num"></em>&nbsp;
                                            @if($goods['goods_unit'])
                                                {{ $goods['goods_unit'] }}
                                            @else
                                                {{ $goods['measure_unit'] }}
                                            @endif
</span>

                                    @endif

                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>

                        @if($goods['goods_tag'])

                            <div class="summary-basic-info">
                                <div class="summary-item is-service">
                                    <div class="si-tit">{{ $lang['service_promise'] }}</div>
                                    <div class="si-warp">

                                        @foreach($goods['goods_tag'] as $tag)

                                            <span class="tary">{{ $tag }}</span>

                                        @endforeach

                                    </div>
                                </div>
                            </div>

                        @endif

                        <div class="summary-basic-info">
                            <div class="summary-item is-service" style="margin-bottom: 20px;">
                                <div class="si-tit">{{ $lang['reminder'] }}</div>
                                <div class="si-warp gray" style="">

                                    @if($goods['is_alone_sale'] == 0)

                                        <p class="local-txt">{{ $lang['is_alone_sale_notic'] }}</p>
                                    @else

                                        @forelse ($goods['goods_services_label'] as $key=>$label)
                                            <a herf="javascript:;" style="cursor: auto;" class="label_url label_wrap @if(!$loop->last)label_wrap_spacing @endif"><img src="{{ $label['formated_label_image'] }}">{{ $label['label_name'] }}</a>
                                        @empty
                                        @endforelse

                                    @endif

                                </div>
                            </div>
                        </div>

                    </div>
                    <style>
                        .label_wrap img {
                            width: 16px;
                            height: 16px;
                            margin-right: 5px;
                            vertical-align: text-top;
                        }
                        .label_wrap {
                            position: relative;
                            padding-right: 15px;
                        }
                        .label_wrap_spacing:after {
                            position: absolute;
                            top: 50%;
                            right: 5px;
                            transform: translateY(-50%);
                            content: '';
                            width: 2px;
                            height: 2px;
                            border-radius: 50%;
                            background-color: #999;
                        }
                    </style>
                        <div class="choose-btns
@if(!$area_position_list || $goods['store_count'] <= 0 || !$stages || $goods['is_on_sale'] == 0 )
                                ml60
@endif
                                clearfix" style="display: none;">

                            @if($shop_close == 0 && $goods['user_id'] != 0)

                                <a class="btn-invalid" href="javascript:void(0);">{{ $lang['shop_close'] }}</a>

                            @else


                                @if($goods['is_alone_sale'] == 0)

                                    <a href="category.php?id=347" class="btn-buynow">{{ $lang['is_alone_sale'] }}</a>

                                @else


                                    @if($goods['review_status'] <= 2 || $goods['is_on_sale'] == 0)

                                        <a id="sold_out" class="btn-invalid"
                                           href="javascript:void(0);">{{ $lang['is_on_sale'] }}</a>

                                    @else


                                        @if($goods_area == 1)

                                            <a href="
@if($user_id <= 0 && $one_step_buy)
                                                    #none
@else
                                                    javascript:bool=0;buynow=1;addToCart({{ $goods['goods_id'] }})
@endif
                                                    " data-type="{{ $one_step_buy }}" class="btn-buynow"
                                               ectype="btn-buynow">{{ $lang['button_buy'] }}</a>

                                            @if(!$one_step_buy)

                                                <a href="javascript:bool=0;addToCartShowDiv({{ $goods['goods_id'] }})"
                                                   class="btn-append" ectype="btn-append"><i
                                                            class="iconfont icon-carts"></i>{{ $lang['btn_add_to_cart'] }}
                                                </a>

                                            @endif



                                            @if($stages)

                                                <a href="javascript:void(0);" class="btn-baitiao"
                                                   ectype="byStages">{{ $lang['Installment_purchase'] }}</a>

                                            @endif



                                        @else

                                            <a id="no_addToCart" class="btn-invalid btn_disabled"
                                               href="javascript:void(0);">{{ $lang['temporarily_stock'] }}</a>

                                        @endif


                                    @endif


                                @endif


                            @endif

                            <a href="javascript:void(0);" class="btn-since" ectype="btn-store-pick"
                               style="display:none">{{ $lang['btn_store_pick'] }}</a>


                            @if($goods['is_show_drp'] == 1 && $goods['is_drp'] == 1 && $goods['commission_money'] > 0)

                                <a href="javascript:void(0);" class="copy-link">{{ $lang['copy_url'] }}</a>

                            @endif


                        </div>

                    <input type="hidden" value="{{ $goods['goods_id'] }}" name="goods_id"/>
                    <input type="hidden" value="{{ $goods_id }}" id="good_id" name="good_id"/>
                    <input type="hidden" value="{{ $area['user_id'] }}" id="user_id" name="user_id"/>
                    <input type="hidden" value="{{ $area['street_list'] ?? 0 }}" name="street_list"/>
                    <input type="hidden" value="{{ $xiangou }}" name="restrictShop" ectype="restrictShop"/>
                    <input type="hidden" value="{{ $cfg['add_shop_price'] }}" name="add_shop_price"
                           ectype="add_shop_price"/>
                    @csrf </form>
            </div>

            @if(!$goods['user_id'])

                <div class="track" id="see_more_goods">

                </div>

            @else

                <div class="seller-pop">
                    <div class="seller-logo">

                        @if($goods['shopinfo']['brand_thumb'])

                            <a href="{{ $goods['store_url'] }}" target="_blank"><img
                                        src="{{ $goods['shopinfo']['brand_thumb'] }}"/></a>

                        @else

                            <a href="{{ $goods['goods_brand_url'] }}" target="_blank">{{ $goods['goods_brand'] }}</a>

                        @endif

                    </div>
                    <div class="seller-info">
                        <a href="{{ $goods['store_url'] }}" title="{{ $goods['rz_shop_name'] }}" target="_blank"
                           class="name">{{ $goods['rz_shop_name'] }}</a>
                        <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goods['user_id'] }}"
                           goods_id="{{ $goods['goods_id'] }}"><i class="icon i-kefu"></i></a>
                    </div>

                    @if($goods['shopinfo']['self_run'])

                        <div class="seller-sr">{{ $lang['platform_self'] }}</div>

                    @endif

                    <div class="seller-pop-box">
                        <div class="s-score">
                            <span class="score-icon"><span class="score-icon-bg"
                                                           style="width:{{ $merch_cmt['cmt']['all_zconments']['allReview'] }}%;"></span></span>
                            <span>{{ $merch_cmt['cmt']['all_zconments']['score'] }}</span>
                            <i class="iconfont icon-down"></i>
                        </div>
                        <div class="g-s-parts">
                            <div class="parts-item parts-goods">
                                <span class="col1">{{ $lang['evaluation_single'] }}</span>
                                <span class="col2
@if($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 1)
                                        ftx-01
@elseif ($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 2)
                                        average
@else
                                        ftx-02
@endif
                                        ">{{ $merch_cmt['cmt']['commentRank']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 1)
                                            up
@elseif ($merch_cmt['cmt']['commentRank']['zconments']['is_status'] == 2)
                                            average
@else
                                            down
@endif
                                            "></i></span>
                            </div>
                            <div class="parts-item parts-goods">
                                <span class="col1">{{ $lang['service_attitude'] }}</span>
                                <span class="col2
@if($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 1)
                                        ftx-01
@elseif ($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 2)
                                        average
@else
                                        ftx-02
@endif
                                        ">{{ $merch_cmt['cmt']['commentServer']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 1)
                                            up
@elseif ($merch_cmt['cmt']['commentServer']['zconments']['is_status'] == 2)
                                            average
@else
                                            down
@endif
                                            "></i></span>
                            </div>
                            <div class="parts-item parts-goods">
                                <span class="col1">{{ $lang['delivery_speed'] }}</span>
                                <span class="col2
@if($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 1)
                                        ftx-01
@elseif ($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 2)
                                        average
@else
                                        ftx-02
@endif
                                        ">{{ $merch_cmt['cmt']['commentDelivery']['zconments']['score'] }}<i class="iconfont icon-arrow-
@if($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 1)
                                            up
@elseif ($merch_cmt['cmt']['commentDelivery']['zconments']['is_status'] == 2)
                                            average
@else
                                            down
@endif
                                            "></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="seller-qrcode">
                        <img src="{{ $shop_qrcode }}">
                        <p>{{ $lang['wechat_scan_shop'] }}</p>
                    </div>
                    <div class="seller-item">
                        <a href="javascript:void(0);" ectype="collect_store" data-type='goods'
                           data-value='{"userid":{{ $user_id }},"storeid":{{ $goods['user_id'] }}}' class="gz-store"
                           data-url="{{ $goods['goods_url'] }}"></a>
                        <a href="{{ $goods['store_url'] }}" class="store-home"><i
                                    class="iconfont icon-home-store"></i>{{ $lang['seller_store'] }}</a>
                        <input type="hidden" name="error" value="{{ $goods['error'] }}" id="error"/>
                    </div>
                    <div class="seller-tel"><i class="iconfont icon-tel"></i>
                        @if($shop_information['kf_tel'])
                            {{ $shop_information['kf_tel'] }}
                        @else
                            {{ $lang['kf_tel_zanwu'] }}
                        @endif
                    </div>
                </div>

            @endif

            <div class="clear"></div>
        </div>
        @include('frontend::library/goods_fittings')
        <div class="goods-main-layout">
            <div class="g-m-left">
                @include('frontend::library/goods_merchants')

                @if($basic_info['kf_type'] == 1)


                    @if($basic_info['kf_ww_all'])

                        <div class="g-main service_list">
                            <div class="mt"><h3>{{ $lang['store_Customer_service'] }}</h3></div>
                            <div class="mc">
                                <ul>

                                    @foreach($basic_info['kf_ww_all'] as $kf_ww)

                                        <li>
                                            <a href="http://www.taobao.com/webww/ww.php?ver=3&touid={{ $kf_ww['1'] }}&siteid=cntaobao&status=1&charset=utf-8"
                                               target="_blank"><i
                                                        class="icon_service_ww"></i><span>{{ $kf_ww['0'] }}</span></a>
                                        </li>

                                    @endforeach

                                </ul>
                            </div>
                        </div>

                    @endif


                @else


                    @if($basic_info['kf_qq_all'])

                        <div class="g-main service_list">
                            <div class="mt"><h3>{{ $lang['store_Customer_service'] }}</h3></div>
                            <div class="mc">
                                <ul>

                                    @foreach($basic_info['kf_qq_all'] as $kf_qq)

                                        <li class="service_qq"><a
                                                    href="http://wpa.qq.com/msgrd?v=3&uin={{ $kf_qq['1'] }}&site=qq&menu=yes"
                                                    target="_blank"><i
                                                        class="icon i-kefu"></i><span>{{ $kf_qq['0'] }}</span></a></li>

                                    @endforeach

                                </ul>
                            </div>
                        </div>

                    @endif


                @endif



                @if($goods_store_cat)

                    <div class="g-main g-store-cate">
                        <div class="mt">
                            <h3>{{ $lang['Store_cat'] }}</h3>
                        </div>
                        <div class="mc">
                            <div class="g-s-cate-warp">

                                @foreach($goods_store_cat as $key => $cat)

                                    <dl
                                            @if($cat['cat_id'])
                                            ectype="cateOpen"
                                            @else
                                            class="hover"
                                            @endif
                                    >
                                        <dt><i class="icon"></i><a href="{{ $cat['url'] }}"
                                                                   target="_blank">{{ $cat['name'] }}</a></dt>

                                        @if($cat['cat_id'])

                                            <dd>

                                                @foreach($cat['cat_id'] as $key => $cat)

                                                    <a href="{{ $cat['url'] }}" class="a-item"
                                                       target="_blank">&gt; {{ $cat['name'] }}</a>

                                                @endforeach

                                            </dd>

                                        @endif

                                    </dl>

                                @endforeach

                            </div>
                        </div>
                    </div>

                @endif



                @if($goods_related_cat)

                    <div class="g-main">
                        <div class="mt">
                            <h3>{{ $lang['Relevant_cat'] }}</h3>
                        </div>
                        <div class="mc">
                            <div class="mc-warp">
                                <div class="items">

                                    @foreach($goods_related_cat as $cat)


                                        @if($loop->iteration < 11)

                                            <div class="item"><a href="{{ $cat['url'] }}"
                                                                 target="_blank">{{ $cat['cat_name'] }}</a></div>

                                        @endif


                                    @endforeach

                                </div>
                            </div>
                        </div>
                    </div>

                @endif



                @if($goods_brand)

                    <div class="g-main">
                        <div class="mt">
                            <h3>{{ $lang['Other_brands'] }}</h3>
                        </div>
                        <div class="mc">
                            <div class="mc-warp">
                                <div class="items">

                                    @foreach($goods_brand as $brand)

                                        <div class="item"><a href="{{ $brand['url'] }}"
                                                             target="_blank">{{ $brand['brand_name'] }}</a></div>

                                    @endforeach

                                </div>
                            </div>
                        </div>
                    </div>

                @endif



                @include('frontend::library/goods_article')

                <div class="g-main g-rank" id="goods_new_best_hot" style="display: none;">
                    <div class="mc">
                        <ul class="mc-tab" ectype="rankMcTab">
                            <li class="curr" id="title_goods_new"
                                style="display: none;">{{ $lang['new_product']  }}</li>
                            <li id="title_goods_best" style="display: none;">{{ $lang['Selling']  }}</li>
                            <li id="title_goods_hot" style="display: none;">{{ $lang['intro_type']  }}</li>
                        </ul>
                        <div class="mc-content">
                            <div id="recommend_new_goods">
                            </div>

                            <div id="recommend_best_goods">
                            </div>

                            <div id="recommend_hot_goods">
                            </div>
                        </div>
                    </div>
                </div>

                {!! insert_history_goods(['goods_id' => 0, 'warehouse_id' => $region_id, 'area_id' => $area_id, 'area_city' => $area_city]) !!}


                @include('frontend::library/goods_related')

            </div>
            <div class="g-m-detail">
                <div class="gm-tabbox" ectype="gm-tabs">
                    <ul class="gm-tab">
                        <li class="curr" ectype="gm-tab-item">{{ $lang['description'] }}</li>

                        @if($properties)
                            <li ectype="gm-tab-item-spec">{{ $lang['specification'] }}</li>
                        @endif


                        @if($shop_can_comment > 0)
                            <li ectype="gm-tab-item">{{ $lang['user_comment'] }}（<em class="ReviewsCount">{{ $comment_all['allmen'] }}</em>）</li>
                            <li ectype="gm-tab-item">{{ $lang['discuss_user'] }}</li>
                        @endif

                    </ul>
                    <div class="extra">
                        <div class="item">

                            @if($two_code)

                                <div class="si-phone-code">
                                    <div class="qrcode-wrap">
                                        <div class="qrcode_tit">{{ $lang['summary_phone'] }}<i
                                                    class="iconfont icon-qr-code"></i></div>
                                        <div class="qrcode_pop">
                                            <div class="mobile-qrcode"><img src="{{ $weixin_img_url }}"
                                                                            alt="{{ $lang['two_code'] }}"
                                                                            title="{{ $weixin_img_text }}" width="175">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endif

                            <div class="inner">
                                <a href="javascript:void(0)" class="btn sc-redBg-btn" id="btn-anchor"
                                   ectype="tb-tab-anchor">{{ $lang['btn_add_to_cart'] }}</a>
                                <div class="tb-popsku">
                                    <span class="arrow-top"></span>
                                    <div class="tb-popsku-content">
                                        <div class="tb-list">
                                            <div class="tb-label">{{ $lang['price'] }}：</div>
                                            <div class="tb-value"><strong class="shop-price"
                                                                          ectype="SHOP_PRICE"></strong></div>
                                        </div>

                                        @foreach($specification as $spec_key => $spec)


                                            @if($spec['values'])

                                                <div class="tb-list is-attr goods_info_attr" ectype="is-attr"
                                                     @if($spec['attr_type'] == 1)
                                                     data-type="radio"
                                                     @else
                                                     data-type="checkbox"
                                                        @endif
                                                >
                                                    <div class="tb-label">{{ $spec['name'] }}：</div>

                                                    @if($cfg['goodsattr_style'] == 1)

                                                        <div class="tb-value">
                                                            <ul>

                                                                @foreach($spec['values'] as $key => $value)


                                                                    @if($spec['is_checked'] > 0)

                                                                        <li class="item
@if($value['checked'] == 1)
                                                                                selected
@endif
                                                                                " data-name="{{ $value['id'] }}">
                                                                            <b></b>
                                                                    <a

@if($value['attr_img_site'])
href="{{ $value['attr_img_site'] }}"
@else
href="javascript:void(0);"
@endif

@if($value['attr_img_site'])
target="_blank"
@endif

                                                                    >

                                                                                @if($value['img_flie'])

                                                                                    <img src="{{ $value['img_flie'] }}"
                                                                                         width="24" height="24"/>

                                                                                @endif

                                                                                <i>{{ $value['label'] }}</i>
                                                                                <input id="spec_value_{{ $value['id'] }}"
                                                                                       @if($spec['attr_type'] == 2)
                                                                                       type="checkbox"
                                                                                       @else
                                                                                       type="radio"
                                                                                       @endif

                                                                                       @if($spec['attr_type'] == 2)
                                                                                       data-attrtype="2"
                                                                                       @else
                                                                                       data-attrtype="1"
                                                                                       @endif
                                                                                       name="spec_{{ $spec_key }}"
                                                                                       value="{{ $value['id'] }}"
                                                                                       class="hide" autocomplete="off"/>

                                                                                @if($value['checked'] == 1)

                                                                                    <script type="text/javascript">
                                                                                        $(function () {
                                                                                            $("#spec_value_{{ $value['id'] }}").prop("checked", true);
                                                                                        });
                                                                                    </script>

                                                                                @endif

                                                                            </a>
                                                                        </li>

                                                                    @else

                                                                        <li class="item
@if($key == 0)
                                                                                selected
@endif
                                                                                " data-name="{{ $value['id'] }}">
                                                                            <b></b>
                                                                            <a href="javascript:void(0);"
                                                                               name="{{ $value['id'] }}" class="noimg">
                                                                                <i>{{ $value['label'] }}</i>
                                                                                <input id="spec_value_{{ $value['id'] }}"
                                                                                       @if($spec['attr_type'] == 2)
                                                                                       type="checkbox"
                                                                                       @else
                                                                                       type="radio"
                                                                                       @endif

                                                                                       @if($spec['attr_type'] == 2)
                                                                                       data-attrtype="2"
                                                                                       @else
                                                                                       data-attrtype="1"
                                                                                       @endif
                                                                                       name="spec_{{ $spec_key }}"
                                                                                       value="{{ $value['id'] }}"
                                                                                       autocomplete="off" class="hide"/></a>

                                                                            @if($key == 0)

                                                                                <script type="text/javascript">
                                                                                    $(function () {
                                                                                        $("#spec_value_{{ $value['id'] }}").prop("checked", true);
                                                                                    });
                                                                                </script>

                                                                                @endif

                                                                                </a>
                                                                        </li>

                                                                    @endif


                                                                @endforeach

                                                            </ul>
                                                        </div>

                                                    @else

                                                        ...

                                                    @endif

                                                    <input type="hidden" name="spec_list" value="{{ $spec_key }}"
                                                           @if($spec['attr_type'] == 1)
                                                           data-spectype="attr-radio"
                                                           @else
                                                           data-spectype="attr-check"
                                                            @endif
                                                    />
                                                </div>

                                            @endif


                                        @endforeach

                                        <div class="tb-list">
                                            <div class="tb-label">{{ $lang['number_to'] }}：</div>
                                            <div class="tb-value">
                                                <div class="amount-warp">
                                                    <input class="text buy-num" ectype="quantity"
@if($goods['is_minimum'] == 1)
value="{{ $goods['minimum'] }}"
@else
value="1"
@endif
                                                             name="number" defaultnumber="1">
                                                    <div class="a-btn">
                                                        <a href="javascript:void(0);" class="btn-add" ectype="btnAdd"><i
                                                                    class="iconfont icon-up"></i></a>
                                                        <a href="javascript:void(0);" class="btn-reduce btn-disabled"
                                                           ectype="btnReduce"><i class="iconfont icon-down"></i></a>
                                                    </div>
                                                    <input name="confirm_type" id="confirm_type" type="hidden"
                                                           value="3"/>
                                                </div>
                                                <span class="lh30 ml10">{{ $lang['goods_inventory'] }}&nbsp;<em
                                                            ectype="goods_attr_num"></em>&nbsp;
                                                    @if($goods['goods_unit'])
                                                        {{ $goods['goods_unit'] }}
                                                    @else
                                                        {{ $goods['measure_unit'] }}
                                                    @endif
</span>
                                            </div>
                                        </div>
                                        <div class="tb-list">
                                            <div class="tb-label">&nbsp;</div>
                                            <div class="tb-value">
                                                <a href="javascript:bool=0;addToCartShowDiv({{ $goods['goods_id'] }})"
                                                   class="cz-btn cz-btn-true"
                                                   ectype="btn-append">{{ $lang['assign'] }}</a>
                                                <a href="javascript:void(0);" class="cz-btn cz-btn-false"
                                                   ectype="tb-cancel">{{ $lang['is_cancel'] }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="gm-tab-qp-bort" ectype="qp-bort"></div>
                </div>
                <div class="gm-floors" ectype="gm-floors">
                    <div class="gm-f-item gm-f-details" ectype="gm-item">
                        <div class="gm-title">
                            <h3>{{ $lang['description'] }}</h3>
                        </div>
                        <div class="goods-para-list">
                            <dl class="goods-para">
                                <dd class="column"><span>{{ $lang['goods_name'] }}：{{ $goods['goods_name'] }}</span>
                                </dd>


                                @if($cfg['show_goodssn'])

                                    <dd class="column"><span>{{ $lang['Commodity_number'] }}
                                            ：{{ $goods['goods_sn'] }}</span></dd>

                                @endif

                                <dd class="column"><span>{{ $lang['seller_store'] }}：<a href="{{ $goods['store_url'] }}"
                                                                                        title="{{ $goods['rz_shop_name'] }}"
                                                                                        target="_blank">{{ $goods['rz_shop_name'] }}</a></span>
                                </dd>

                                @if($cfg['show_goodsweight'])

                                    <dd class="column"><span>{{ $lang['weight'] }}：{{ $goods['goods_weight'] }}</span>
                                    </dd>

                                @endif


                                @if($cfg['show_addtime'])

                                    <dd class="column"><span>{{ $lang['add_time'] }}{{ $goods['add_time'] }}</span></dd>

                                @endif

                            </dl>


                            @if($properties)

                                <dl class="goods-para">

                                    @foreach($properties as $key => $property_group)


                                        @foreach($property_group as $property)


                                            @if($loop->iteration < 13)

                                                <dd class="column"><span title="{{ $property['value'] }}">{{ $property['name'] }}
                                                        ：{{ $property['value'] }}</span></dd>

                                            @endif


                                        @endforeach


                                    @endforeach

                                </dl>

                            @endif


                            @if($extend_info)

                                <dl class="goods-para">

                                    @foreach($extend_info as $key => $info)

                                        <dd class="column"><span title="{{ $info }}">{{ $key }}：{{ $info }}</span></dd>

                                    @endforeach

                                </dl>

                            @endif


                            @if($properties)

                                <p class="more-par">
                                    <a href="javascript:void(0);" ectype="product-detail"
                                       class="ftx-05">{{ $lang['more_parameters'] }}>></a>
                                </p>

                            @endif

                        </div>

                        {!! $goods['goods_desc'] !!}
                    </div>

                    @if($properties)

                        <div class="gm-f-item gm-f-parameter" ectype="gm-item" id="product-detail"
                             style="display:none;">
                            <div class="gm-title">
                                <h3>{{ $lang['specification'] }}</h3>
                            </div>
                            <div class="Ptable">

                                @foreach($properties as $key => $property_group)

                                    <div class="Ptable-item">
                                        <h3>{{ $key }}</h3>
                                        <dl>

                                            @foreach($property_group as $property)

                                                <dt>{{ $property['name'] }}</dt>
                                                <dd title="{{ $property['value'] }}">{{ $property['value'] }}</dd>

                                            @endforeach

                                        </dl>
                                    </div>

                                @endforeach

                            </div>
                        </div>

                    @endif


                    @if($shop_can_comment > 0)

                        <div class="gm-f-item gm-f-comment" ectype="gm-item">
                            <div class="gm-title">
                                <h3>{{ $lang['comment_sunburn'] }}</h3>
                                {{-- DSC 提醒您：动态载入goods_comment_title.lbi，显示首页分类小广告 --}}
                                {!! insert_goods_comment_title(['goods_id' => $goods['goods_id']]) !!}
                            </div>
                            <div class="gm-warp">
                                <div class="praise-rate-warp">
                                    <div class="rate">
                                        <strong>{{ $comment_all['goodReview'] }}</strong>
                                        <span class="rate-span">
                                            <span class="tit">{{ $lang['Rate_praise'] }}</span>
                                            <span class="bf">%</span>
                                        </span>
                                    </div>
                                    <div class="actor-new">

                                        @if($goods['impression_list'])

                                            <dl>
                                                <dd ectype="comment_tag" data-type="1"><span
                                                            class="red">{{ $lang['all_attribute'] }}</span></dd>

                                                @foreach($goods['impression_list'] as $tag)

                                                    <dd ectype="comment_tag">
                                                        <span>{{ $tag['txt'] }}</span>({{ $tag['num'] }})
                                                    </dd>

                                                @endforeach

                                            </dl>

                                        @else

                                            <div class="not_impression">{{ $lang['not_impression'] }}</div>

                                        @endif

                                    </div>
                                </div>
                                <div class="com-list-main">
                                    @include('frontend::library/comments')
                                </div>
                            </div>
                        </div>
                        <div class="gm-f-item gm-f-tiezi" ectype="gm-item">
                            {{-- DSC 提醒您：动态载入goods_discuss_title.lbi，显示首页分类小广告 --}}
                            {!! insert_goods_discuss_title(['goods_id' => $goods['goods_id']]) !!}
                            <div class="table" id='discuss_list_ECS_COMMENT'></div>
                        </div>

                    @endif

                </div>
            </div>
            <div class="clear"></div>
            <div class="rection" id="guess_goods_love">
            </div>
        </div>
    </div>

    <div class="dialogbox hidden">

        <div id="notify_box" class="hide">
            <div class="sale-notice">
                <div class="prompt">{{ $lang['price_notice_desc'] }}</div>
                <div class="user-form foreg-form">
                    <div class="form-row">
                        <div class="form-label"><em class="red">*</em>{{ $lang['Price_below'] }}：</div>
                        <div class="form-value">
                            <input type="text" id="price-notice" name="price-notice" class="form-input w120 fl"
                                   onkeyup="notifyKeyup(this)">
                            <div class="notic mr10">{{ $lang['Price_below_one'] }}</div>
                            <div class="error"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><em class="red">*</em>{{ $lang['label_mobile'] }}</div>
                        <div class="form-value">
                            <input type="text" class="form-input" id="cellphone" name="cellphone"
                                   onkeyup="notifyKeyup(this)">
                            <div class="error"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><em class="red">*</em>{{ $lang['website_email'] }}：</div>
                        <div class="form-value">
                            <input type="text" class="form-input" id="user_email_notice" name="email"
                                   onkeyup="notifyKeyup(this)">
                            <div class="error"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="ecsc-cart-popup" id="addtocartdialog">
            <div class="loading-mask"></div>
            <div class="loading">
                <div class="center_pop_txt">
                    <div class="title"><h3>{{ $lang['Prompt'] }}</h3><a href="javascript:loadingClose();"
                                                                        title="{{ $lang['close'] }}"
                                                                        class="loading-x">X</a></div>
                </div>
                <div class="btns">
                    <a href="flow.php" class="ecsc-btn-mini ecsc-btn-orange">{{ $lang['go_pay'] }}</a>
                    <a href="javascript:loadingClose();" class="ecsc-btn-mini">{{ $lang['go_shopping'] }}</a>
                </div>
            </div>
        </div>
    </div>


    @include('frontend::library/duibi')


    @include('frontend::library/goods_fittings_cnt')
</div>

{{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position(['ru_id' => $goods['user_id']]) !!}

@include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/compare.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/magiczoomplus.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
<script type="text/javascript" src="{{ url('/') }}/calendar.php?lang={{ $cfg_lang }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/region.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/goodsFittings.js') }}"></script>

{{-- DSC 提醒您：动态载入goods_delivery_area_js.lbi，显示首页分类小广告 --}}
{!! insert_goods_delivery_area_js(['area' => $area]) !!}
<script type="text/javascript">

    //商品详情
    goods_desc_floor();

    //商品相册小图滚动
    $(".spec-list").slide({
        mainCell: ".spec-items ul",
        effect: "left",
        trigger: "click",
        pnLoop: false,
        autoPage: true,
        scroll: 1,
        vis: 5,
        prevCell: ".spec-prev",
        nextCell: ".spec-next"
    });

    //右侧看了又看上下滚动
    $(".track_warp").slide({
        mainCell: ".track-con ul",
        effect: "top",
        pnLoop: false,
        autoPlay: false,
        autoPage: true,
        prevCell: ".sprite-up",
        nextCell: ".sprite-down",
        vis: 3
    });

    //商品搭配切换
    $(".combo-inner").slide({
        titCell: ".tab-nav li",
        mainCell: ".tab-content",
        titOnClassName: "curr",
        trigger: "click"
    });

    //商品搭配 多个商品滚动切换
    $(".combo-items").slide({
        mainCell: ".combo-items-warp ul",
        effect: "left",
        pnLoop: false,
        autoPlay: false,
        autoPage: true,
        prevCell: ".o-prev",
        nextCell: ".o-next",
        vis: 4
    });

    //左侧新品 热销 推荐排行切换
    $(".g-rank").slide({titCell: ".mc-tab li", mainCell: ".mc-content", titOnClassName: "curr", trigger: "click"});


    @if($goods['gmt_end_time'])

    //促销价格倒计时
    $(".cd-time").each(function () {
        $(this).yomi();
    });

    @endif


    //全局变量
    var seller_id = {{ $goods['user_id'] ?? 0 }};
    var goods_id = {{ $goods_id ?? 0 }};
    var goodsId = {{ $goods_id ?? 0 }};
    var goodsattr_style = {{ $cfg['goodsattr_style'] ?? 1 }};
    var gmt_end_time = {{ $promote_end_time ?? 0 }};
    var now_time = {{ $now_time }};
    var isReturn = false;
    $(function () {


        @if($see_more_goods)

        seeMoreGoods();

        @endif


        if (seller_id > 0) {
            goods_collect_store(seller_id);
        }

        guessGoodsLove();

        Ajax.call('ajax_goods.php', 'act=goods_new_list&id=' + goods_id, newResponse, 'GET', 'JSON');

        Ajax.call('ajax_goods.php', 'act=goods_best_list&id=' + goods_id, bestResponse, 'GET', 'JSON');

        Ajax.call('ajax_goods.php', 'act=goods_hot_list&id=' + goods_id, hotResponse, 'GET', 'JSON');

        //对比默认加载
        Compare.init();
    });

    function newResponse(res) {

        if (res.is_show == 1) {
            $("#goods_new_best_hot").show();
            $("#title_goods_new").show();
        }

        $("#recommend_new_goods").html(res.content);
    }

    function bestResponse(res) {
        if (res.is_show == 1) {
            $("#goods_new_best_hot").show();
            $("#title_goods_best").show();
        }

        $("#recommend_best_goods").html(res.content);
    }

    function hotResponse(res) {
        if (res.is_show == 1) {
            $("#goods_new_best_hot").show();
            $("#title_goods_hot").show();
        }

        $("#recommend_hot_goods").html(res.content);
    }

    /******************************************* js方法 start***********************************************/

    var add_shop_price = $("*[ectype='add_shop_price']").val();

    /* 点击可选属性或改变数量时修改商品价格的函数 */
    function changePrice(type) {
        var qty = $("*[ectype='quantity']").val();
        var goods_attr_id = '';
        var goods_attr = '';
        var attr_id = '';
        var attr = '';
        var region_id = $(":input[name='region_id']").val();
        var area_id = $(":input[name='area_id']").val();
        var area_city = $("input[name='area_city']").val();
        var district_id = $("input[name='district_region_id']").val();

        if (!region_id) {
            region_id = {{ $region_id ?? 0 }};
        }

        if (!area_id) {
            area_id = {{ $area_id ?? 0 }};
        }

        goods_attr_id = getSelectedAttributes(document.forms['ECS_FORMBUY']);

        if (type != 1) {
            if (add_shop_price == 0) {
                attr_id = getSelectedAttributesGroup(document.forms['ECS_FORMBUY']);
                goods_attr = '&goods_attr=' + attr_id;
            }
            Ajax.call('goods.php', 'act=price&id=' + goodsId + '&attr=' + goods_attr_id + goods_attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&district_id=' + district_id, changePriceResponse, 'GET', 'JSON');
        } else {
            attr = '&attr=' + goods_attr_id;
            Ajax.call('goods.php', 'act=price&id=' + goodsId + attr + '&number=' + qty + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&type=' + type + '&district_id=' + district_id, changePriceResponse, 'GET', 'JSON');
        }
    }

    /* 接收返回的信息 回调函数 */
    function changePriceResponse(res) {
        if (res.err_msg.length > 0) {
            pbDialog(res.err_msg, " ", 0, 450, 80, 50);
        } else {
            //商品条形码
            if ($("#bar_code").length > 0) {
                if (res.bar_code) {
                    $("#bar_code").html(res.bar_code);
                    $("#bar_code").parents(".bar_code").removeClass("hide");
                } else {
                    $("#bar_code").parents(".bar_code").addClass("hide");
                }
            }

            $("#cost-price").html(res.marketPrice_amount);

            //更新库存
            if ($("*[ectype='goods_attr_num']").length > 0) {
                $("*[ectype='goods_attr_num']").html(res.attr_number);
                $("*[ectype='perNumber']").val(res.attr_number);
            }

            //更新已购买数量
            if ($("#orderG_number").length > 0) {
                $("#orderG_number").html(res.orderG_number);
            }

            if ($("#ECS_SHOPPRICE").length > 0) {
                //市场价
                if ($("#ECS_MARKETPRICE").length > 0) {
                    $("#ECS_MARKETPRICE").html(res.result_market);
                }

                @if($cross_border_version)

                //进口税
                if ($("#import_tax").length > 0) {
                    $("#import_tax").html(res.formated_goods_rate);
                }

                @endif


                //商品价格
                if (res.type == 1) {
                    $("*[ectype='SHOP_PRICE']").html(res.result);
                } else {
                    if (add_shop_price == 1) {
                        $("*[ectype='SHOP_PRICE']").html(res.result);
                    } else {
                        if (res.show_goods == 1) {
                            $("*[ectype='SHOP_PRICE']").html(res.spec_price);
                        } else {
                            $("*[ectype='SHOP_PRICE']").html(res.result);
                        }
                    }
                }
                if (add_shop_price == 0 && res.goods_rank_prices) {
                    $(".goods_rank_prices").html(res.goods_rank_prices);
                }
                //搭配 套餐价
                var combo_shop = document.getElementsByName('combo_shopPrice[]');
                var combo_mark = document.getElementsByName('combo_markPrice[]');
                var index = $(".tab-nav li.curr").index();

                for (var i = 0; i < combo_shop.length; i++) {
                    combo_shop[i].innerHTML = res.fittings_interval[index].fittings_minMax;
                }

                for (var i = 0; i < combo_mark.length; i++) {
                    combo_mark[i].innerHTML = res.fittings_interval[index].market_minMax;
                }
            }

            // 显示开通高级会员权益卡
            if ($("#vip_register").length > 0) {

                if (res.is_show_drp == 1 && res.is_drp == 1 && res.drp_shop == 0) {
                    if (res.membership_card_discount_price > 0) {
                        $("#card_discount_price").html(res.membership_card_discount_price_formated);
                    }

                    $("#vip_register").css('display', 'block');
                } else {
                    $("#vip_register").css('display', 'none');
                }
            }

            if (res.err_no == 2) {
                $("#isHas_warehouse_num").html(json_languages.shiping_prompt);
            } else {
                var isHas;
                var is_shipping = Number($("#is_shipping").val());

                if ($("#isHas_warehouse_num").length > 0) {
                    if ((res.attr_number > 0) && (res.attr_number > 0 || res.original_spec_price == res.original_shop_price) && ({{ $goods['is_on_sale'] ?? 0 }} != 0 || is_shipping == 1))
                    {
                        $("a[ectype='btn-append']").attr('href', 'javascript:addToCartShowDiv({{ $goods['goods_id'] }})').removeClass('btn_disabled');
                        @if($user_id <= 0 && $one_step_buy)
                        $("a[ectype='btn-buynow']").attr('href', '#none').removeClass('btn_disabled');
                        @else
                        $("a[ectype='btn-buynow']").attr('href', 'javascript:bool=0;buynow=1;addToCart({{ $goods['goods_id'] }})').removeClass('btn_disabled');
                        @endif

                        $("a[ectype='byStages']").removeClass('btn_disabled');
                        $('a').remove('#quehuo');
                        isHas = '<strong>' + json_languages.Have_goods + '</strong>，' + json_languages.Deliver_back_order;

                        $("a[ectype='btn-buynow']").show();
                        $("a[ectype='btn-append']").show();
                        $("a[ectype='byStages']").show();
                    }
                else
                    {
                        isHas = '<strong>' + json_languages.No_goods + '</strong>，' + json_languages.goods_over;

                        $("a[ectype='btn-buynow']").attr('href', 'javascript:void(0)').addClass('btn_disabled');
                        $("a[ectype='btn-append']").attr('href', 'javascript:void(0)').addClass('btn_disabled');
                        $("a[ectype='byStages']").addClass('btn_disabled');


                        @if($goods['review_status'] >= 3)

                        if (!document.getElementById('quehuo')) {
                            if ({{ $goods['is_on_sale'] ?? 0 }} !=
                            0 || is_shipping == 1
                        )
                            {
                                $("a[ectype='btn-buynow']").hide();
                                $("a[ectype='btn-append']").hide();
                                $("a[ectype='byStages']").hide();
                                $('.choose-btns').append('<a id="quehuo" class="btn-buynow" href="javascript:addToCart({{ $goods['goods_id'] }});">{{ $lang['have_no_goods'] }}</a>');
                            }
                        }

                        @endif

                    }

                    if (res.store_type == 1) {
                        $("[ectype='btn-store-pick']").show();
                        $("[ectype='list-store-pick']").show();
                    } else {
                        $("[ectype='btn-store-pick']").hide();
                        $("[ectype='list-store-pick']").hide();
                    }
                    if (is_shipping == 0) {
                        //商品没有选择运费模板或者没有设置固定运费时，提示不支持配送
                        var freight_info = $("#user_area_shipping").find("*[ectype='freight_info_span']");
                        if (freight_info.length < 1) {
                            $("#user_area_shipping").html("<span class='gary'>[ " + json_languages.shiping_prompt + " ]</span>");
                        }
                    }

                    $("#isHas_warehouse_num").html(isHas);
                }
            }

            if (res.fittings_interval) {
                for (var i = 0; i < res.fittings_interval.length; i++) {
                    $("#m_goods_" + res.fittings_interval[i].groupId).html(res.fittings_interval[i].fittings_minMax);
                    $("#m_goods_save_" + res.fittings_interval[i].groupId).html(res.fittings_interval[i].save_minMaxPrice);
                    $("#m_goods_reference_" + res.fittings_interval[i].groupId).html(res.fittings_interval[i].market_minMax);
                }
            }

            if (res.type == 1) {
                $("*[ectype='SHOP_PRICE']").html(res.result);
            }

            $(".ECS_fittings_interval").html(res.result);

            //更新白条分期购每期的价格 start
            if (res.stages) {
                var i = 0;
                $.each(res.stages, function (k, v) {
                    if (k != 1) {
                        $("*[ectype='is-ious'] .item i").eq(i).html('￥' + v + '×' + k + json_languages.qi);
                        $("*[ectype='is-ious'] .item i").eq(i).next('span').html(json_languages.free_desc + '{{ $goods['stages_rate'] }}%，￥' + v + '×' + k + json_languages.qi);
                    }
                    i++;
                });
            }
        }

        isReturn = true;

        //切换属性 库存变化数量加减start
        $("*[ectype='quantity']").val(1);
        $("[ectype='btnReduce']").addClass("btn-disabled");
        if (Number($("*[ectype='perNumber']").val()) > 1) {
            $("[ectype='btnAdd']").removeClass("btn-disabled");
        }else{
            $("[ectype='btnAdd']").addClass("btn-disabled");
        }
        //切换属性 库存变化数量加减end

        if (res.type == 1) {
            quantity();
        }

        //加载完成后才能操作，解决未加载完成就能加入购物车bug
        $(".choose-btns").show();
    }

    /* 商品看了又看 start */
    function seeMoreGoods() {
        var region_id = $(":input[name='region_id']").val();
        var area_id = $(":input[name='area_id']").val();
        var area_city = $(":input[name='area_city']").val();

        if (!region_id) {
            region_id = {{ $region_id ?? 0 }};
        }

        if (!area_id) {
            area_id = {{ $area_id ?? 0 }};
        }

        if (!area_city) {
            area_city = {{ $area_city ?? 0 }};
        }

        Ajax.call('ajax_goods.php', 'act=see_more_goods&goods_id=' + goodsId + '&cat_id=' + {{ $goods['cat_id'] ?? 0 }} +'&seller_id=' + {{ $goods['user_id'] ?? 0 }} +'&warehouse_id=' + region_id + '&area_id=' + area_id + '&area_city=' + area_city, seeMoreResponse, 'GET', 'JSON');
    }

    function seeMoreResponse(res) {
        $("#see_more_goods").html(res.content);

        //右侧看了又看上下滚动
        $(".track_warp").slide({
            mainCell: ".track-con ul",
            effect: "top",
            pnLoop: false,
            autoPlay: false,
            autoPage: true,
            prevCell: ".sprite-up",
            nextCell: ".sprite-down",
            vis: 3
        });
    }

    /* 商品看了又看 end */

    /* 商品猜你喜欢 start */
    function guessGoodsLove() {
        var region_id = $(":input[name='region_id']").val();
        var area_id = $(":input[name='area_id']").val();
        var area_city = $(":input[name='area_city']").val();

        if (!region_id) {
            region_id = '{{ $region_id ?? 0 }}';
        }

        if (!area_id) {
            area_id = '{{ $area_id ?? 0 }}';
        }

        if (!area_city) {
            area_city = '{{ $area_city ?? 0 }}';
        }

        Ajax.call('ajax_goods.php', 'act=guess_goods_love&goods_id=' + goodsId + '&warehouse_id=' + region_id + '&area_id=' + area_id + '&area_city=' + area_city, guessGoodsLoveResponse, 'GET', 'JSON');
    }

    function guessGoodsLoveResponse(res) {
        $("#guess_goods_love").html(res.content);
    }
    /* 商品猜你喜欢 end */

    /*生成购买高级会员权益h5链接二维码*/
    $('.onVipRegister').on('click', function () {

        var user_id = '{{ $user_id }}';

        if (!user_id || user_id == 0) {
            var goods_url = "goods.php?id=" + goodsId;
            $.notLogin("get_ajax_content.php?act=get_login_dialog", goods_url);
            return false;
        }

        Ajax.call('ajax_goods.php?act=on_vip_register', 'goods_id=' + goodsId, onVipRegisterResponse, 'POST', 'JSON');
    });

    function onVipRegisterResponse(res) {
        var membership_card_discount_price = $("#card_discount_price").html();

        var htmlStr = '<div class="immediately-opened-popup ecsc-cart-popup" style="display: block;"><div class="loading-mask"></div><div class="loading"><div class="center_pop_txt"><div class="title"><h3>{{ $lang['immediately_opened'] }}</h3><a href="javascript:loadingClose();" title="{{ $lang['close'] }}" class="loading-x">X</a></div><div class="center_pop_p"><div>{{ $lang['membership_card_can_discount_2'] }} <span class="color-red">'+membership_card_discount_price+'</span></div><img class="erweima" src="'+res.qrcode+'" /><div class="color-ccc size10">{{ $lang['qrcode_span_membership_card'] }}</div></div></div></div></div>'

        $('.dialogbox').append(htmlStr);
    }

    /* 复制链接弹框 star */
    $('.copy-link').on('click', function () {
        var current_url = "{{ url('goods.php') }}?id=" + goodsId + '&u={{ $user_id }}';

        var shareHtml = '<div class="immediately-opened-popup ecsc-cart-popup" style="display: block;"><div class="loading-mask"></div><div class="loading"><div class="center_pop_txt"><div class="title"><h3>{{ $lang['copy_url'] }}</h3><a href="javascript:loadingClose();" title="{{ $lang['close'] }}" class="loading-x">X</a></div><div class="center_pop_p"><div class="share-explain">{{ $lang['share_explain'] }}</div><div class="share-box"><span>{{ $lang['share'] }}: </span><textarea wrap="hard" id="share">{{ $lang['share_link'] }} '+current_url+'</textarea></div><div class="share-copy-btn">{{ $lang['copy'] }}</div></div></div></div></div>';

        $('.dialogbox').append(shareHtml);
    })
    /* 复制链接弹框 end */

    /* 复制链接 star */
    $('.hidden').on('click', '.share-copy-btn', function () {

        var share = $('#share').text();
        copyTextToClipboard(share);

        console.log('复制链接到剪粘板:' + share)

        loadingClose();
    })
    /* 复制链接 end */


    /******************************************* js方法 end***********************************************/
</script>

</body>
</html>
