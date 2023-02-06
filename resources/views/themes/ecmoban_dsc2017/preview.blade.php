<!doctype html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="{{ $keywords }}" />
<meta name="Description" content="{{ $description }}" />

<title>{{ $page_title }}</title>



<link rel="shortcut icon" href="favicon.ico" />
@include('frontend::library/js_languages_new')
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/store_css.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ skin('css/preview.css') }}">
</head>

<body class="store_visual_body">
   @include('frontend::library/page_header_common')

    <div class="shop-header">
        @include('frontend::library/merchants_store_top')
    </div>
    <div class="shop-list-main">
    	<div class="pc-wrapper">{!! $pc_page['out'] !!}</div>
    </div>
    <input type="hidden" value="{{ $merchant_id }}" id="merchantId" class="merchantId" name="merchantId">
	<input type="hidden" value="{{ $user_id }}" id="user_id" name="user_id">


@if($coupon_store_info['0'])

	<div id="dialogCouponTemp" style="display: none;">
		<div class="coupon-wrap">
			<div class="coupon-item sku_coupon_item">
				<div class="item-wrap">
					<div class="coupon-price">
						{{ $coupon_store_info['0']['format_cou_money'] }}
					</div>
					<div class="coupon-info">
						<!-- <span class="tit" title="{{ $coupon_store_info['cou_title'] }}">{{ $coupon_store_info['cou_title'] }}</span> -->
						<span class="tit">{{ $shop_name }}</span>
						<span class="condition" title="{{ $coupon_store_info['cou_title'] }}">{{ $lang['man'] }}{{ $coupon_store_info['0']['cou_man'] }}{{ $lang['available_full'] }}</span>
					</div>
					<a class="btn-get q-btn get-coupon-dialog" href="javascript:;" cou_id="{{ $coupon_store_info['0']['cou_id'] }}">{{ $lang['follow_and_receive'] }}</a>
					<p class="coupon-time">{{ $lang['valid_time'] }}{{ $coupon_store_info['0']['cou_start_time'] }}{{ $lang['zhi'] }}{{ $coupon_store_info['0']['cou_end_time'] }}</p>
				</div>
			</div>
		</div>
	</div>

@endif


    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position(['ru_id' => $merchant_id]) !!}
    @include('frontend::library/page_footer')

    <input name="warehouse_id" type="hidden" value="{{ $warehouse_id }}">
    <input name="area_id" type="hidden" value="{{ $area_id }}">

@if($is_jsonp)

    <script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/jquery.nyroModal.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>

@else


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.yomi.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.nyroModal.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>

@endif

	<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
	<script type="text/javascript">
		//导航添加链接
		var href = window.location.href
		$(".store_nav_right li").each(function(){
			var url = $(this).data('url');
			if(url == href){
				$(this).addClass('current').siblings().removeClass("current");
			}
		})

		// 领取关注优惠券


@if($coupon_store_info['0'])

		var dialogCoupon = pb({
			id:"dialogCoupon",
			title : json_languages.receive_coupons,
			width:295,
			height:122,
			content:$("#dialogCouponTemp").html(), 	//调取内容
			drag:false,
			foot:false,
		})
		/****会员领取优惠券 start***/
		$(document).on("click",".get-coupon-dialog",function(){
			dialogCoupon.dispose();
			var cou_id = $(this).attr('cou_id');
					var coupon = '';
					if($(this).data('coupon')){
						coupon = $(this).data('coupon');
					}
			receiveCoupon(cou_id,coupon);
		});

		function receiveCoupon(cou_id,coupon){
			if(user_id > 0){
				$.post('get_ajax_content.php?act=ajax_coupons_receive',{'cou_id':cou_id},function(data){
					if(data.status=='ok'){
						$(".item-fore h3").html(data.msg);
						$(".success-icon").removeClass("i-icon").addClass("m-icon");
						var content =$("#pd_coupons").html();
						pb({
							id:"coupons_dialog",
							title:json_languages.receive_coupons,
							width:550,
							height:140,
							ok_title:json_languages.ok, 	//按钮名称
							cl_title:json_languages.close, 	//按钮名称
							content:content, 	//调取内容
							drag:false,
							foot:true,
							onOk:function(){
								// location.href="search.php?cou_id="+cou_id
								location.href = location.href;
							},
							onCancel:function(){
								$(".cou-data").html(data.content);
								$(".cou-seckill").html(data.content_kill);
								$(".cou_shipping").html(data.content_shipping);
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
				var back_url = "coupons.php?act=coupons_index";
				if(coupon == 1){
					back_url = 'coupons.php?act=coupons_info&id=' + cou_id;
				}
				$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
				return false;
			}
		}
		/****会员领取优惠券 end***/

@endif



		var slideType = $("*[data-mode='lunbo']").find("*[data-type='range']").data("slide");
		var length = $(".shop_banner .bd").find("li").length;
		if(slideType == "roll"){
			slideType = "left";
			$(".shop_banner .bd").find("li").show();
		}

		if(length>1){
			$(".shop_banner").slide({titCell:".hd ul",mainCell:".bd ul",effect:slideType,interTime:5000,delayTime:500,autoPlay:true,autoPage:true,trigger:"click",endFun:function(i,c,s){
				$(window).resize(function(){
					var width = $(window).width();
					s.find(".bd li").css("width",width);
				});
			}});
		}else{
			$(".shop_banner .hd").hide();
		}

		$(".adv_module").each(function(){
			var adv_length = $(this).find(".bd li").length;
			var adv_slideType = $("*[data-mode='advImg1']").find("*[data-type='range']").data("slide");

			if(adv_slideType == "roll"){
				adv_slideType = "left";
				$(this).find(".bd li").show();
			}
			if(adv_length>1){
				$(this).slide({titCell:".hd ul",mainCell:".bd ul",effect:adv_slideType,interTime:5000,delayTime:500,autoPlay:true,autoPage:true,trigger:"click"});
			}else{
				$(this).find(".hd").hide();
			}
		});

        //楼层二级分类商品切换
		$("*[ectype='floorItem']").slide({titCell:".hd-tags li",mainCell:".floor-tabs-content",titOnClassName:"current"});

		$("*[ectype='floorItem']").slide({titCell:".floor-nav li",mainCell:".floor-tabs-content",titOnClassName:"current"});

		$("*[ectype='floorItem']").slide({titCell:".tab li",mainCell:".floor-tabs-content",titOnClassName:"current"});

		//第五套楼层模板
		$(".floor-fd-slide").slide({mainCell:".bd ul",effect:"left",autoPlay:false,autoPage:true,vis:4,scroll:1,prevCell:".ff-prev",nextCell:".ff-next"});

		//第六套楼层模板
		$(".floor-brand").slide({mainCell:".fb-bd ul",effect:"left",pnLoop:true,autoPlay:false,autoPage:true,vis:3,scroll:1,prevCell:".fs_prev",nextCell:".fs_next"});

		//楼层轮播图广告
		$("*[data-purebox='homeFloor']").each(function(index, element) {
			var f_slide_length = $(this).find(".floor-left-slide .bd li").length;
			if(f_slide_length > 1){
				$(element).find(".floor-left-slide").slide({titCell:".hd ul",mainCell:".bd ul",effect:"left",interTime:3500,delayTime:500,autoPlay:true,autoPage:true});
			}else{
				$(element).find(".floor-left-slide .hd").hide();
			}
        });

		//店铺模板一
		$(".st_item_slide").slide({titCell:".hd ul",mainCell:".bd ul",effect:"left",interTime:3500,delayTime:500,autoPlay:true,autoPage:true});

        $(document).on("mouseover", ".all_box", function () {
            var all_cats_tcc = $(".all_cats_tcc").html();
            all_cats_tcc = $.trim(all_cats_tcc);

            if(all_cats_tcc == ''){
                var merchant_id = $("#merchantId").val();
                Ajax.call('{{ url("/") }}/ajax_category.php', 'act=cat_store_list&merchant_id=' + merchant_id, cat_store_listResponse, 'GET', 'JSON');
            }
        });

        function cat_store_listResponse(data){
            $(".all_cats_tcc").html(data.content);
        }

        $(function(){
        	//重新加载商品模块
            $("[data-mode='floor']").each(function(){
                var _this = $(this);
                var goods_ids = _this.data("goodsid");
                var warehouse_id = $("input[name='warehouse_id']").val();
                var area_id = $("input[name='area_id']").val();
                var url;

                if(goods_ids){

                    var is_jsonp = $("#is_jsonp").val();

                    if (is_jsonp == 1) {
                        url = 'crossDomain?act=getGuessYouLike';
                    }else{
                        url = 'ajax_dialog.php?act=getGuessYouLike';
                    }

                    Ajax.call(url, 'goods_ids=' + goods_ids + "&warehouse_id=" + warehouse_id + "&area_id=" + area_id + "&type=seller", function(data){
                         if(data.content){
                             _this.find(".view ul").html(data.content);
                         }
                    } , 'GET', 'JSON');
                }
            });

			//营业执照
			$(".nyroModal").nyroModal();
				$("li[ectype='floor_cat_content'].current").each(function(){
				get_homefloor_cat_content(this);
			});

			$("[ectype='identi_floorgoods'].current").each(function(){
				get_homefloor_cat_content(this);
			});
        });
    </script>
</body>
</html>
