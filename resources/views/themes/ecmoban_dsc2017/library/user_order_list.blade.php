<div class="user-order-list user-order-list-special">

@forelse($orders['order_list'] as $order)


@if($order['order_over'] == 1)

<input name="order_id[]" value="{{ $order['order_id'] }}" type="hidden">

@endif

<dl class="item">
	<dt class="item-t item-t-qb">
		<div class="t-statu">
        	<div class="t-statu-name" id="ss_received_{{ $order['order_id'] }}">

@if($order['order_over'] != 1)
{{ $order['order_status'] }}
@endif



@if($order['activity_lang'] != '')

                <em class="em-promotion em-promotion-1">{{ $order['activity_lang'] }}</em>

@endif

        	</div>

@if($order['allow_order_delay'] && $order['allow_order_delay_handler'])

            <div class="fr">
                <div class="time fl" ectype="time" data-time="{{ $order['auto_delivery_time'] }}">
                    <span></span><span class="days">00</span><em>:</em><span class="hours">00</span><em>:</em><span class="minutes">00</span><em>:</em><span class="seconds">00</span>
                </div>
            </div>

@endif

        </div>
		<div class="t-info">
			<span class="info-item">{{ $lang['order_number'] }}：{{ $order['order_sn'] }}</span>
			<span class="info-item">{{ $order['order_time'] }}</span>
			<span class="info-item">{{ $order['consignee'] }}</span>
		</div>
		<div class="t-price">{{ $order['total_fee'] }}</div>
	</dt>
	<dd class="item-c">
		<div class="c-left">

@foreach($order['order_goods'] as $goods)

			<div class="c-goods" ectype="c-goods"
@if($loop->index > 2)
 style="display:none;"
@endif
>

@if($goods['og_extension_code'] != 'package_buy')

					<div class="c-img"><a href="{{ $goods['url'] }}"><img src="
@if($goods['goods_thumb'])
{{ $goods['goods_thumb'] }}
@else
{{ $order['no_picture'] }}
@endif
" alt=""></a></div>

@else

				<div class="c-img"><a href="./package.php"><img src="{{ $goods['goods_thumb'] }}" width="70" height="70" /></a></div>

@endif

				<div class="c-info">
					<div class="o-info-lm">
						@if($goods['og_extension_code'] == 'package_buy')
							@if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
							{{ $goods['goods_name'] }}
							<span class="red">{{ $lang['remark_package'] }}</span>
						@else
							<a href="{{ $goods['url'] }}" class="info-name" target="_blank" title="{{ $goods['goods_name'] }}">
								@if (!empty($goods['country_icon']))<img src="{{$goods['country_icon']}}" width="20px" style="position: relative; top:-1px;" />@endif
								{{ $goods['goods_name'] }}
							</a>
						@endif
                    </div>
                    <div class="fl">
					<div class="info-price mr10" style="width: auto;"><b>{{ $goods['goods_price'] }}</b><i>×</i><span>{{ $goods['goods_number'] }}</span></div>

@if($goods['trade_id'] &&  $goods['og_extension_code'] != 'package_buy')
<a href="user_order.php?act=trade&tradeId={{ $goods['trade_id'] }}&snapshot=true" class="ftx-05 trade_snapshot" target="_blank">{{ $lang['trade_snapshot'] }}</a>
@endif

					</div>

@if($goods['goods_attr'])
<div class="info-attr">{{ $goods['goods_attr'] }}</div>
@endif

				</div>
			</div>

@endforeach


@if($order['order_goods_count'] > 3)

            <span class="ellipsis">......</span>
            <a href="javascript:void(0);" class="order-prolist-more" ectype="opm">{{ $lang['see_more'] }}︾</a>

@endif

		</div>
        <div class="c-outhr">
            <span class="info-item">
            <a
            @if($order['ru_id'] > 0)
                href="merchants_store.php?merchant_id={{ $order['ru_id'] }}" target="_blank"
            @else
                href="javascript:void(0);"
            @endif
                class="user-shop-link">{{ $order['shop_name'] }}</a>
            <a id="IM" onclick="openWin(this)" href="javascript:void(0);" ru_id="{{ $order['ru_id'] }}" class="iconfont icon-kefu user-shop-kefu"></a>
            </span>

@if($order['handler_return'])
<span class="info-item"><a href="{{ $order['return_url'] }}"class="ftx-05">{{ $lang['return_apply'] }}</a></span>
@endif

        </div>
		<div class="c-handle" id="ss_msg_{{ $order['order_id'] }}">

@if($order['order_over'] != 1)


@if($goods['og_extension_code'] == 'package_buy' && !$order['is_package'])

					<span style="color:red">{{ $lang['package_already'] }}</span>

@else


@if($order['allow_order_delay'] && $order['allow_order_delay_handler'])

					<a href="javascript:void(0);" class="sc-btn" data-id="{{ $order['order_id'] }}" id="sbumit_order_delay">{{ $order['allow_order_delay_handler'] }}</a>

@endif


@if($action == 'auction')

					<a href="user_order.php?act=auction_order_detail&order_id={{ $order['order_id'] }}"  class="sc-btn">{{ $lang['order_detail'] }}</a>

@else

					<a href="user_order.php?act=order_detail&order_id={{ $order['order_id'] }}"  class="sc-btn">{{ $lang['order_detail'] }}</a>

@endif


@if($order['order_del'] == 1)

	@if($action == 'order_list' || $action == 'auction')
		{{--删除订单--}}
		<a href="javascript:get_order_delete_restore('delete', {{ $order['order_id'] }});" class="sc-btn">{{ $lang['delete_order'] }}</a>

	@elseif($action == 'order_recycle' || $action == 'auction_order_recycle')
			{{--还原订单--}}
			<a href="javascript:get_order_delete_restore('restore', {{ $order['order_id'] }});" class="sc-btn">{{ $lang['reduction'] }}</a>
			<a href="javascript:get_order_delete_restore('thorough', {{ $order['order_id'] }});" class="sc-btn">{{ $lang['delete_order'] }}</a>

	@endif

@endif


@if($order['handler_cancel'] == 1)
	@if($action == 'order_list' || $action == 'auction')
		{{--取消订单--}}
		<a href="user_order.php?act=cancel_order&order_id={{ $order['order_id'] }}" onclick="if (!confirm('{{ trans('user.confirm_cancel') }}')) return false;" class="sc-btn">{{ trans('user.cancel') }}</a>
	@endif
@endif


	@if($order['handler_act'] && $order['original_handler'])

		<a href="user_order.php?act={{ $order['handler_act'] }}&order_id={{ $order['order_id'] }}"
		@if($order['remind']) onclick="if (!confirm('{{ $order['remind'] }}')) return false;" @endif class="sc-btn">
		{{ $order['original_handler'] }}
		</a>

	@endif

	@if(isset($order['handler_comment']) && $order['handler_comment'] == 1)
		@if($action == 'order_list' || $action == 'auction')
			{{--去评价--}}
			<a href="user_message.php?act=commented_view&order_id={{ $order['order_id'] }}" class="sc-btn">{{ __('user.click_review') }}</a>
		@endif
	@endif


	@if($order['order_confirm'] === 1 && $order['is_store_order'] == 0 && $order['is_delete'] == 0)
	{{--再次购买--}}
	<a href="javascript:buy_again({{ $order['order_id'] }});" class="sc-btn">{{ $lang['again_order'] }}</a>

	@endif


@endif


@endif

		</div>
	</dd>
</dl>

@empty

<div class="no_records">
	<i class="no_icon"></i>
    <div class="no_info">
    	<h3>

@if($no_records)

            	{{ $no_records }}

@else

            {{ $lang['no_records'] }}

@endif

        </h3>
    </div>
</div>

@endforelse

</div>


@if($orders['order_list'])

<div class="pages pages_warp">{!! $orders['pager'] !!}</div>

@endif



@if($orders['order_list'])


<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript">
$(function(){
	$("*[ectype='time']").each(function(){
		$(this).yomi();
	});

	//自动确认收货

@if($open_delivery_time == 1)

	$(":input[name='order_id[]']").each(function(index, element) {
		var order_id = $(this).val();
        $.ajax({
			url: "user_order.php",
			type: "get",
			data:'act=return_order_status' + '&order_id=' + order_id,
			dataType: 'json',
			success:function(result){
				if(result.error == 1){
					$('#ss_received_' + order_id).html(result.ss_received);
					$('#ss_msg_' + order_id).html(result.msg);
				}
			}
		});
    });

@endif

});
</script>

@endif

