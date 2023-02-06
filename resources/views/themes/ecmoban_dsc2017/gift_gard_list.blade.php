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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/gift.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.css') }}" />
</head>
<body>
@include('frontend::library/page_header_common')
<div class="ecsc-breadcrumb w1200 w">
	@include('frontend::library/ur_here')
</div>

<div class="w1200 w">
	<div class="AreaL">

        @include('frontend::library/history')

    </div>
    <div class="AreaR">

        @include('frontend::library/gift_gard_list')
        @include('frontend::library/pages')

    </div>
</div>
{{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
@include('frontend::library/page_footer')

<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/region.js') }}"></script>
<script type="text/javascript">
$(function(){
	$("*[ectype='openLayer']").on("click",function(){
		var value = $(this).data("value");

		//添加收货地址信息
		Ajax.call('gift_gard.php?act=edit_Consignee&goodsId=' + value,'', function(data){
			pb({
				id:"gift_gard_content",
				title:json_languages.gift_gard_title,
				width:805,
				height:300,
				content:data.content, 	//调取内容
				drag:false,
				foot:true,
				ok_title:json_languages.Confirmation_delivery,
				cl_title:json_languages.cancel,
				onOk:function(){
					if(gift_update("form[name='theForm']") == false){
						gift_update("form[name='theForm']");
						return false;
					}else{
						$("form[name='theForm']").submit();
					}
				}
			});

			$.levelLink(1);

		}, 'POST','JSON');
	});

	function gift_update(frm){
		var obj = $(frm),
			consignee = obj.find("[name='consignee']").val(),
			country   = obj.find("[name='country']").val(),
			province  = obj.find("[name='province']").val(),
			city	  = obj.find("[name='city']").val(),
			district  = obj.find("[name='district']").val(),
			street 	  = obj.find("[name='street']").val(),
			address   = obj.find("[name='address']").val(),
			mobile    = obj.find("[name='mobile']").val(),
			email     = obj.find("[name='email']").val(),
			shipping_time = obj.find("[name='shipping_time']").val();

		if(consignee == ""){
			pbDialog(json_languages.consignee,'',0);
			return false;
		}else if(country == 0){
			pbDialog(json_languages.Country,'',0);
			return false;
		}else if(province == 0){
			pbDialog(json_languages.Province,'',0);
			return false;
		}else if(city == 0){
			pbDialog(json_languages.City,'',0);
			return false;
		}else if(!$('#selDistricts_').is(":hidden") && district == 0){
			pbDialog(json_languages.District,'',0);
			return false;
		}else if(!$('#selStreets_').is(":hidden") && street == 0){
			pbDialog(json_languages.Street,'',0);
			return false;
		}else if(address == ""){
			pbDialog(json_languages.Detailed_address_null,'',0);
			return false;
		}else if(!Utils.isTel(mobile) || mobile.length != 11){
			pbDialog(json_languages.msg_phone_not,'',0);
			return false;
		}else{
			return true;
		}
	}
});
</script>
</body>
</html>
