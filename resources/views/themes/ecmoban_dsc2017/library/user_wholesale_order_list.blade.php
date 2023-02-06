<div class="user-order-list user-purchase">

@forelse($orders['order_list'] as $order)

    <dl class="item">
        <dt class="item-t">
            <div class="t-statu">{{ $order['order_status'] }}</div>

@if($order['invoice_no'])

            <div class="logistics">
                <div class="logistics-track">
                    <div class="logistics-t">
                        <i class="logistics-icon"></i>
                    </div>
                    <div class="logistics-c">
                    	<div class="logistics-items" id="retData_{{ $order['order_id'] }}"></div>
                	</div>
                </div>
                <span id="invoice_no_{{ $order['order_id'] }}" style="display:none">{{ $order['invoice_no'] }}</span>
                <span id="shipping_code_name_{{ $order['order_id'] }}" style="display:none">{{ $order['shipping_code_name'] }}</span>
                <span id="express_code_{{ $order['order_id'] }}" style="display:none">{{ $order['express_code'] }}</span>
            </div>

@endif

            <div class="t-info">
                <span class="info-item">{{ $lang['order_number'] }}{{ $order['order_sn'] }}</span>
                <span class="info-item">{{ $order['order_time'] }}</span>
                <span class="info-item hide">
                    <a href="{{ $order['shop_url'] }}" class="user-shop-link">{{ $order['shop_name'] }}</a>

                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goods['goods_id'] }}"
                       class="iconfont icon-kefu user-shop-kefu"></a>
                </span>
            </div>

@if($order['order_over'] == 0 && $order['pay_status'] == 2)

            <div class="t-btn ml10"><a href="javascript:void(0);" data-orderid="{{ $order['order_id'] }}" ectype="userWhoConfirm" class="sc-btn sc-blue-btn">{{ $lang['setup_complete'] }}</a></div>

@if($order['is_settlement'] == 0)


@if($order['is_refund'])

                     <div class="t-btn ml10"><a href="javascript:void(0);" class="sc-btn sc-red-btn">{{ $lang['refund_replacement_applied'] }}</a></div>

@else

                    <div class="t-btn ml10"><a href="user_wholesale.php?act=wholesale_goods_order&order_id={{ $order['order_id'] }}" data-orderid="{{ $order['order_id'] }}" data-refund="{{ $order['is_refund'] ?? 1 }}" class="sc-btn sc-blue-btn">{{ $lang['refund_replacement_appliy'] }}</a></div>

@endif


@endif


@endif


@if($order['order_over'] == 1 && $order['pay_status'] == 2)


@if($order['is_settlement'] == 0)


@if($order['is_refund'])

                        <div class="t-btn ml10"><a href="javascript:void(0);" class="sc-btn sc-red-btn">{{ $lang['refund_replacement_applied'] }}</a></div>

@else

                        <div class="t-btn ml10"><a href="user_wholesale.php?act=wholesale_goods_order&order_id={{ $order['order_id'] }}" data-orderid="{{ $order['order_id'] }}" data-refund="{{ $order['is_refund'] ?? 1 }}" class="sc-btn sc-blue-btn">{{ $lang['refund_replacement_appliy'] }}</a></div>

@endif


@endif


@endif

            <div class="t-price mr0">{{ $order['total_fee'] }}
@if($order['pay_fee'] > 0)
({{ $lang['service_charge'] }}{{ $order['pay_fee'] }})
@endif
</div>
        </dt>
        <dd class="item-c">
            <div class="itemc-left">
            	<div class="itemc-left-info ps-scrollbar-visible">

@foreach($order['order_goods'] as $goods)

                <div class="itemc-goods
@if($loop->last)
last-child
@endif
">
                    <div class="c-img"><a href="{{ $goods['url'] }}"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>
                    <div class="c-info">
                        <div class="c-name"><a href="{{ $goods['url'] }}" target="_blank">{{ $goods['goods_name'] }}</a></div>
                        <div class="c-lie">
                            <span class="c-row">{!! nl2br($goods['goods_attr']) !!}</span>
                            <span class="c-row ftx-07">{{ $goods['goods_price'] }}</span>
                            <span class="c-row last">X{{ $goods['goods_number'] }}</span>
                        </div>
                    </div>
                </div>

@endforeach

                </div>
            </div>

            <div class="itemc-right">
                <div class="p-i-items">

                    <div class="lie">
                        <div class="label">{{ $lang['pay_status'] }}</div>
                        <div class="value">

@if($order['pay_status'] == 0)
{{ $lang['not_paid'] }}
@if($order['status'] != 4)

                        <a href="wholesale_flow.php?step=order_pay&order_id={{ $order['order_id'] }}"  class="sc-btn sc-blue-btn ml10" target="_blank">{{ $lang['go_pay'] }}</a>

@endif

@else
{{ $lang['Already_paid'] }}
@endif
</div>
                    </div>
                    <div class="lie">
                        <div class="label">{{ $lang['contact_username'] }}</div>
                        <div class="value">{{ $order['consignee'] }}</div>
                    </div>
                    <div class="lie">
                        <div class="label">{{ $lang['contact_phone'] }}</div>
                        <div class="value">{{ $order['mobile'] }}</div>
                    </div>
                    <div class="lie">
                        <div class="label">{{ $lang['address_list'] }}</div>
                        <div class="value">{{ $order['address'] }}</div>
                    </div>
                    <div class="lie">
                        <div class="label">{{ $lang['invoice_type'] }}</div>
                        <div class="value">{{ $lang['need_invoice'][$order['invoice_type']] }}</div>
                    </div>

@if($order['invoice_type'] == 0)

                    <div class="lie">
                        <div class="label">{{ $lang['inv_payee'] }}</div>
                        <div class="value">{{ $order['inv_payee'] }}</div>
                    </div>

@if($order['tax_id'])

                    <div class="lie">
                        <div class="label">{{ $lang['label_tax_id'] }}</div>
                        <div class="value">{{ $order['tax_id'] }}</div>
                    </div>

@endif

                    <div class="lie">
                        <div class="label">{{ $lang['invoice_content'] }}</div>
                        <div class="value">{{ $order['inv_content'] }}</div>
                    </div>

@endif

                    <div class="lie">
                        <div class="label">{{ $lang['payment_method'] }}</div>
                        <div class="value">{{ $order['pay_name'] }}</div>
                    </div>

@if($order['postscript'])

                    <div class="lie last">

                        <div class="label">{{ $lang['purchase_handle'] }}</div>
                        <div class="value">{{ $order['postscript'] }}</div>
                    </div>

@endif

                </div>
            </div>
            <a href="javascript:delete_wholesale_order({{ $order['order_id'] }})" class="pur-remove" ectype="pur-remove"><i class="iconfont icon-remove-alt"></i></a>
        </dd>
    </dl>

@empty

    <div class="no_records">
	<i class="no_icon"></i>
        <div class="no_info">
            <h3>{{ $lang['wholesale_order_notic'] }}</h3>
        </div>
    </div>

@endforelse

</div>


@if($orders['page_count'] > 1)

<div class="pages pages_warp">{!! $orders['pager'] !!}</div>

@endif



@if($orders['order_list'])

<script type="text/javascript">
$(function(){

@foreach($orders['order_list'] as $order)

@if($order['invoice_no'])

        $('#retData_' + {{ $order['order_id'] }}).html("<center>" + json_languages.logistics_tracking_in + "</center>");
        var expressid = $('#shipping_code_name_'+{{ $order['order_id'] }}).html();
        var expressno = $('#invoice_no_'+{{ $order['order_id'] }}).html();

        var order_id = '{{ $order['order_id'] }}';
        var express_code = $('#express_code_'+{{ $order['order_id'] }}).html() || '';

        $.ajax({
            url: "wholesale_tracker_shipping",
            type: "get",
            data:'act=asyn&type=' + expressid + '&postid=' + expressno +'&order_id=' + order_id + '&express_code='+ express_code,
            success: function(data,textStatus){
                $("#retData_" + {{ $order['order_id'] }}).html(data.content);
            },
            error: function(o){
            }
        });

@endif

@endforeach


	//用户中心 物流跟踪
	$(".logistics-track").hover(function(){
		$(this).addClass("hover");
		$(this).parents("tr").css({"z-index":99,"position":"relative"});
	},function(){
		$(this).removeClass("hover");
		$(this).parents("tr").css({"z-index":"auto","position":"static"});
	});

	$(".itemc-left-info").perfectScrollbar("destroy");
	$(".itemc-left-info").perfectScrollbar();
});
</script>

@endif

