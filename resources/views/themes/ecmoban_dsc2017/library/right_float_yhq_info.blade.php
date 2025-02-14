<div class="tbar-panel-main" ectype="tbpl-main">
    <div class="tbar-panel-content" data-height="48" ectype="tbpl-content">
    	<div class="coupon-wrap">
            <div class="coupon-type sku_conpon_title">{{ $lang['voucher_tobe_received'] }}{{ $aaa }}</div>

@forelse($kelingqu_coupons as $vo)

            <div class="coupon-item sku_coupon_item" id="sc_1">
                <div class="item-wrap">
                    <div class="coupon-price">
                        <span class="token">{{ config('shop.currency_format', '¥') }}</span>{{ $vo['cou_money'] }}
                    </div>
                    <div class="coupon-info">
                    	<!-- <span class="tit" title="{{ $vo['cou_title'] }}">{{ $vo['cou_title'] }}</span> -->
                    	<span class="tit">{{ $vo['shop_name'] }}</span>
                    	<span class="condition" title="{{ $vo['cou_title'] }}">{{ $lang['man'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}</span>
                    </div>
                    <a class="btn-get q-btn get-coupon" href="javascript:;" cou_id="{{ $vo['cou_id'] }}">{{ $lang['receive_now'] }}</a>
                    <p class="coupon-time">{{ $lang['valid_time'] }}{{ $vo['cou_start_time'] }}{{ $lang['zhi'] }}{{ $vo['cou_end_time'] }}</p>
                </div>
            </div>

@empty

            <div class="coupon-item-notic">{{ $lang['not_voucher'] }}</div>

@endforelse



@if($kelingqu_coupons)

            <a href="{{ url('/') }}/coupons.php?act=coupons_index" class="follow-bottom-more">{{ $lang['see_more'] }}&gt;&gt;</a>

@endif


            <div class="coupon-type user_conpon_title">{{ $lang['Received_couponsa'] }}</div>

@foreach($user_coupons as $vo)

            <div class="coupon-item uc">
                <div class="item-wrap">
                    <div class="coupon-price
@if($vo['cou_type_name'] == 5)
 coupon-price-free
@endif
">
@if($vo['cou_type_name'] == 5)
{{ $lang['lang_goods_coupons']['free_pay'] }}
@else
<span class="token">{{ config('shop.currency_format', '¥') }}</span>{{ $vo['cou_money'] }}
@endif
</div>
                    <div class="coupon-info"><span class="tit" title="{{ $vo['cou_title'] }}">{{ $vo['cou_title'] }}</span><span class="condition">{{ $lang['man'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}</span></div>

@if($vo['keyong'] == 1)

                    <a class="btn-get usable" href="javascript:;">{{ $lang['Current_commodity_available'] }}</a>

@endif

                    <p class="coupon-time">{{ $vo['cou_start_time'] }}{{ $lang['zhi'] }}{{ $vo['cou_end_time'] }}</p>
                </div>
            </div>

@endforeach

            <a href="{{ url('/') }}/user_activity.php?act=coupons" class="follow-bottom-more">{{ $lang['see_more'] }}&gt;&gt;</a>
        </div>
    </div>
</div>
<script type="text/javascript">
    //优惠券领取 bylu
    $(".get-coupon").click(function(e){
		var cou_id = $(this).attr('cou_id');
		receiveCoupon(cou_id);
    });

	function receiveCoupon(cou_id){
		if(user_id > 0){
            var urlDomain;
		    var is_jsonp = $("#is_jsonp").val();

            if (is_jsonp == 1) {
                urlDomain = 'crossDomain?act=coupons_receive';
            } else {
                urlDomain = 'coupons.php?act=coupons_receive';
            }

			$.post(urlDomain,{'cou_id':cou_id},function(data){
				if(data.status=='ok'){
                    $(".item-fore h3").html(data.msg);
                    $(".success-icon").removeClass("i-icon").addClass("m-icon");
					var content =$("#pd_coupons").html();
					pb({
						id:"coupons_dialog",
						title:json_languages.receive_coupons,
						width:550,
						height:140,
						ok_title:json_languages.Immediate_use, 	//按钮名称
						cl_title:json_languages.close, 	//按钮名称
						content:content, 	//调取内容
						drag:false,
						foot:true,
						onOk:function(){
							location.href="{{ url('/') }}/search.php?cou_id="+cou_id
						},
						onCancel:function(){
							$(".cou-data").html(data.content);
							$(".cou-seckill").html(data.content_kill);
						},
					});

					$(".pb-ok").addClass("color_df3134");
				}else{
					$(".success-icon").removeClass("m-icon").addClass("i-icon");
					$(".item-fore h3").addClass("red");
					$(".item-fore h3").html(data.msg);
					var content =$("#pd_coupons").html();
					pb({
						id:"coupons_dialog",
						title:json_languages.receive_coupons,
						width:550,
						height:140,
						ok_title:json_languages.close, 	//按钮名称
						content:content, 	//调取内容
						cl_cBtn:false,
						onOk:function(){}
					});
				}
			},'json');
		}else{
			var back_url = "{{ url('/') }}/coupons.php?act=coupons_index";
			$.notLogin("{{ url('/') }}/get_ajax_content.php?act=get_login_dialog",back_url);
        	return false;
		}
	}
</script>
