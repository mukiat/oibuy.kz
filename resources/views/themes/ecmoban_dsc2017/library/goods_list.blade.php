@if($search_driver != 'elasticsearch')
    <div class="filter">
        @if($script_name == 'search')
            @include('frontend::/library/search_filter')
        @else
            @include('frontend::/library/category_filter')
        @endif
    </div>
@endif

<div class="g-view w">
    <div class="goods-list
@if(!$best_goods)
 goods-list-w1390
@endif
" ectype="gMain">

@if($goods_list)

        <div class="gl-warp gl-warp-large"
@if($dsc_display == 'grid')
 style="display:none;"
@else
 style="display:block;"
@endif
>

@if($category > 0)

            <form name="compareForm" action="compare.php" method="post" onSubmit="return compareGoods(this);" class="goodslistForm" data-state="0">

@endif

            <div class="goods-list-warp">
                <ul ectype="items">

@foreach($goods_list as $goods)


@if($goods['goods_id'])


@if($goods['act_id'])

                    <li class="gl-item" ectype="large_{{ $goods['goods_id'] }}">
                        <div class="gl-i-wrap">
                            <div class="p-img">
                                <a href="{{ $goods['purl'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" />
                                @if (!empty($goods['goods_label_suspension']['formated_label_image']))
                                <div class="goods_label_suspension">
                                    <img src="{{ $goods['goods_label_suspension']['formated_label_image'] }}">
                                </div>
                                @endif
                                </a>
                            </div>
                            <div class="sider">
                                <a href="javascript:void(0);" class="sider-prev"><i class="iconfont icon-left"></i></a>
                                <ul>

@if($goods['pictures'])


@foreach($goods['pictures'] as $picture)


@if($loop->index < 4)

                                    <li
@if($loop->index == 0)
 class="curr"
@endif
><img src="
@if($picture['thumb_url'])
{{ $picture['thumb_url'] }}
@else
{{ $picture['img_url'] }}
@endif
" width="26" height="26" /></li>

@endif


@endforeach


@else

                                    <li class="curr"><img src="{{ $goods['goods_thumb'] }}" width="26" height="26" /></li>

@endif

                                </ul>
                                <a href="javascript:void(0);" class="sider-next"><i class="iconfont icon-right"></i></a>
                            </div>
                            <div class="p-lie">
                                <div class="p-price">
                                    <span>{{ $goods['shop_price'] }}</span>
                                </div>
                                <div class="p-num">{{ $lang['existing'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['make_appointment'] }}</div>
                            </div>
                            <div class="p-name">
                                <a href="{{ $goods['purl'] }}" title="{{ $goods['goods_name'] }}" target="_blank">
                                    @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                    @if($script_name == 'search')
                                    {!! $goods['goods_name_keyword'] !!}
                                    @else
                                    {{ $goods['goods_name'] }}
                                    @endif
                                </a>
                            </div>
                            <div class="p-store">

@if($goods['user_id'] == 0)

                                <a href="javascript:;" title="{{ $goods['rz_shop_name'] }}" class="store">{{ $goods['rz_shop_name'] }}</a>

@else

                                <a href="merchants_store.php?merchant_id={{ $goods['user_id'] }}" title="{{ $goods['rz_shop_name'] }}" class="store" target="_blank">{{ $goods['rz_shop_name'] }}</a>

@endif

                                <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goods['user_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="p-kefu
@if($goods['user_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>


@if($goods['is_minimum'] == 1)
<div class="minimum">{{ $lang['minimum'] }}:{{ $goods['minimum'] }}</div>
@endif

                            </div>

<div class="label_list">
@forelse ($goods['goods_label'] as $key=>$label)
<a @if($label['label_url']) href="{{ $label['label_url'] }}" target="_blank" @else href="javascript:;" style="cursor: auto;" @endif class="label_url"><img src="{{ $label['formated_label_image'] }}"></a>
@empty

@endforelse
</div>

                            <div class="p-operate">
                                <a href="javascript:void(0);" id="compareLink"
@if($script_name == 'search' && $goods['type'] == 0)
 title="{{ $lang['not_set_attrtype_contrast'] }}" class="not_compareLink"
@endif
>
                                    <input id="{{ $goods['goods_id'] }}" type="checkbox" name="duibi" class="ui-checkbox"
@if($script_name != 'search' || $goods['type'] != 0)
 onClick="Compare.add(this, {{ $goods['goods_id'] }},'{{ $goods['goods_name'] }}','{{ $goods['type'] }}', '{{ $goods['goods_thumb'] }}', '{{ $goods['shop_price'] }}', '{{ $goods['market_price'] }}')"
@endif
>
                                    <label class="ui-label"
@if($script_name != 'search' || $goods['type'] != 0)
 for="{{ $goods['goods_id'] }}"
@endif
>{{ $lang['compare'] }}</label>
                                </a>
                                <a href="javascript:collect({{ $goods['goods_id'] }},'large');" class="choose-btn-coll
@if($goods['is_collect'])
 selected
@endif
"><i class="iconfont
@if($goods['is_collect'])
 icon-zan-alts
@else
 icon-zan-alt
@endif
"></i>{{ $lang['btn_collect'] }}</a>

@if($goods['prod'] == 1)


@if($goods['goods_number'] > 0)

                                    <a href="{{ $goods['purl'] }}" rev="{{ $goods['goods_thumb'] }}" class="addcart"><i class="iconfont icon-carts"></i>{{ $lang['rob_make_appointment'] }}</a>

@else

                                    <a href="javascript:void(0);" class="addcart btn_disabled"><i class="iconfont icon-carts"></i>{{ $lang['have_no_goods'] }}</a>

@endif


@else

                                <a href="{{ $goods['purl'] }}" rev="{{ $goods['goods_thumb'] }}" class="addcart"><i class="iconfont icon-carts"></i>{{ $lang['rob_make_appointment'] }}</a>

@endif

                            </div>


@if($dwt_filename == 'history_list')

                            <div class="history_close">
                                <a href="javascript:delHistory({{ $goods['goods_id'] }})"><img src="{{ skin('images/p-del.png') }}"></a>
                            </div>

@endif

                        </div>
                    </li>

@else

                    <li class="gl-item" ectype="large_{{ $goods['goods_id'] }}">
                        <div class="gl-i-wrap">
                            <div class="p-img">
                                <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['pictures']['0']['img_url'] ?? $goods['goods_thumb'] }}" />
                                @if (!empty($goods['goods_label_suspension']['formated_label_image']))
                                <div class="goods_label_suspension">
                                    <img src="{{ $goods['goods_label_suspension']['formated_label_image'] }}">
                                </div>
                                @endif
                                </a>
                            </div>
                            <div class="sider">
                                <a href="javascript:void(0);" class="sider-prev"><i class="iconfont icon-left"></i></a>
                                <ul>

@if($goods['pictures'])


@foreach($goods['pictures'] as $picture)


@if($loop->index < 4)

                                    <li
@if($loop->index == 0)
 class="curr"
@endif
><img src="
@if($picture['thumb_url'])
{{ $picture['thumb_url'] }}
@else
{{ $picture['img_url'] }}
@endif
" width="26" height="26" /></li>

@endif


@endforeach


@else

                                    <li class="curr"><img src="{{ $goods['goods_thumb'] }}" width="26" height="26" /></li>

@endif

                                </ul>
                                <a href="javascript:void(0);" class="sider-next"><i class="iconfont icon-right"></i></a>
                            </div>
                            <div class="p-lie">
                                <div class="p-price">

@if($goods['promote_price'] != '')
                                        {{ $goods['promote_price'] }} <em style="text-decoration: line-through; font-size: 12px; color: #555">{{ $goods['shop_price'] }}</em>

@else

                                        {{ $goods['shop_price'] }}

@endif

                                </div>
                                <div class="p-num">{{ $lang['Sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
                            </div>
                            <div class="p-name">
                                <a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">
                                    @if($goods['promote_price'] != '')
                                    <span style=" background: #ff5256; color: #FFF; padding: 2px 6px;">{{ $lang['special_sale'] }}</span>
                                    @endif
                                    @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                    @if($script_name == 'search')
                                    {!! $goods['goods_name_keyword'] !!}
                                    @else
                                    {{ $goods['goods_name'] }}
                                    @endif
                                </a>
                            </div>
                            <div class="p-store">

@if($goods['user_id'] == 0)

                                <a href="javascript:;" title="{{ $goods['rz_shop_name'] }}" class="store">{{ $goods['rz_shop_name'] }}</a>

@else

                                <a href="merchants_store.php?merchant_id={{ $goods['user_id'] }}" title="{{ $goods['rz_shop_name'] }}" class="store" target="_blank">{{ $goods['rz_shop_name'] }}</a>

@endif


                                <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goods['user_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="p-kefu
@if($goods['user_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>


@if($goods['is_minimum'] == 1)
<div class="minimum">{{ $lang['minimum'] }}:{{ $goods['minimum'] }}</div>
@endif

                            </div>

<div class="label_list">
@forelse ($goods['goods_label'] as $key=>$label)
<a @if($label['label_url']) href="{{ $label['label_url'] }}" target="_blank" @else href="javascript:;" style="cursor: auto;"  @endif class="label_url"><img src="{{ $label['formated_label_image'] }}"></a>
@empty

@endforelse
</div>
                            <div class="p-operate">
                                <a href="javascript:void(0);" id="compareLink"
@if($script_name == 'search' && $goods['type'] == 0)
 title="{{ $lang['not_set_attrtype_contrast'] }}" class="not_compareLink"
@endif
>
                                    <input id="{{ $goods['goods_id'] }}" type="checkbox" name="duibi" class="ui-checkbox"
@if($script_name != 'search' || $goods['type'] != 0)
 onClick="Compare.add(this, {{ $goods['goods_id'] }},'{{ $goods['goods_name'] }}','{{ $goods['type'] }}', '{{ $goods['goods_thumb'] }}', '{{ $goods['shop_price'] }}', '{{ $goods['market_price'] }}')"
@endif
>
                                    <label class="ui-label"
@if($script_name != 'search' || $goods['type'] != 0)
 for="{{ $goods['goods_id'] }}"
@endif
>{{ $lang['compare'] }}</label>
                                </a>
                                <a href="javascript:collect({{ $goods['goods_id'] }},'large');" class="choose-btn-coll
@if($goods['is_collect'])
 selected
@endif
"><i class="iconfont
@if($goods['is_collect'])
 icon-zan-alts
@else
 icon-zan-alt
@endif
"></i>{{ $lang['btn_collect'] }}</a>

@if($goods['prod'] == 1)


@if($goods['goods_number'] > 0)

                                    <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem');" rev="{{ $goods['goods_thumb'] }}" data-dialog="addCart_dialog"
@if($goods['is_minimum'] == 1)
data-minimum="{{ $goods['minimum'] }}"
@endif
 data-divid="addCartLog" data-title="{{ $lang['select_attr'] }}" class="addcart">
                                        <i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}
                                    </a>

@else

                                    <a href="javascript:void(0);" class="addcart btn_disabled"><i class="iconfont icon-carts"></i>{{ $lang['have_no_goods'] }}</a>

@endif


@else


@if($goods['seckill'])

                                <a href="{{ $goods['url'] }}" rev="{{ $goods['goods_thumb'] }}" class="addcart"><i class="iconfont icon-carts"></i>立即秒杀</a>

@else

                                <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem');" class="addcart" rev="{{ $goods['goods_thumb'] }}"><i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}</a>

@endif


@endif

                            </div>


@if($dwt_filename == 'history_list')

                            <div class="history_close">
                                <a href="javascript:delHistory({{ $goods['goods_id'] }})"><img src="{{ skin('images/p-del.png') }}"></a>
                            </div>

@endif

                        </div>
                    </li>

@endif


@endif


@endforeach

                </ul>
            </div>

@if($category > 0)

            @csrf </form>

@endif

            <div id="flyItem" class="fly_item"><img src="" width="40" height="40"></div>
        </div>
        <div class="gl-warp gl-warp-samll"
@if($dsc_display != 'grid')
 style="display:none;"
@else
 style="display:block;"
@endif
>

@if($category > 0)

            <form name="compareForm_cat" id="compareForm_cat" action="compare.php" method="post" onSubmit="return compareGoods(this);" class="goodslistForm" data-state="0">

@endif

            <div class="goods-list-warp">
            <ul ectype="items">

@foreach($goods_list as $goods)


@if($goods['goods_id'])

                <li class="gl-h-item
@if($loop->iteration % 2 == 0)
item_bg
@endif
" ectype="samll_{{ $goods['goods_id'] }}">
                    <div class="gl-i-wrap">
                        <div class="col col-1">
                            <div class="p-img">
                                <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" />
                                @if (!empty($goods['goods_label_suspension']['formated_label_image']))
                                <div class="goods_label_suspension">
                                    <img src="{{ $goods['goods_label_suspension']['formated_label_image'] }}">
                                </div>
                                @endif
                                </a>
                            </div>
                            <div class="p-right">
                                <div class="p-name">
                                    <a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">
                                        @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                        @if($goods['promote_price'] != '')
                                            <span style=" background: #ff5256; color: #FFF; padding: 2px 6px;">{{ $lang['special_sale'] }}</span>
                                        @endif
                                        {!! $goods['goods_name'] !!}
                                    </a>
                                </div>
                                <div class="p-lie">
                                    <div class="p-num">{{ $lang['sales_volume'] }}：{{ $goods['sales_volume'] }}</div>
                                    <div class="p-comm">{{ $lang['comments_rank'] }}：{{ $goods['review_count'] }}
@if($filename != 'category')
+
@endif
</div>
                                </div>
                            </div>
                        </div>
                        <div class="col col-2">
                            <div class="p-store">

@if($goods['user_id'] == 0)

                            <a href="javascript:;" title="{{ $goods['rz_shop_name'] }}" class="store">{{ $goods['rz_shop_name'] }}</a>

@else

                            <a href="merchants_store.php?merchant_id={{ $goods['user_id'] }}" title="{{ $goods['rz_shop_name'] }}" class="store" target="_blank">{{ $goods['rz_shop_name'] }}</a>

@endif

                            <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goods['user_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="p-kefu"><i class="iconfont icon-kefu"></i></a>

@if($goods['is_minimum'] == 1)
<div class="minimum">{{ $lang['minimum'] }}:{{ $goods['minimum'] }}</div>
@endif

                            </div>
                        </div>
                        <div class="col col-3">
                            <div class="p-price">
                                <div class="shop-price">

@if($goods['promote_price'] != '')
                                        <span class="tag tac-mn">
                                            <i class="i-left"></i>
                                            {{ $goods['presale'] }}
                                            <i class="i-right"></i>
                                        </span>
                                        {{ $goods['promote_price'] }} <em style="text-decoration: line-through; font-size: 12px; color: #555">{{ $goods['shop_price'] }}</em>

                                        <div class="summary-item">
                                            <div class="si-warp">
                                                <div class="m-price">{{ $goods['shop_price'] }}</div>
                                            </div>
                                        </div>
@else

                                        {{ $goods['shop_price'] }}

@endif

                                </div>
                                <div class="original-price">{{ $goods['market_price'] }}</div>
                            </div>
                        </div>
                        <div class="col col-4">
                            <div class="p-operate">
                                <a href="javascript:void(0);" id="compareLink"
@if($script_name == 'search' && $goods['type'] == 0)
 title="{{ $lang['not_set_attrtype_contrast'] }}" class="not_compareLink"
@endif
>
                                <input id="duibi_{{ $goods['goods_id'] }}" type="checkbox" name="duibi" class="ui-checkbox"
@if($script_name != 'search' || $goods['type'] != 0)
 onClick="Compare.add(this, {{ $goods['goods_id'] }},'{{ $goods['goods_name'] }}','{{ $goods['type'] }}', '{{ $goods['goods_thumb'] }}', '{{ $goods['shop_price'] }}', '{{ $goods['market_price'] }}')"
@endif
>
                                <label class="ui-label"
@if($script_name != 'search' || $goods['type'] != 0)
for="duibi_{{ $goods['goods_id'] }}"
@endif
>{{ $lang['compare'] }}</label>
                            </a>
                            <a href="javascript:collect({{ $goods['goods_id'] }},'samll');" class="choose-btn-coll
@if($goods['is_collect'])
 selected
@endif
"><i class="iconfont
@if($goods['is_collect'])
 icon-zan-alts
@else
 icon-zan-alt
@endif
"></i>{{ $lang['btn_collect'] }}</a>

@if($goods['prod'] == 1)


@if($goods['goods_number'] > 0)

                                <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem2');" rev="{{ $goods['goods_thumb'] }}" data-dialog="addCart_dialog"
@if($goods['is_minimum'] == 1)
 data-minimum="{{ $goods['minimum'] }}"
@endif
 data-id="" data-divid="addCartLog" data-url="" data-title="{{ $lang['select_attr'] }}" class="addcart">
                                    <i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}
                                </a>

@else

                                <a href="javascript:void(0);"  class="addcart"><i class="iconfont icon-carts"></i>{{ $lang['have_no_goods'] }}</a>

@endif


@else

                            <a href="javascript:void(0);" onClick="javascript:addToCart({{ $goods['goods_id'] }},0,event,this,'flyItem2');" class="addcart" rev="{{ $goods['goods_thumb'] }}"><i class="iconfont icon-carts"></i>{{ $lang['add_to_cart'] }}</a>

@endif

                            </div>
                        </div>
                    </div>
                </li>

@endif


@endforeach

            </ul>
            </div>

@if($category > 0)

            @csrf </form>

@endif

            <div id="flyItem2" class="fly_item2"><img src="" width="40" height="40"></div>
        </div>
        <input type="hidden" value="{{ $region_id ?? 0 }}" id="region_id" name="region_id">
        <input type="hidden" value="{{ $area_id ?? 0 }}" id="area_id" name="area_id">
        <input type="hidden" value="{{ $area_city ?? 0 }}" id="area_city" name="area_city">

@else

        <div class="no_records">
            <i class="no_icon_two"></i>
            <div class="no_info no_info_line">
                <h3>{{ $lang['information_null'] }}</h3>
                <div class="no_btn">
                    <a href="index.php" class="btn sc-redBg-btn">{{ $lang['back_home'] }}</a>
                </div>
            </div>
        </div>

@endif



@if($category_load_type)

        <div class="floor-loading goods-floor-loading" ectype="floor-loading"><div class="floor-loading-warp"><img src="{{ skin('images/load/loading.gif') }}"></div></div>

@else


            @include('frontend::library/pages')


@endif

        <div class="clear"></div>
    </div>
    @include('frontend::/library/category_recommend_best')
</div>
