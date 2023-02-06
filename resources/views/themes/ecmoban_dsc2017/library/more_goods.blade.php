
@if($goods_list)


@foreach($goods_list as $goods)


@if($goods['goods_id'])


@if(!$model)


@if($goods['act_id'])

    <li class="gl-item">
        <div class="gl-i-wrap">
            <div class="p-img"><a href="{{ $goods['purl'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" /></a></div>

@if($goods['pictures'])

            <div class="sider">
                <a href="javascript:void(0);" class="sider-prev"><i class="iconfont icon-left"></i></a>
                <ul>

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

                </ul>
                <a href="javascript:void(0);" class="sider-next"><i class="iconfont icon-right"></i></a>
            </div>

@endif

            <div class="p-lie">
                <div class="p-price">
                    <span>{{ $goods['shop_price'] }}</span>
                </div>
                <div class="p-num">{{ $lang['existing'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['make_appointment'] }}</div>
            </div>
            <div class="p-name"><a href="{{ $goods['purl'] }}" target="_blank" title="{{ $goods['goods_name'] }}">
@if($script_name == 'search')
{{ $goods['goods_name_keyword'] }}
@else
{{ $goods['goods_name'] }}
@endif
</a></div>
            <div class="p-store">

@if($goods['user_id'] == 0)

                <a href="javascript:;" title="{{ $goods['rz_shop_name'] }}" class="store">{{ $goods['rz_shop_name'] }}</a>

@else

                <a href="{{ $goods['store_url'] }}" title="{{ $goods['rz_shop_name'] }}" class="store" target="_blank">{{ $goods['rz_shop_name'] }}</a>

@endif


                <a  id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goods['user_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="p-kefu
@if($goods['user_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>

            </div>

@if($goods['is_new'] || $goods['is_hot'] || $goods['is_best'] || $goods['is_shipping'] || $goods['self_run'] || $goods['user_id'] == 0)

            <div class="p-activity">

@if($goods['act_id'])

                <span class="tag tac-mn">
                    <i class="i-left"></i>
                    {{ $goods['presale'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['is_new'])

                <span class="tag tac-mn">
                    <i class="i-left"></i>
                    {{ $lang['is_new'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['is_hot'])

                <span class="tag tac-mh">
                    <i class="i-left"></i>
                    {{ $lang['is_hot'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['is_best'])

                <span class="tag tac-mb">
                    <i class="i-left"></i>
                    {{ $lang['is_best'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['is_shipping'])

                <span class="tag tac-by">
                    <i class="i-left"></i>
                    {{ $lang['Free_shipping'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['self_run'] || $goods['user_id'] == 0)

                <span class="tag tac-sr">
                    <i class="i-left"></i>
                    {{ $lang['self_run'] }}
                    <i class="i-right"></i>
                </span>

@endif

            </div>

@else

            <div class="p-activity">&nbsp;</div>

@endif

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

    <li class="gl-item">
        <div class="gl-i-wrap">
            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" /></a></div>

@if($goods['pictures'])

            <div class="sider">
                <a href="javascript:void(0);" class="sider-prev"><i class="iconfont icon-left"></i></a>
                <ul>

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

                </ul>
                <a href="javascript:void(0);" class="sider-next"><i class="iconfont icon-right"></i></a>
            </div>

@endif

            <div class="p-lie">
                <div class="p-price">

@if($goods['promote_price'] != '')

                        {{ $goods['promote_price'] }}

@else

                        {{ $goods['shop_price'] }}

@endif

                </div>
                <div class="p-num">{{ $lang['Sold'] }}<em>{{ $goods['sales_volume'] }}</em>{{ $lang['jian'] }}</div>
            </div>
            <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">
@if($script_name == 'search')
{{ $goods['goods_name_keyword'] }}
@else
{{ $goods['goods_name'] }}
@endif
</a></div>
            <div class="p-store">

@if($goods['user_id'] == 0)

                <a href="javascript:;" title="{{ $goods['rz_shop_name'] }}" class="store">{{ $goods['rz_shop_name'] }}</a>

@else

                <a href="{{ $goods['store_url'] }}" title="{{ $goods['rz_shop_name'] }}" class="store" target="_blank">{{ $goods['rz_shop_name'] }}</a>

@endif

                <a  id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goods['user_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="p-kefu
@if($goods['user_id'] == 0)
 p-c-violet
@endif
"><i class="iconfont icon-kefu"></i></a>

            </div>

@if($goods['is_new'] || $goods['is_hot'] || $goods['is_best'] || $goods['is_shipping'] || $goods['self_run'] || $goods['user_id'] == 0)

            <div class="p-activity">

@if($goods['is_new'])

                <span class="tag tac-mn">
                    <i class="i-left"></i>
                    {{ $lang['is_new'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['is_hot'])

                <span class="tag tac-mh">
                    <i class="i-left"></i>
                    {{ $lang['is_hot'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['is_best'])

                <span class="tag tac-mb">
                    <i class="i-left"></i>
                    {{ $lang['is_best'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['is_shipping'])

                <span class="tag tac-by">
                    <i class="i-left"></i>
                    {{ $lang['Free_shipping'] }}
                    <i class="i-right"></i>
                </span>

@endif


@if($goods['self_run'] || $goods['user_id'] == 0)

                <span class="tag tac-sr">
                    <i class="i-left"></i>
                    {{ $lang['self_run'] }}
                    <i class="i-right"></i>
                </span>

@endif

            </div>

@else

            <div class="p-activity">&nbsp;</div>

@endif

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


@else

<li class="gl-h-item
@if($loop->iteration % 2 == 0)
item_bg
@endif
">
    <div class="gl-i-wrap">
        <div class="col col-1">
            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" /></a></div>
            <div class="p-right">
                <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                <div class="p-lie">
                    <div class="p-num">{{ $lang['sales_volume'] }}：{{ $goods['sales_volume'] }}</div>
                </div>
            </div>
        </div>
        <div class="col col-2">
            <div class="p-store">

@if($goods['user_id'] == 0)

            <a href="javascript:;" title="{{ $goods['rz_shop_name'] }}" class="store">{{ $goods['rz_shop_name'] }}</a>

@else

            <a href="{{ $goods['store_url'] }}" title="{{ $goods['rz_shop_name'] }}" class="store" target="_blank">{{ $goods['rz_shop_name'] }}</a>

@endif

            <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goods['user_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="p-kefu"><i class="iconfont icon-kefu"></i></a>

            </div>
        </div>
        <div class="col col-3">
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


@endif


@endforeach


@endif

