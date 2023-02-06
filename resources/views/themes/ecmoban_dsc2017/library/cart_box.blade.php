

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
                        <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-05" target="_blank">&nbsp;{{ $lang['gather_together'] }}&nbsp;></a>

@endif


@else


@if($activity['available'])

                        <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-03" target="_blank">{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }} ，{{ $lang['receive_gifts'] }}{{ $activity['act_type_ext'] }}{{ $lang['jian'] }} ，{{ $lang['receive_gifts_again'] }}{{ $activity['left_gift_num'] }}{{ $lang['jian'] }} &gt; </a>
                        <a href="javascript:void(0);" data-actid="{{ $activity['act_id'] ?? 0 }}" data-ruid="{{ $goodsRu['ru_id'] }}" id="select-gift-{{ $activity['act_id'] ?? 0 }}" class="f-btn" ectype="tradeBtn">{{ $lang['receive_gift'] }}</a>

@else

                        <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-03" target="_blank">{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}，{{ $lang['receive_gifts'] }}{{ $activity['act_type_ext'] }}{{ $lang['jian'] }} &gt; </a>
                        <a href="javascript:void(0);" data-actid="{{ $activity['act_id'] ?? 0 }}" data-ruid="{{ $goodsRu['ru_id'] }}" id="select-gift-{{ $activity['act_id'] ?? 0 }}" class="f-btn" ectype="tradeBtn">{{ $lang['see_gift'] }}</a>
                        <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" class="ftx-05" target="_blank">&nbsp;{{ $lang['gather_together'] }}&nbsp;></a>

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
<div class="ftx-01 ml mt5">{{ $lang['been_reduced'] }}{{ $activity['act_type_ext_format'] }}</div>
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

                    <a href="coudan.php?id={{ $activity['act_id'] ?? 0 }}" target="_blank" class="ftx-05">
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
<div class="ftx-01 ml mt5">{{ $lang['been_reduced'] }}{{ $activity['goods_fav_amount_format'] }}</div>
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
 />
                            <label for="checkItem_{{ $goods['rec_id'] }}" class="ui-label-14">&nbsp;</label>
                        </div>
                    </div>
                    <div class="cell s-goods">
                        <div class="goods-item">
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70"></a></div>
                            <div class="item-msg">
                                <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['goods_name'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                                <div class="gds-types">

@if($goods['stages_qishu'] != -1)

                                    <em class="gds-type gds-type-stages">{{ $lang['by_stages'] }}</em>

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

                        <div class="tc ftx-03">{{ $lang['Have_goods'] }}</div>

@else

                        <div class="tc ftx-01">{{ $lang['No_goods'] }}</div>

@endif

                    </div>
                    <div class="cell s-sum">
                        <strong id="goods_subtotal_{{ $goods['rec_id'] }}"><font id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}_subtotal">{{ $goods['formated_subtotal'] }}</font></strong>
                        <div class="cuttip
@if($goods['dis_amount'] == 0)
hide
@endif
">
                            <span class="tit">{{ $lang['youhui'] }}</span>
                            <span class="price" id="discount_amount_{{ $goods['rec_id'] }}">{{ $goods['discount_amount'] }}</span>
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
 />
                            <label for="checkItem_{{ $goods['rec_id'] }}" class="ui-label-14">&nbsp;</label>
                        </div>
                    </div>
                    <div class="cell s-goods">
                        <div class="goods-item">
                            <div class="p-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                                <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70" /></a>

@else

                                <a href="javascript:void(0);" target="_blank"><img src="{{ skin('images/17184624079016pa.jpg') }}" width="70" height="70" /></a>

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

                                <a href="{{ $goods['url'] }}" target="_blank">{{ $goods['goods_name'] }}</a>

@endif


                                <div class="gds-types">
                                    <em class="gds-type gds-type-stages">{{ $lang['largess'] }}</em>

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
                        <strong id="goods_price_{{ $goods['rec_id'] }}">{{ $goods['goods_price'] }}</strong>
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
@if($goods['dis_amount'] == 0)
hide
@endif
">
                            <span class="tit">{{ $lang['youhui'] }}</span>
                            <span class="price" id="discount_amount_{{ $goods['rec_id'] }}">{{ $goods['discount_amount'] }}</span>
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
 />
                            <label for="checkItem_{{ $goods['rec_id'] }}" class="ui-label-14">&nbsp;</label>
                        </div>
                    </div>
                    <div class="cell s-goods">
                        <div class="goods-item">
                            <div class="p-img">

@if($goods['goods_id'] > 0 && $goods['extension_code'] != 'package_buy')

                                <a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70" /></a>

@else

                                <a href="javascript:void(0);" target="_blank"><img src="{{ skin('images/17184624079016pa.jpg') }}" width="70" height="70" /></a>

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

                                <a href="{{ $goods['url'] }}" target="_blank">{{ $goods['goods_name'] }}</a>

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
                        <strong id="goods_price_{{ $goods['rec_id'] }}">{{ $goods['goods_price'] }}</strong>
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

                        <div class="tc ftx-03">{{ $lang['Have_goods'] }}</div>

@else

                        <div class="tc ftx-01">{{ $lang['No_goods'] }}</div>

@endif

                    </div>
                    <div class="cell s-sum">
                        <strong id="goods_subtotal_{{ $goods['rec_id'] }}"><font id="{{ $goods['group_id'] }}_{{ $goods['rec_id'] }}_subtotal">{{ $goods['formated_subtotal'] }}</font></strong>
                        <div class="cuttip
@if($goods['dis_amount'] == 0)
hide
@endif
">
                            <span class="tit">{{ $lang['youhui'] }}</span>
                            <span class="price" id="discount_amount_{{ $goods['rec_id'] }}">{{ $goods['discount_amount'] }}</span>
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

<script type="text/javascript">
    $("*[ectype='c-promotion']").on("click",function(){
        var $this = $(this);
        var parent = $this['parent']();
        var height = parent.find("*[ectype='promTips'] ul").height();

        $(".promotion-info").removeClass("prom-hover");
        $(".promotion-info").find("*[ectype='promTips']").css("height",0);
        if(parent.hasClass("prom-hover")){
            parent.removeClass("prom-hover");
            parent.find("*[ectype='promTips']").css("height",0);
        }else{
            parent.addClass("prom-hover");
            parent.find("*[ectype='promTips']").css("height",height);
        }
    });
</script>
