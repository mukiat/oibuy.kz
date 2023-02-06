<p>
<style type="text/css">
    body,td { font-size:13px; }
    em{ font-style:normal;}
</style>
</p>
<h1 align="center">{{ __('admin/order.order_info') }}</h1>
<table width="100%" cellpadding="1">
    <tbody>
        <tr>
            <td width="13%">{{ __('admin/order.print_buy_name') }}</td>
            {{--购货人姓名--}}
            <td width="15%">@if(!empty($order['user_name'])) {{ $order['user_name'] }} @else {{ __('admin/order.anonymous') }} @endif</td>
            <td width="9%" align="right">{{ __('admin/order.label_order_time') }}</td>
            {{--下订单时间--}}
            <td width="20%">{{ $order['order_time'] ?? '' }}</td>
            <td width="10%" align="right">{{ __('admin/order.label_payment') }}</td>
            {{--支付方式--}}
            <td width="10%">{{ $order['pay_name'] ?? '' }}</td>
            <td width="10%" align="right">{{ __('admin/order.print_order_sn') }}</td>
            {{--订单号--}}
            <td width="13%">{{ $order['order_sn'] ?? '' }}</td>
        </tr>
        <tr>
            <td>{{ __('admin/order.label_pay_time') }}</td>
            <td>{{ $order['pay_time'] ?? '' }}</td>
            {{--付款时间--}}
            <td align="right">{{ __('admin/order.label_shipping_time') }}</td>
            {{--发货时间--}}
            <td>{{ $order['shipping_time'] ?? '' }}</td>
            <td align="right">{{ __('admin/order.label_shipping') }}</td>
            {{--配送方式--}}
            <td>{{ $order['shipping_name'] ?? '' }}</td>
            <td align="right">{{ __('admin/order.label_invoice_no') }}</td>
            {{--发货单号--}}
            <td>{{ $order['invoice_no'] ?? '' }}</td>
        </tr>
        <tr>
            <td>{{ __('admin/order.label_consignee_address') }}</td>
            <td colspan="7">
                {{--收货人地址--}}
                [{{ $order['region'] ?? '' }}] &nbsp; {{ $order['address'] ?? '' }} &nbsp;
                {{--收货人姓名--}}
                {{ __('admin/order.label_consignee') }} {{ $order['consignee'] ?? '' }} &nbsp;
                {{--邮政编码--}}
                @if(!empty($order['zipcode'])) {{ __('admin/order.label_zipcode') }} {{ $order['zipcode'] }} &nbsp;  @endif
                {{--联系电话--}}
                @if(!empty($order['tel'])) {{ __('admin/order.label_tel') }} {{ $order['tel'] }} &nbsp;  @endif
                {{--手机号码--}}
                @if(!empty($order['mobile'])) {{ __('admin/order.label_mobile') }} {{ $order['mobile'] }} @endif
            </td>
        </tr>
        <tr>
            <td colspan="8">&nbsp;</td>
        </tr>
        <td colspan="8" align="right">
            <p><img src="{{ $shop_url }}barcodegen.php?filetype=png&scale=1&rotation=0&size=40&text={{ $order['order_sn'] ?? '' }}&thickness=40&checksum=&code=code39" /></p>
            <p class="font14 fontweight" style="font-family:Arial; padding-right:10px;">{{ $order['order_sn'] ?? '' }}</p>
        </td>
        <tr>
            <td colspan="8">&nbsp;</td>
        </tr>
    </tbody>
</table>
<table width="100%" border="1" style="border-collapse:collapse;border-color:#000;">
    <tbody>
        <tr align="center">
            <!-- 商品名称 -->
            <td bgcolor="#cccccc">{{ __('admin/order.goods_name') }}</td>
            <!-- 商品货号 -->
            <td bgcolor="#cccccc">{{ __('admin/order.goods_sn') }}</td>
            <!-- 条形码 -->
            <td bgcolor="#cccccc">{{ __('admin/order.bar_code') }}</td>
            <!-- 商品属性 -->
            <td bgcolor="#cccccc">{{ __('admin/order.goods_attr') }}</td>
            <!-- 商品单价 -->
            <td bgcolor="#cccccc">{{ __('admin/order.goods_price') }}</td>
            <!-- 商品数量 -->
            <td bgcolor="#cccccc">{{ __('admin/order.goods_number') }}</td>
            <!-- 商品单位 -->
            <td bgcolor="#cccccc">{{ __('admin/order.measure_unit') }}</td>
            <!-- 价格小计 -->
            <td bgcolor="#cccccc">{{ __('admin/order.subtotal') }}</td>
        </tr>
        @if(!empty($goods_list))
            @foreach($goods_list as $key => $goods)
            <tr>
                <td>
                    {{--商品名称--}}
                    &nbsp;{{ $goods['goods_name'] ?? '' }}
                    @if(!empty($goods['is_gift']))
                        @if(!empty($goods['goods_price']))
                            {{ __('admin/order.remark_favourable') }}
                        @else
                            {{ __('admin/order.remark_gift') }}
                        @endif
                    @endif
                    @if(!empty($goods['parent_id']))
                        {{ __('admin/order.remark_fittings') }}
                    @endif
                </td>
                <td>&nbsp;{{ $goods['goods_sn'] ?? '' }} <!-- 商品货号 --></td>
                <td>{{ $goods['bar_code'] ?? '' }}</td>
                <td>
                    <!-- 商品属性 -->
                    @if(!empty($goods_list[$key]))
                        @foreach($goods_list as $key => $attr)

                            @if(!empty($attr['name'])) {{ $attr['name'] }} : {{ $attr['value'] ?? '' }} @endif

                        @endforeach
                    @endif
                </td>
                <td align="right">{{ $goods['formated_goods_price'] ?? '' }}&nbsp; <!-- 商品单价 --></td>
                <td align="right">{{ $goods['goods_number'] ?? '' }}&nbsp; <!-- 商品数量 --></td>
                <td align="right">{{ $goods['measure_unit'] ?? '' }}&nbsp; <!-- 商品单位 --></td>
                <td align="right">{{ $goods['formated_subtotal'] ?? '' }} &nbsp; <!-- 商品金额小计 --></td>
            </tr>
            @endforeach
        @endif
        <tr>
            {{--发票抬头和发票内容--}}
            <td colspan="4">
                @if(!empty($order['inv_payee'])) {{ __('admin/order.label_inv_payee') }} {{ $order['inv_payee'] }}&nbsp;&nbsp;&nbsp;{{ __('admin/order.label_inv_content') }} {{ $order['inv_content'] ?? '' }}  @endif</td>
            {{--商品总金额--}}
            <td align="right" colspan="4">{{ __('admin/order.label_goods_amount') }} {{ $order['formated_goods_amount'] ?? 0 }}</td>
        </tr>
    </tbody>
</table>
<table width="100%" border="0">
    <tbody>
        <tr align="right">
            <td>
                {{--折扣--}}
                @if(!empty($order['discount']))
                    - {{ __('admin/order.label_discount') }} {{ $order['formated_discount'] }}
                @endif
                {{--包装名称包装费用--}}
                @if(!empty($order['pack_name']) && !empty($order['pack_fee']))
                    + {{ __('admin/order.label_pack_fee') }} {{ $order['formated_pack_fee'] }}
                @endif
                {{--贺卡名称以及贺卡费用--}}
                @if(!empty($order['card_name']) && !empty($order['card_fee']))
                    + {{ __('admin/order.label_card_fee') }} {{ $order['formated_card_fee'] }}
                @endif
                {{--支付手续费--}}
                @if(!empty($order['pay_fee']))
                    + {{ __('admin/order.label_pay_fee') }}{{ $order['formated_pay_fee'] }}
                @endif
                {{--配送费用--}}
                @if(!empty($order['shipping_fee']))
                    + {{ __('admin/order.label_shipping_fee') }} {{ $order['formated_shipping_fee'] }}
                @endif
                {{--保价费用--}}
                @if(!empty($order['insure_fee']))
                    + {{ __('admin/order.label_insure_fee') }}  {{ $order['formated_insure_fee'] }}
                @endif
                {{--订单总金额--}}
                    = {{ __('admin/order.label_order_amount') }} {{ $order['formated_total_fee'] }}
            </td>
        </tr>
        <tr align="right">
            <td>
                <!-- 如果已付了部分款项, 减去已付款金额 -->
                @if(!empty($order['money_paid'])) - {{ __('admin/order.label_money_paid') }} {{ $order['formated_money_paid'] }} @endif

                <!-- 如果使用了余额支付, 减去已使用的余额 -->
                @if(!empty($order['surplus'])) - {{ __('admin/order.label_surplus') }} {{ $order['formated_surplus'] }} @endif

                <!-- 如果使用了积分支付, 减去已使用的积分 -->
                @if(!empty($order['integral_money'])) - {{ __('admin/order.label_integral') }} {{ $order['formated_integral_money'] }} @endif

                <!-- 如果使用了红包支付, 减去已使用的红包 -->
                @if(!empty($order['bonus'])) - {{ __('admin/order.label_bonus') }} {{ $order['formated_bonus'] }} @endif
                {{--应付款金额--}}
                 = {{ __('admin/order.label_money_dues') }}  {{ $order['formated_order_amount'] ?? 0 }}
            </td>
        </tr>
    </tbody>
</table>

<table width="100%" border="0">
    <tbody>
        <tr>
            <!-- 给购货人看的备注信息 -->
            <td>{{ __('admin/order.label_to_buyer') }} {{ $order['to_buyer'] ?? '' }} </td>
        </tr>
        <tr>
            <!-- 发货备注 -->
            <td>{{ __('admin/order.label_invoice_note') }} {{ $order['invoice_note'] ?? ''  }}</td>
        </tr>
        <tr>
            <!-- 支付备注 -->
            <td>{{ __('admin/order.pay_note') }} {{ $order['pay_note'] ?? ''  }}</td>
        </tr>
        <tr>
            <!-- 网店名称, 网店地址, 网店URL以及联系电话 -->
            <td>{{ $shop_name ?? '' }}（@if(!empty($domain_name)) {{ $domain_name }} @else {{ $shop_url ?? '' }} @endif） {{ __('admin/order.label_shop_address') }}{{ $shop_address ?? '' }} &nbsp;&nbsp; {{ __('admin/order.label_service_phone') }} {{ $service_phone ?? '' }}</td>
        </tr>
        <tr align="right">
            <!-- 订单操作员以及订单打印的日期 -->
            <td>{{ __('admin/order.label_print_time') }}{{ $print_time ?? '' }}&nbsp;&nbsp;&nbsp;{{ __('admin/order.action_user') }}：{{ $action_user ?? '' }}</td>
        </tr>
    </tbody>
</table>
