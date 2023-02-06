<div class="ck-goods-list">

    @foreach($goods_list as $goodsRu)

        <div class="ck-goods-item" ectype="shoppingList">
            <div class="ck-goods-tit">
                <div class="ck-store">

                    @if($goodsRu['ru_id'] == 0)

                        <a href="javascript:;" class="shop-name">{{ $goodsRu['shop_name'] }}</a>

                    @else

                        <a href="{{ $goodsRu['url'] }}" target="_blank"
                           class="shop-name">{{ $goodsRu['shop_name'] }}</a>

                    @endif


                        <a id="IM" onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goodsRu['goods_id'] }}" class="p-kefu
@if($goodsRu['ru_id'] == 0)
                                p-c-violet
@endif
                                " ><i class="iconfont icon-kefu"></i></a>

                </div>

                @if($goods_flow_type == 101)

                    <div class="ck-dis-modes hide">
                        <div class="ck-dis-tit">{{ $lang['shipping_method'] }}：</div>

                        @if($goodsRu['shipping'])

                            <div class="ck-dis-info" ectype="disInfo">
                                <ul class="ck-sp-type">


                                    @if($goodsRu['shipping'])


                                        @foreach($goodsRu['shipping'] as $shipping)


                                            @if($shipping['shipping_code'] != 'cac' && $goodsRu['tmp_shipping_id'] == $shipping['shipping_id'])

                                                <li class="mode-tab-item mode-tab-log shopping-list-checked
@if($goodsRu['shipping_type'] == 0 && $shipping['default'] == 1)
                                                        item-selected
@endif
                                                        " ectype="tabLog" data-ruid="{{ $goodsRu['ru_id'] }}"
                                                    data-type="0" data-shipping="{{ $shipping['shipping_id'] }}"
                                                    data-shippingcode="{{ $shipping['shipping_code'] }}">
                                                    <span>{{ $shipping['shipping_name'] }}</span>
                                                </li>

                                            @endif


                                        @endforeach


                                    @endif



                                    @if($goodsRu['ru_id'] == 0 && $goodsRu['self_point'] != "")

                                        <li class="mode-tab-item shopping-list-checked
@if($goodsRu['shipping_type'] == 1)
                                                item-selected
@endif
                                                " ectype="tabCac" data-ruid="{{ $goodsRu['ru_id'] }}" data-type="1"
                                            data-shipping="{{ $goodsRu['self_point']['shipping_id'] }}"
                                            data-shippingcode="{{ $goodsRu['self_point']['shipping_code'] }}">
                                            <span>{{ $lang['Door_self'] }}</span>
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

                                                        <li class="logistics_li
@if($goodsRu['tmp_shipping_id'] == $shipping['shipping_id'])
                                                                item-selected
@endif
                                                                " data-ruid="{{ $goodsRu['ru_id'] }}" data-type="0"
                                                            data-shipping="{{ $shipping['shipping_id'] }}"
                                                            data-shippingcode="{{ $shipping['shipping_code'] }}"
                                                            data-text="{{ $shipping['shipping_name'] }}">
                                                            <span>{{ $shipping['shipping_name'] }}</span>
                                                            <em>（
                                                                @if($shipping['shipping_fee'])
                                                                    {{ $shipping['format_shipping_fee'] }}
                                                                @else
                                                                    {{ $lang['Free_shipping'] }}
                                                                @endif
                                                                ）</em>
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

                                        @if($goodsRu['self_point'] != "")

                                            <div class="mode-list shipping_{{ $goodsRu['self_point']['shipping_id'] }}">
                                                <div class="mode-list-item">
                                                    <label class="tit">{{ $lang['Place_reference'] }}：</label>
                                                    <span class="value">{{ $goodsRu['self_point']['name'] }}</span>
                                                    <a href="javascript:void(0);" class="ftx-05" ectype="flow_dialog"
                                                       data-value='{"mark":0,"width":700,"height":350,"divid":"picksite_box","title":"{{ $lang['select_Place_reference'] }}","url":"ajax_flow.php?step=pickSite"}'>{{ $lang['modify'] }}</a>
                                                </div>
                                                <div class="mode-list-item">
                                                    <label class="tit">{{ $lang['Self_mentioning_date'] }}：</label>
                                                    <span class="value">{{ $goodsRu['self_point']['shipping_dateStr'] }}</span>
                                                    <a href="javascript:void(0);" class="ftx-05" ectype="flow_dialog"
                                                       data-value='{"mark":1,"width":600,"height":250,"divid":"take_their_time","title":"{{ $lang['Self_mentioning_date'] }}","url":"ajax_flow.php?step=pickSite&mark=1"}'>{{ $lang['modify'] }}</a>
                                                </div>
                                                <input type="hidden" name="point_id"
                                                       value="{{ $goodsRu['self_point']['point_id'] }}">
                                                <input type="hidden" name="shipping_dateStr"
                                                       value="{{ $goodsRu['self_point']['shipping_dateStr'] }}">
                                            </div>

                                        @endif

                                    </div>
                                </div>
                            </div>

                        @else


                            @if(!$user_address)

                                <span class="ftx-01">请填写收货地址</span>

                            @else

                                <span class="ftx-01">暂不支持配送</span>

                            @endif


                        @endif


                        <input type="hidden" name="ru_id[]" value="{{ $goodsRu['ru_id'] }}"/>
                        <input type="hidden" name="shop_name[]" value="{{ $goodsRu['shop_name'] }}"/>

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
                            <input type="hidden" name="shipping_code[]" class="shipping_code_{{ $goodsRu['ru_id'] }}"
                                   value="{{ $shipping['shipping_code'] }}" autocomplete="off"/>

                        @endif

                        <input type="hidden" name="shipping_type[]" class="shipping_type_{{ $goodsRu['ru_id'] }}"
                               value="0" autocomplete="off"/>
                    </div>

                @endif

            </div>
            <div class="ck-goods-cont">

                @foreach($goodsRu['goods_list'] as $key => $goods)

                    <div class="cg-item
@if($loop->last)
                            last
@endif
                            ">
                        <div class="c-col cg-name">

                            @if($goods['extension_code'] == '')

                                <input name="cart_info[]" type="hidden"
                                       value="{{ $goods['ru_id'] }}|{{ $goods['rec_id'] }}_{{ $goods['goods_id'] }}_{{ $goods['freight'] }}_{{ $goods['tid'] }}">

                            @endif


                            @if($goods['goods_id'] > 0 && $goods['extension_code'] == 'package_buy')

                                <div class="p-img"><img src="{{ skin('images/17184624079016pa.jpg') }}" width="48" height="48"></div>
                                <div class="p-info">
                                    <div class="p-name package-name">{{ $goods['goods_name'] }}<span
                                                class="red">（{{ $lang['remark_package'] }}）</span></div>
                                    <div class="package_goods" id="suit_{{ $goods['goods_id'] }}">
                                        <div class="title">包含内容：</div>
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

                                <a href="
@if($order['extension_code'] == 'seckill')
                                        seckill.php?id={{ $seckill_id }}&act=view
@else
                                {{ $goods['url'] }}
                                @endif
                                        " target="_blank">
                                    <div class="p-img"><img src="{{ $goods['goods_thumb'] }}" width="48" height="48">
                                    </div>
                                    <div class="p-info">
                                        <div class="p-name">{{ $goods['goods_name'] }}</div>
                                        <div class="p-attr">{!! nl2br($goods['goods_attr_text']) !!}</div>
                                    </div>
                                </a>

                            @endif

                        </div>
                        <div class="c-col cg-price">
                    <span class="fr cl">
@if($goods['rec_type'] == 5)
                            {{ $lang['Deposit_flow'] }}：{{ $goods['formated_presale_deposit'] }}
                        @else
                            {{ $goods['cart_goods_price_formatted'] }}
                        @endif
</span>

                            @if($goods['dis_amount'] > 0)

                                <span class="ftx-01 fr cl fs12">({{ $lang['Discount_flow'] }}
                                    ：{{ $goods['discount_amount'] }})</span>

                            @endif

                        </div>
                        <div class="c-col cg-number">x{{ $goods['total_number'] }}</div>
                        <div class="c-col cg-state">
                    <span>

@if($goods['total_number'])

                            {{ $lang['Have_goods'] }}
                            <input name="rec_number" type="hidden" id="{{ $goods['rec_id'] }}" value="0">

                        @else

                            <font style="color:#e4393c">{{ $lang['No_goods'] }}</font>
                            <input name="rec_number" type="hidden" id="{{ $goods['rec_id'] }}" value="1">

                        @endif


                        @if($goodsRu['shipping'] || !$goods['is_real'])

                            <input name="shipping_prompt" type="hidden" id="{{ $goods['rec_id'] }}" value="0">

                        @else

                            <input name="shipping_prompt" type="hidden" id="{{ $goods['rec_id'] }}" value="1">

                        @endif

                    </span>
                        </div>
                        <div class="c-col cg-sum">{{ $goods['total_price_formatted'] }}</div>
                        <div class="cg-item-line"></div>
                        <i class="dian"></i>
                    </div>

                @endforeach

            </div>
        </div>

    @endforeach

</div>
