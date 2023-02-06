<div class="ck-goods-list">
    @foreach($goods_list as $goodsRu)
        <div class="ck-goods-item" ectype="shoppingList">
            <div class="ck-goods-tit">
                <div class="ck-store">
                    @if($goodsRu['ru_id'] == 0)
                        <a href="javascript:;" class="shop-name">{{ $goodsRu['ru_name'] }}</a>
                    @else
                        <a href="{{ $goodsRu['url'] }}" target="_blank" class="shop-name">{{ $goodsRu['ru_name'] }}</a>
                    @endif

                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $goodsRu['ru_id'] }}"
                       class="p-kefu @if($goodsRu['ru_id'] == 0) p-c-violet @endif"><i class="iconfont icon-kefu"></i></a>
                </div>

                @if($goods_flow_type == 101)
                    @if($store_id > 0 || $store_seller == 'store_seller')
                        <div class="ck-dis-modes">
                            <div class="ck-dis-text"><i
                                        class="iconfont icon-store-alt"></i><span>{{ $lang['offline_store_information'] }}</span>
                            </div>
                            <div class="ck-dis-warp">
                                <i class="i-box"></i>

                                @if($goodsRu['offline_store'] != "")
                                    <div class="items">
                                        <div class="item">
                                            <div class="tit">{{ $lang['store_name'] }}：</div>
                                            <div class="value">{{ $goodsRu['offline_store']['stores_name'] }}
                                                ({{ $goodsRu['offline_store']['stores_tel'] }})
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="tit">{{ $lang['store_address'] }}：</div>
                                            <div class="value">
                                                <span class="tipTitle"
                                                      title="[{{ $goodsRu['offline_store']['province'] }}&nbsp;{{ $goodsRu['offline_store']['city'] }}&nbsp;{{ $goodsRu['offline_store']['district'] }}]&nbsp;{{ $goodsRu['offline_store']['stores_address'] }}">[{{ $goodsRu['offline_store']['province'] }}
                                                    &nbsp;{{ $goodsRu['offline_store']['city'] }}
                                                    &nbsp;{{ $goodsRu['offline_store']['district'] }}
                                                    ]&nbsp;{{ $goodsRu['offline_store']['stores_address'] }}</span>
                                            </div>
                                        </div>

                                        @if($goodsRu['offline_store']['stores_img'])
                                            <div class="item">
                                                <div class="tit">{{ $lang['store_pic'] }}：</div>
                                                <div class="value">
                                                    @if($goodsRu['offline_store']['stores_img'])
                                                        <a href="{{ $goodsRu['offline_store']['stores_img'] }}"
                                                           class="nyroModal ftx-05">{{ $lang['view'] }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        <div class="item">
                                            <div class="tit">{{ $lang['stores_opening_hours'] }}：</div>
                                            <div class="value">{{ $goodsRu['offline_store']['stores_opening_hours'] }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="tit">{{ $lang['stores_traffic_line'] }}：</div>
                                            <div class="value">
                                                <span class="tipTitle"
                                                      title="{{ $goodsRu['offline_store']['stores_traffic_line'] }}">{{ $goodsRu['offline_store']['stores_traffic_line'] }}</span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="extract[]" value="{{ $goodsRu['ru_id'] }}"/>
                                        <input type="hidden" name="ru_id[]" value="{{ $goodsRu['ru_id'] }}"/>
                                        <input type="hidden" name="ru_name[]" value="{{ $goodsRu['ru_name'] }}"/>
                                    </div>
                                @else
                                    {{ $lang['Please_store'] }}
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="ck-dis-modes">
                            <div class="ck-dis-tit">{{ $lang['shipping_method'] }}：</div>
                            @if($goodsRu['shipping'])
                                <div class="ck-dis-info" ectype="disInfo">
                                    <ul class="ck-sp-type">
                                        @if($goodsRu['shipping'])
                                            @foreach($goodsRu['shipping'] as $shipping)
                                                @if($shipping['shipping_code'] != 'cac' && $goodsRu['tmp_shipping_id'] == $shipping['shipping_id'])
                                                    <li class="mode-tab-item mode-tab-log shopping-list-checked @if($goodsRu['shipping_type'] == 0 && $shipping['default'] == 1) item-selected @endif"
                                                        ectype="tabLog" data-ruid="{{ $goodsRu['ru_id'] }}"
                                                        data-type="0" data-shipping="{{ $shipping['shipping_id'] }}"
                                                        data-shippingcode="{{ $shipping['shipping_code'] }}">
                                                        <span>
                                                            @if($shipping['shipping_name'])
                                                                {{ $shipping['shipping_name'] }}
                                                            @else
                                                                {{ $lang['not_set_shipping'] }}
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if($goodsRu['ru_id'] == 0 && $goodsRu['self_point'] != "")
                                            <li class="mode-tab-item shopping-list-checked @if($goodsRu['shipping_type'] == 1) item-selected @endif"
                                                ectype="tabCac" data-ruid="{{ $goodsRu['ru_id'] }}" data-type="1"
                                                data-shipping="{{ $goodsRu['self_point']['shipping_id'] }}"
                                                data-shippingcode="{{ $goodsRu['self_point']['shipping_code'] }}">
                                                <span>{{ $goodsRu['self_point']['shipping_name'] }}</span>
                                            </li>
                                        @endif
                                    </ul>

                                    <div class="mwapper mwapper-logistics" ectype="logistics">
                                        <i class="i-box"></i>
                                        <div class="mwapper-content">
                                            <ul>
                                                @if($goodsRu['shipping'])
                                                    @foreach($goodsRu['shipping'] as $shipping)
                                                        @if($shipping['shipping_code'] != 'cac')
                                                            <li class="logistics_li @if($goodsRu['tmp_shipping_id'] == $shipping['shipping_id']) item-selected @endif"
                                                                data-ruid="{{ $goodsRu['ru_id'] }}" data-type="0"
                                                                data-shipping="{{ $shipping['shipping_id'] }}"
                                                                data-shippingcode="{{ $shipping['shipping_code'] }}"
                                                                data-text="{{ $shipping['shipping_name'] }}">
                                                                <span>
                                                                    @if($shipping['shipping_name'])
                                                                        {{ $shipping['shipping_name'] }}
                                                                    @else
                                                                        {{ $lang['not_set_shipping'] }}
                                                                    @endif
                                                                </span>

                                                                @if($shipping['shipping_name'] && $shipping['shipping_code'] != 'fpd')
                                                                    <em>
                                                                        @if($shipping['shipping_fee'])
                                                                            （{{ $shipping['format_shipping_fee'] }}）
                                                                        @else
                                                                            （{{ $lang['Free_shipping'] }}）
                                                                        @endif
                                                                    </em>
                                                                @endif
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="mwapper mwapper-since" ectype="since">
                                        <i class="i-box"></i>
                                        <div class="mwapper-content">
                                            @if(!empty($goodsRu['self_point']))
                                                <div class="mode-list shipping_{{ $goodsRu['self_point']['shipping_id'] }}">
                                                    <div class="mode-list-item">
                                                        <label class="tit">{{ $lang['Place_reference'] }}：</label>
                                                        <span class="value">{{ $goodsRu['self_point']['name'] ?? '' }}</span>
                                                        <a href="javascript:void(0);" class="ftx-05" ectype="flow_dialog"
                                                           data-value='{"mark":0,"width":700,"height":350,"divid":"picksite_box","title":"{{ $lang['select_Place_reference'] }}","url":"ajax_flow.php?step=pickSite"}'>{{ $lang['modify'] }}</a>
                                                    </div>
                                                    <div class="mode-list-item">
                                                        <label class="tit">{{ $lang['Self_mentioning_date'] }}：</label>
                                                        <span class="value">{!! $goodsRu['self_point']['shipping_dateStr'] !!}</span>
                                                        <a href="javascript:void(0);" class="ftx-05" ectype="flow_dialog"
                                                           data-value='{"mark":1,"width":600,"height":250,"divid":"take_their_time","title":"{{ $lang['Self_mentioning_date'] }}","url":"ajax_flow.php?step=pickSite&mark=1"}'>{{ $lang['modify'] }}</a>
                                                    </div>
                                                    <input type="hidden" name="point_id" value="0">
                                                    <input type="hidden" name="shipping_dateStr" value="">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if(!$user_address)
                                    <span class="ftx-01">{{ $lang['address_prompt_two'] }}</span>
                                @else
                                    <span class="ftx-01">{{ $lang['shiping_prompt'] }}</span>
                                @endif
                            @endif

                            <input type="hidden" name="ru_id[]" value="{{ $goodsRu['ru_id'] }}"/>
                            <input type="hidden" name="ru_name[]" value="{{ $goodsRu['ru_name'] }}"/>

                            @if($goodsRu['shipping'] && $goodsRu['shipping_type'] == 0)
                                @foreach($goodsRu['shipping'] as $shipping)
                                    @if($goodsRu['tmp_shipping_id'] == $shipping['shipping_id'])
                                        <input type="hidden" name="shipping[]" class="shipping_{{ $goodsRu['ru_id'] }}"
                                               data-sellerid="{{ $goodsRu['ru_id'] }}"
                                               value="{{ $shipping['shipping_id'] ?? 0 }}" autocomplete="off"/>
                                        <input type="hidden" name="shipping_code[]"
                                               class="shipping_code_{{ $goodsRu['ru_id'] }}"
                                               value="{{ $shipping['shipping_code'] }}" autocomplete="off"/>

                                    @endif
                                @endforeach
                            @else
                                <input type="hidden" name="shipping[]" class="shipping_{{ $goodsRu['ru_id'] }}"
                                       data-sellerid="{{ $goodsRu['ru_id'] }}"
                                       value="{{ $goodsRu['self_point']['shipping_id'] ?? 0 }}" autocomplete="off"/>
                                <input type="hidden" name="shipping_code[]"
                                       class="shipping_code_{{ $goodsRu['ru_id'] }}"
                                       value="{{ $shipping['shipping_code'] }}" autocomplete="off"/>
                            @endif
                            <input type="hidden" name="shipping_type[]" class="shipping_type_{{ $goodsRu['ru_id'] }}"
                                   value="0" autocomplete="off"/>
                        </div>
                    @endif
                @endif
            </div>
            <div class="ck-goods-cont">
                @foreach($goodsRu['new_list'] as $key => $activity)
                    @if($activity['act_id'] > 0)
                        <div class="ck-prom @if($loop->first) ck-prom-first @endif">
                            <div class="ck-prom-header">
                                <div class="f-txt">
                                    <span class="full-icon"><i class="i-left"></i>{{ $activity['act_type_txt'] }}<i
                                                class="i-right"></i></span>
                                    @if($activity['act_type'] == 0)
                                        @if($activity['act_type_ext'] == 0)
                                            @if($activity['available'])
                                                <span class="ftx-01">{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}
                                                    ，{{ $lang['receive_gifts'] }}
                                                    @if($activity['cart_favourable_gift_num'] > 0)
                                                        ，{{ $lang['Already_receive'] }}{{ $activity['cart_favourable_gift_num'] }}{{ $lang['jian'] }}
                                                    @endif
</span>
                                            @else
                                                <span class="ftx-01">{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['cart_goods_one'] }} </span>
                                            @endif
                                        @else
                                            @if($activity['available'])
                                                <span class="ftx-01">
                                                    {{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}
                                                    ，
                                                    @if($activity['cart_favourable_gift_num'] > 0)
                                                        {{ $lang['cart_goods_two'] }}
                                                    @else
                                                        {{ $lang['cart_goods_three'] }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="ftx-01">{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['cart_goods_one'] }}</span>
                                            @endif
                                        @endif
                                    @elseif ($activity['act_type'] == 1)
                                        @if($activity['available'])
                                            <span class="ftx-01">{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}
                                                ,{{ $lang['been_reduced'] }}{{ $activity['act_type_ext_format'] }}{{ $lang['cart_goods_four'] }}</span>
                                        @else
                                            <span class="ftx-01">{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['cart_goods_five'] }}</span>
                                        @endif

                                    @elseif ($activity['act_type'] == 2)
                                        @if($activity['available'])
                                            <span class="ftx-01">{{ $lang['activity_notes_one'] }}{{ $activity['min_amount'] }}{{ $lang['yuan'] }}
                                                ，{{ $lang['Already_enjoy'] }}{{ $activity['act_type_ext_format'] }}{{ $lang['percent_off_Discount'] }}</span>
                                        @else
                                            <span class="ftx-01">{{ $lang['activity_notes_three'] }}{{ $activity['min_amount'] }}{{ $lang['zhekouxianzhi'] }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            @foreach($activity['act_goods_list'] as $goods)
                                <div class="cg-item @if($loop->first) first @endif">
                                    <div class="c-col cg-name">
                                        @if($goods['extension_code'] == '')
                                            <input name="cart_info[]" type="hidden"
                                                   value="{{ $goods['ru_id'] }}|{{ $goods['rec_id'] }}_{{ $goods['goods_id'] }}_{{ $goods['freight'] }}_{{ $goods['tid'] }}">
                                        @endif
                                        <a href="{{ $goods['url'] }}" target="_blank">
                                            <div class="p-img"><img src="{{ $goods['goods_thumb'] }}" width="48"
                                                                    height="48"></div>
                                            <div class="p-info">
                                                <div class="p-name">

                                                    @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                        <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                    @endif

                                                    @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                                    {{ $goods['goods_name'] }}
                                                </div>
                                                <div class="p-attr">{!! nl2br($goods['goods_attr_text']) !!}</div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="c-col cg-price">
                                        <span class="fr cl">
                                        @if($goods['rec_type'] == 5)
                                                {{ $lang['Deposit_flow'] }}：{{ $goods['formated_presale_deposit'] }}
                                            @else
                                                {{ $goods['goods_price_format'] }}
                                            @endif
                                        </span>
                                        @if($goods['dis_amount'] > 0)
                                            <span class="original-price fr cl">{{ $lang['Original_price'] }}
                                                :{{ config('shop.currency_format', '¥') }}{{ $goods['original_price'] }}</span>
                                            <span class="ftx-01 fr cl fs12">({{ $lang['Discount_flow'] }}
                                                ：{{ $goods['discount_amount'] }})</span>
                                        @endif
                                    </div>
                                    <div class="c-col cg-number">x{{ $goods['goods_number'] }}</div>
                                    <div class="c-col cg-state">
                                        <span>
                                            @if($goods['attr_number'])

                                                @if($goods['attr_number']  >= $goods['goods_number'] || $goods['extension_code'] == 'presale')
                                                    {{ $lang['Have_goods'] }}
                                                    <input name="rec_number" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}" value="0">
                                                @else
                                                    <font style="color:#e4393c">{{ $lang['Stock_goods_null'] }}</font>
                                                    <input name="rec_number" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}" value="1">
                                                @endif
                                            @else
                                                <font style="color:#e4393c">{{ $lang['No_goods'] }}</font>
                                                <input name="rec_number" type="hidden" data-id="{{ $goods['rec_id'] }}"
                                                       value="1">

                                            @endif

                                            @if($goodsRu['shipping'] || !$goods['is_real'])
                                                @if($goods['rec_shipping'] == 1)
                                                    <input name="shipping_prompt" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}"
                                                           value="0"/>
                                                @else
                                                    <input name="shipping_prompt" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}"
                                                           value="1"/>
                                                @endif
                                            @else
                                                <input name="shipping_prompt" type="hidden"
                                                       data-id="{{ $goods['rec_id'] }}" value="1"/>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="c-col cg-sum 1">
                                        <div>{{ $goods['formated_subtotal'] }}</div>

                                        @if($cross_border_version)
                                            @foreach($rate_arr as $ra)
                                                @if($ra['rec_id'] == $goods['rec_id'])
                                                    <div id="rate_price_{{ $goods['rec_id'] }}"
                                                         style="font-size:12px;color:#666;font-weight: normal;">{{ $lang['tax_fee'] }}
                                                        :{{ $ra['format_rate_price'] }}</div>

                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                    @if($activity['act_goods_list_num'] > 1)
                                        <div class="kuan"></div>
                                    @endif
                                </div>
                            @endforeach

                            @foreach($activity['act_cart_gift'] as $goods)
                                <div class="cg-item">
                                    <div class="c-col cg-name">
                                        <a href="{{ $goods['url'] }}" target="_blank">
                                            <div class="p-img"><img src="{{ $goods['goods_thumb'] }}" width="48"
                                                                    height="48"></div>
                                            <div class="p-info">
                                                <div class="p-name">
                                                    <i class="i-zp">{{ $lang['largess'] }}</i>
                                                    @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                        <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                    @endif

                                                    @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                                    {{ $goods['goods_name'] }}
                                                </div>
                                                <div class="p-attr">{!! nl2br($goods['goods_attr_text']) !!}</div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="c-col cg-price">
                                        <span class="fr cl">
                                            @if($goods['rec_type'] == 5)
                                                {{ $lang['Deposit_flow'] }}：{{ $goods['formated_presale_deposit'] }}
                                            @else
                                                {{ $goods['goods_price_format'] }}
                                            @endif
                                        </span>
                                        @if($goods['dis_amount'] > 0)
                                            <span class="original-price fr cl">{{ $lang['Original_price'] }}
                                                :{{ config('shop.currency_format', '¥') }}{{ $goods['original_price'] }}</span>
                                            <span class="ftx-01 fr cl fs12">({{ $lang['Discount_flow'] }}
                                                ：{{ $goods['discount_amount'] }})</span>

                                        @endif
                                    </div>
                                    <div class="c-col cg-number">x{{ $goods['goods_number'] }}</div>
                                    <div class="c-col cg-state">
                                        <span>
                                            @if($goods['attr_number'])

                                                @if($goods['attr_number']  >= $goods['goods_number'] || $goods['extension_code'] == 'presale')
                                                    {{ $lang['Have_goods'] }}
                                                    <input name="rec_number" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}" value="0">
                                                @else
                                                    <font style="color:#e4393c">{{ $lang['Stock_goods_null'] }}</font>
                                                    <input name="rec_number" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}" value="1">

                                                @endif
                                            @else
                                                <font style="color:#e4393c">{{ $lang['No_goods'] }}</font>
                                                <input name="rec_number" type="hidden" data-id="{{ $goods['rec_id'] }}"
                                                       value="1">
                                            @endif

                                            @if($goodsRu['shipping'] || !$goods['is_real'])
                                                @if($goods['rec_shipping'] == 1)
                                                    <input name="shipping_prompt" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}"
                                                           value="0"/>
                                                @else
                                                    <input name="shipping_prompt" type="hidden"
                                                           data-id="{{ $goods['rec_id'] }}"
                                                           value="1"/>
                                                @endif
                                            @else
                                                <input name="shipping_prompt" type="hidden"
                                                       data-id="{{ $goods['rec_id'] }}" value="1"/>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="c-col cg-sum 2">
                                        <div>{{ $goods['formated_subtotal'] }}</div>
                                        @if($cross_border_version)
                                            @foreach($rate_arr as $ra)
                                                @if($ra['rec_id'] == $goods['rec_id'])
                                                    <div id="rate_price_{{ $goods['rec_id'] }}"
                                                         style="font-size:12px;color:#666;font-weight: normal;">{{ $lang['tax_fee'] }}
                                                        :{{ $ra['format_rate_price'] }}</div>

                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                    @if($activity['act_goods_list_num'] > 2)
                                        <div class="kuan"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        @foreach($activity['act_goods_list'] as $goods)
                            <div class="cg-item @if($loop->last) last @endif">
                                <div class="c-col cg-name">

                                    @if($goods['extension_code'] == '')
                                        <input name="cart_info[]" type="hidden"
                                               value="{{ $goods['ru_id'] }}|{{ $goods['rec_id'] }}_{{ $goods['goods_id'] }}_{{ $goods['freight'] }}_{{ $goods['tid'] }}">
                                    @endif

                                    @if($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')
                                        <div class="p-img"><img src="{{ $goods['goods_thumb'] }}" width="48"
                                                                height="48"></div>
                                        <div class="p-info">
                                            <div class="p-name package-name">{{ $goods['goods_name'] }}<span
                                                        class="red">（{{ $lang['remark_package'] }}）</span></div>
                                            <div class="package_goods" id="suit_{{ $goods['goods_id'] }}">
                                                <div class="title">{{ $lang['contain_content'] }}：</div>
                                                <ul>
                                                    @foreach($goods['package_goods_list'] as $package_goods_list)
                                                        <li>
                                                            <div class="goodsName"><a
                                                                        href="goods.php?id={{ $package_goods_list['goods_id'] }}"
                                                                        target="_blank">{{ $package_goods_list['goods_name'] }}</a>
                                                            </div>
                                                            <div class="goodsNumber">
                                                                x{{ $package_goods_list['goods_number'] }}</div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @else
                                        @if($order['extension_code'] == 'seckill')
                                            <a href="seckill.php?id={{ $seckill_id }}&act=view " target="_blank">
                                                <div class="p-img">
                                                    <img src="{{ $goods['goods_thumb'] }}" width="48" height="48">
                                                </div>
                                                <div class="p-info">
                                                    <div class="p-name">
                                                        @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                            <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                        @endif

                                                        @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                                        {{ $goods['goods_name'] }}
                                                    </div>
                                                    <div class="p-attr">{!! nl2br($goods['goods_attr']) !!}</div>
                                                    <div class="p-attr">
                                                        @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                            <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        @else
                                            <a href="{{ $goods['url'] }} " target="_blank">
                                                <div class="p-img">
                                                    <img src="{{ $goods['goods_thumb'] }}" width="48" height="48">
                                                </div>
                                                <div class="p-info">
                                                    <div class="p-name">
                                                        @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                            <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                        @endif

                                                        @if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
                                                        {{ $goods['goods_name'] }}
                                                    </div>
                                                    <div class="p-attr">{!! nl2br($goods['goods_attr']) !!}</div>
                                                    <div class="p-attr">
                                                        @if($goods['group_id'] && $goods['parent_id'] != 0)
                                                            <em class="gds-type gds-type-store">{{ $lang['parts'] }}</em>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                                <div class="c-col cg-price">
                                    <span class="fr cl">
                                        @if($goods['rec_type'] == 5)
                                            {{ $lang['Deposit_flow'] }}：{{ $goods['formated_presale_deposit'] }}
                                        @else
                                            {{ $goods['goods_price_format'] }}
                                        @endif
                                    </span>
                                    @if($goods['dis_amount'] > 0)
                                        <span class="ftx-01 fr cl fs12">({{ $lang['Discount_flow'] }}
                                            ：{{ $goods['discount_amount'] }})</span>
                                    @endif
                                </div>
                                <div class="c-col cg-number">x{{ $goods['goods_number'] }}</div>
                                <div class="c-col cg-state">
                                    <span>
                                        @if($goods['attr_number'])
                                            @if($goods['attr_number']  >= $goods['goods_number'] || $goods['rec_type'] == '6' || $goods['extension_code'] == 'presale')
                                                {{ $lang['Have_goods'] }}
                                                <input name="rec_number" type="hidden" data-id="{{ $goods['rec_id'] }}"
                                                       value="0">
                                            @else
                                                <font style="color:#e4393c">{{ $lang['Stock_goods_null'] }}</font>
                                                <input name="rec_number" type="hidden" data-id="{{ $goods['rec_id'] }}"
                                                       value="1">

                                            @endif
                                        @else
                                            <font style="color:#e4393c">{{ $lang['No_goods'] }}</font>
                                            <input name="rec_number" type="hidden" data-id="{{ $goods['rec_id'] }}"
                                                   value="1">
                                        @endif

                                        @if($goodsRu['shipping'] || !$goods['is_real'])
                                            @if($goods['rec_shipping'] == 1)
                                                <input name="shipping_prompt" type="hidden"
                                                       data-id="{{ $goods['rec_id'] }}" value="0">
                                            @else
                                                <input name="shipping_prompt" type="hidden"
                                                       data-id="{{ $goods['rec_id'] }}" value="1">
                                            @endif
                                        @else
                                            <input name="shipping_prompt" type="hidden" data-id="{{ $goods['rec_id'] }}"
                                                   value="1">
                                        @endif
                                    </span>
                                </div>
                                <div class="c-col cg-sum">
                                    <div>{{ $goods['formated_subtotal'] }}</div>

                                    @if($cross_border_version)
                                        @foreach($rate_arr as $ra)
                                            @if($ra['rec_id'] == $goods['rec_id'])
                                                <div id="rate_price_{{ $goods['rec_id'] }}"
                                                     style="font-size:12px;color:#666;font-weight: normal;">{{ $lang['tax_fee'] }}
                                                    :{{ $ra['format_rate_price'] }}</div>

                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="cg-item-line"></div>
                                <i class="dian"></i>
                            </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>

        <div class="ck-order-remark">
            <input name="postscript[{{ $goodsRu['ru_id'] ?? 0 }}]" type="text" maxlength="60" size="15" class="text" placeholder="{{ $lang['order_Remarks_desc'] }}" autocomplete="off" >
            <span class="prompt">{{ $lang['order_Remarks_desc_one'] }}</span>
        </div>

    @endforeach
</div>
