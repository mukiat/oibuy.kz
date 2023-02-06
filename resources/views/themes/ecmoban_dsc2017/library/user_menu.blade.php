
<div class="side-menu">
	<dl>
        <dt><i class="square"></i><span>{{ $lang['oreder_core'] }}</span></dt>
		<dd>
			<p
@if($action == 'order_list' || $action == 'order_detail' || $action == 'order_recycle')
 class="current"
@endif
><a href="user_order.php?act=order_list" target="_self">{{ $lang['label_order'] }}</a></p>
			<p
@if($action == 'address_list' || $action == 'address')
 class="current"
@endif
><a href="user_address.php?act=address_list" target="_self">{{ $lang['label_address'] }}</a></p>
			<p
@if($action == 'booking_list')
 class="current"
@endif
><a href="user.php?act=booking_list" target="_self">{{ $lang['label_booking'] }}</a></p>
			<p
@if($action == 'return_list' || $action == 'return_detail')
 class="current"
@endif
><a href="user_order.php?act=return_list" target="_self">{{ $lang['return_list'] }}</a></p>
		</dd>
    </dl>
    <dl>
        <dt><i class="square"></i><span>{{ $lang['user_core'] }}</span></dt>
		<dd>
			<p
@if($action == 'profile' || $action == 'users_log')
 class="current"
@endif
><a href="user.php?act=profile" target="_self">{{ $lang['label_profile'] }}</a></p>
			<p
@if($action == 'account_safe')
 class="current"
@endif
><a href="user.php?act=account_safe" target="_self">{{ $lang['account_safe'] }}</a></p>
			<p
@if($action == 'account_bind')
 class="current"
@endif
><a href="user.php?act=account_bind" target="_self">{{ $lang['account_bind'] }}</a></p>
			<p
@if($action == 'crowdfunding')
 class="current"
@endif
><a href="user_crowdfund.php?act=crowdfunding" target="_self">{{ $lang['crowdfunding'] }}</a></p>
			<p
@if($action == 'collection_list' || $action == 'store_list' || $action == 'focus_brand')
 class="current"
@endif
><a href="user_collect.php?act=collection_list" target="_self">{{ $lang['btn_collect'] }}/{{ $lang['attention'] }}</a></p>
            @if(config('shop.message_board') > 0)
			<p
@if($action == 'message_list')
 class="current"
@endif
><a href="user_message.php?act=message_list" target="_self">{{ $lang['message_list'] }}</a></p>
            @endif
			<p
@if($action == 'notification')
 class="current"
@endif
><a href="user_message.php?act=notification" target="_self">{{ $lang['notification'] }}</a></p>
			
@if($is_dir == 1)

                
@if($affiliate['on'] == 1)

                <p
@if($action == 'drp_affiliate' || $action == 'sales_reward_detail')
 class="current"
@endif
><a href="user.php?act=drp_affiliate" target="_self">{{ $lang['drp_affiliate'] }}</a></p>
                
@endif

			
@endif

			
@if($is_dir == 0)

				
@if($affiliate['on'] == 1)

				<p
@if($action == 'affiliate')
 class="current"
@endif
><a href="user.php?act=affiliate" target="_self">{{ $lang['affiliate'] }}</a></p>
				
@endif

			
@endif


			
@if($shop_can_comment == 1)

			<p
@if($action == 'comment_list' || $action == 'commented_view')
 class="current"
@endif
><a href="user_message.php?act=comment_list" target="_self">{{ $lang['comment_list'] }}</a></p>
			
@endif

			<p
@if($action == 'take_list')
 class="current"
@endif
><a href="user.php?act=take_list"> {{ $lang['my_take_delivery'] }}</a></p>
			<p
@if($action == 'complaint_list' || $action == 'complaint_apply' )
 class="current"
@endif
><a href="user_order.php?act=complaint_list"> {{ $lang['Trade_complaint'] }}</a></p>
			<p
@if($action == 'invoice' || $action == 'vat_invoice_info')
 class="current"
@endif
><a href="user.php?act=invoice"> {{ $lang['my_invoice'] }}</a></p>
			
@if($is_illegal == 1)

			<p
@if($action == 'illegal_report' || $action == 'goods_report')
 class="current"
@endif
><a href="user.php?act=illegal_report"> {{ $lang['illegal_report'] }}</a></p>
			
@endif

        </dd>
	</dl>
    <dl>
        <dt><i class="square"></i><span>{{ $lang['account_center'] }}</span></dt>
		<dd>
            @if ($cfg['use_bonus'] == 1)
			<p
@if($action == 'bonus')
 class="current"
@endif
><a href="user_activity.php?act=bonus" target="_self">{{ $lang['label_bonus'] }}</a></p>
            @endif
			
@if($use_value_card == 1)

			<p
@if($action == 'value_card')
 class="current"
@endif
><a href="user.php?act=value_card" target="_self">{{ $lang['label_value_card'] }}</a></p>
			
@endif

			<p
@if($action == 'coupons')
 class="current"
@endif
><a href="user_activity.php?act=coupons" target="_self">{{ $lang['label_coupons'] }}</a></p>
			<p
@if($action == 'track_packages')
 class="current"
@endif
><a href="user.php?act=track_packages" target="_self">{{ $lang['label_track_packages'] }}</a></p>
			<p
@if($action == 'account_log' || $action == 'account_detail' || $action == 'account_raply' || $action == 'account_deposit' || $action == 'act_account')
 class="current"
@endif
><a href="user.php?act=account_log" target="_self">{{ $lang['account_log'] }}</a></p>
			<p
@if($action == 'baitiao' || $action == 'repay_bt')
 class="current"
@endif
><a href="user_baitiao.php?act=baitiao" target="_self">{{ $lang['baitiao'] }}</a></p>
        </dd>
    </dl>
	<dl>
        <dt><i class="square"></i><span>{{ $lang['activity_center'] }}</span></dt>
		<dd>
			<p
@if($action == 'auction_list' || $action == 'auction' || $action == 'auction_order_detail')
 class="current"
@endif
><a href="user_activity.php?act=auction_list" target="_self">{{ $lang['label_auction'] }}</a></p>
			<p
@if($action == 'snatch_list')
 class="current"
@endif
><a href="user_activity.php?act=snatch_list" target="_self">{{ $lang['label_snatch'] }}</a></p>
        </dd>
    </dl>
    
@if($wholesale_use)

    <dl>
        <dt><i class="square"></i><span>{{ $lang['wholesale_centre'] }}</span></dt>
		<dd>
            <p
@if($action == 'wholesale_buy')
 class="current"
@endif
><a href="user_wholesale.php?act=wholesale_buy" target="_self">{{ $lang['my_purchase_order'] }}</a></p>
            <p
@if($action == 'wholesale_return_list' || $action == 'wholesale_return_detail')
 class="current"
@endif
><a href="user_wholesale.php?act=wholesale_return_list" target="_self">{{ $lang['wholesale_return_list'] }}</a></p>
            <p
@if($action == 'wholesale_purchase' || $action == 'purchase_info')
 class="current"
@endif
><a href="user_wholesale.php?act=wholesale_purchase" target="_self">{{ $lang['want_buy_order'] }}</a></p>
        </dd>
    </dl>
    
@endif

    
@if($is_merchants > 0)

    <dl>
        <dt><i class="square"></i><span>{{ $lang['Shop_management'] }}</span></dt>
		<dd>
			<p><a href="./seller" target="_blank">{{ $lang['Store_backstage'] }}</a></p>
			<p 
@if($action == 'merchants_upgrade' || $action == 'application_grade')
 class="current"
@endif
><a href="user.php?act=merchants_upgrade" target="_self">{{ $lang['seller_Grade'] }}</a></p>
		</dd>
    </dl>
    
@endif

</div>
