<div class="user-order-list user-invoice-list">

@forelse($invoice_list['order_list'] as $key => $list)

    <dl class="item">
        <dt class="item-t">
            <div class="t-info">
                <span class="info-item">{{ $lang['order_number'] }}：{{ $list['order_sn'] }}</span>
                <span class="info-item">{{ $list['add_time'] }}</span>
                <span class="info-item">

@if($list['ru_id'] != 0)

                    <a href="merchants_store.php?merchant_id={{ $list['ru_id'] }}" class="user-shop-link">

@else

                    <a href="user_order.php?act=order_list" class="user-shop-link">

@endif

                    {{ $list['shop_name'] }}</a>
                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $list['ru_id'] }}"  class="iconfont icon-kefu user-shop-kefu"></a>
                </span>
                <span class="info-item">{{ $list['mobile'] }}</span>
            </div>
        </dt>
        <dd class="item-c relative">
            <table>
                <tr>
                    <td>

@foreach($list['order_goods'] as $goods)

                        <div class="c-goods" ectype="c-goods"
@if($loop->index > 2)
 style="display:none;"
@endif
>

@if($goods['extension_code'] != 'package_buy')

                            <div class="c-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

@else

                            <div class="c-img"><a href="javascript:void(0);"><img src="{{ skin('images/17184624079016pa.jpg') }}" alt=""></a></div>

@endif

                            <div class="c-name">

@if($goods['extension_code'] == 'package_buy')

                                <a href="{{ $goods['url'] }}" target="_blank" class="ftx-03">{{ $goods['goods_name'] }}</a>
                                <span class="red">{{ $lang['remark_package'] }}</span>

@else

                                <a href="{{ $goods['url'] }}" target="_blank" class="ftx-03">{{ $goods['goods_name'] }}</a>

@endif

                            </div>
                            <div class="c-number">X{{ $goods['goods_number'] }}</div>
                        </div>

@endforeach


@if($order['order_goods_count'] > 3)

                        <span class="ellipsis">......</span>
                        <a href="javascript:void(0);" class="order-prolist-more" ectype="opm">{{ $lang['see_more'] }}︾</a>

@endif

                    </td>
                    <td class="c-state"><div class="ftx-01">{{ $list['inv_status'] }}</div></td>
                    <td>
                        <div class="c-handle">
                            <a href="#" class="sc-btn">{{ $lang['invoice_desc'] }}</a>
                            <div class="arrowWarp">
                                <i class="arrowTop"></i>
                                <div class="arrowContent">

@if($list['vat_info'])

									<div class="arrowItems">
                                        <div class="arrowItem">
                                            <div class="label">{{ $lang['types'] }}：</div>
                                            <div class="value">{{ $list['invoice_type'] }}</div>
                                        </div>
                                        <div class="arrowItem">
                                            <div class="label">{{ $lang['rise'] }}：</div>
                                            <div class="value">{{ $list['vat_info']['company_name'] }}</div>
                                        </div>
                                        <div class="arrowItem">
                                            <div class="label">{{ $lang['au_bid_status'] }}：</div>
                                            <div class="value">

@if($list['vat_info']['audit_status'] == 1)

												{{ $lang['audited_adopt'] }}

@else

												{{ $lang['audited_not_adopt'] }}

@endif

											</div>
                                        </div>
                                    </div>

@else

                                    <div class="arrowItems">
                                        <div class="arrowItem">
                                            <div class="label">{{ $lang['types'] }}：</div>
                                            <div class="value">{{ $list['invoice_type'] }}</div>
                                        </div>
                                        <div class="arrowItem">
                                            <div class="label">{{ $lang['rise'] }}：</div>
                                            <div class="value">{{ $list['inv_payee'] }}</div>
                                        </div>
                                        <div class="arrowItem">
                                            <div class="label">{{ $lang['content'] }}：</div>
                                            <div class="value">{{ $list['inv_content'] }}</div>
                                        </div>
                                    </div>

@endif

                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </dd>
    </dl>

@empty

    <div class="no_records">
        <i class="no_icon"></i>
        <div class="no_info">
            <h3>
                {{ $lang['no_records'] }}
            </h3>
        </div>
    </div>

@endforelse

</div>

@if($invoice_list['order_list'])

<div class="pages pages_warp">{!! $invoice_list['pager'] !!}</div>

@endif



