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
<link href="{{ skin('css/merchants.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('js/calendar/calendar.min.css') }}" />

<script type="text/javascript" src="{{ asset('js/calendar/calendar.min.js') }}"></script>
</head>

<body class="merchants">
@include('frontend::library/page_header_merchants')
<div class="container settled-container
@if($step != 'stepSubmit')
bg-ligtGary
@else
settled-prog-container
@endif
">
    <div class="w w1200">
        <div class="sett-process-warp clearfix">

@if($step != 'stepSubmit')

            <div class="s-p-steps">
                <ul>
                    <li class="
@if($sid == 1)
curr
@elseif ($sid > 1  || $step == 'stepSubmit')
 cur
@endif
">
                        <span>1. {{ $lang['merchants_step_one'] }}</span>
                        <i class="iconfont icon-arrow-right-alt"></i>
                    </li>
                    <li class="
@if($sid == 2)
 curr
@elseif ($sid > 2  || $step == 'stepSubmit')
 cur
@endif
">
                        <span>2. {{ $lang['merchants_step_two'] }}</span>
                        <i class="iconfont icon-arrow-right-alt"></i>
                    </li>
                    <li class="
@if($sid == 3)
 curr
@elseif ($sid > 3 || $step == 'stepSubmit')
 cur
@endif
">
                        <span>3. {{ $lang['merchants_step_three'] }}</span>
                        <i class="iconfont icon-arrow-right-alt"></i>
                    </li>
                    <li class="last
@if($step == 'stepSubmit')
 curr
@elseif ($sid > 4)
 cur
@endif
">
                        <span>4. {{ $lang['merchants_step_four'] }}</span>
                    </li>
                </ul>
            </div>

@endif



@if($sid == 1)

            <div class="panel">
                <form id="stepForm" action="merchants_steps_action.php" method="post" name="stepForm">
                    <div class="panel-content">
                        <div class="bg-warp sid-frist">
                            <div class="title">{{ $lang['merchants_step_five'] }}</div>
                            <div class="textareay">
                                <div class="agreement">
                                    {!! $steps['article_centent'] !!}
                                </div>
                            </div>
                            <div class="btn-group">
                                <input name="agreement" type="hidden" value="1" />
                                <input name="nextStepBtn" class="btn" type="submit" value="{{ $steps['fields_next'] }}" />
                            </div>
                        </div>
                    </div>
                @csrf </form>
            </div>

@else

            <div class="panel">

@if($step != 'stepSubmit')

            <form enctype="multipart/form-data" id="stepForm" method="post" action="merchants_steps_action.php" name="stepForm">
                <div class="panel-content">
                    <div class="bg-warp">
                        <div class="title">
                            <span>{{ $process['process_title'] }}</span>
                        </div>

@foreach($steps_title as $title)


@if($title['special_type'] == 1 && $title['fields_special'] != '')

                            <div class="prompt">{!! $title['fields_special'] !!}</div>

@endif



@if($title['steps_style'] == 0)

                                @include('frontend::library/basic_type')

@elseif ($title['steps_style'] == 1)

                                @include('frontend::library/shop_type')

@elseif ($title['steps_style'] == 2)

                                @include('frontend::library/cate_type')

@elseif ($title['steps_style'] == 3)

                                @include('frontend::library/brank_type')

@elseif ($title['steps_style'] == 4)

                                @include('frontend::library/shop_info')

@endif


@if($title['special_type'] == 2 && $title['fields_special'] != '')

                            <div class="btn-group">

@if($brandView != 'addBrand')

                                    {{ $title['fields_special'] }}

@endif

                            </div>

@endif


@endforeach

                    </div>
                </div>

@if($brandView != 'addBrand')

                <div class="btn-group">
                    <input type="hidden" name="numAdd" value="1" id="numAdd" />
                    <input type="hidden" name="pid_key" value="{{ $pid_key }}" />
                    <input type="hidden" name="sid" value="{{ $sid }}" />
                    <input type="hidden" name="step" value="{{ $step }}" />


@if($pid_key>1 || $sid>=3)

                    <input type="button" value="{{ $lang['merchants_step_Seven'] }}" class="btn btn-w" id="js-pre-step" />

@endif



@if($brandView == 'brandView')

                    <input type="hidden" name="brandView" value="{{ $brandView }}" />
                    <input type="submit" value="{{ $lang['Preservation'] }}" class="btn" id="nextStepBtn" />

@elseif ($brandView == 'add_brand')

                    <input type="hidden" name="brandView" value="{{ $brandView }}" />

@else


@if($process['fields_next'])

                        <input type="submit" value="{{ $process['fields_next'] }}" class="btn" id="nextStepBtn" />

@endif


@endif

                </div>

@endif

            @csrf </form>

@else

            <div class="settled-prog-warp">
                <div class="che"><img src="{{ skin('images/che.png') }}"></div>
                <div class="spw-cont">
                    <div class="spw-tit"><h1>{{ $lang['review_progress'] }}</h1></div>

                    <div class="spw-mian
@if($shop_info['merchants_audit'] == 0)
spw-wait
@elseif ($shop_info['merchants_audit'] == 1)
spw-success
@elseif ($shop_info['merchants_audit'] == 2)
spw-fail
@endif
">
                        <i class="spw-icon"></i>
                        <div class="spw-info">
                            <h2>

@if($shop_info['merchants_audit'] == 0)

                                {{ $lang['under_review_one'] }}

@elseif ($shop_info['merchants_audit'] == 1)

                                {{ $lang['under_review_two'] }}

@elseif ($shop_info['merchants_audit'] == 2)

                                {{ $lang['under_review_three'] }}

@endif

                            </h2>
                            <div class="spw-txt">
                                <p>{{ $lang['merchants_step_complete_one'] }}</p>

@if($shop_info['merchants_audit'] == 1)

                                <span>{{ $lang['merchants_step_complete_two'] }}：<em>{{ $shop_info['hope_login_name'] }}</em></span>

@endif

                                <span>{{ $lang['audit_shop_one'] }}：<strong class="orange2">{{ $shop_info['rz_shop_name'] }}</strong></span>
                                <span>{{ $lang['audit_shop_two'] }}：<em>{{ $shop_info['shop_class_key_words'] }}</em></span>

@if($shop_info['merchants_audit'] == 2 && $shop_info['merchants_message'])

                                <span>{{ $lang['reply_comment'] }}：<strong class="red">{{ $shop_info['merchants_message'] }}</strong></span>

@endif

                            </div>

@if($shop_info['merchants_audit'] == 2 || $shop_info['merchants_audit'] == 0)


@if($shop_info['steps_audit'] == 0 )

                                <div class="spw-btn"><a href="merchants_steps.php?step=stepOne&pid_key=0" class="btn sc-redBg-btn">{{ $lang['reapply'] }}</a></div>

@endif


@elseif ($shop_info['merchants_audit'] == 1)

                            <div class="spw-btn"><a href="seller/index.php" target="_blank" class="btn sc-redBg-btn">{{ $lang['merchant_login'] }}</a></div>

@elseif ($shop_info['merchants_audit'] == 0)

                            <!--<div class="spw-btn"><a href="user.php" target="_blank" class="btn sc-redBg-btn">{{ $lang['user_center'] }}</a></div>-->

@endif

                            <div class="setted-footer"><a href="index.php" class="ftx-05">{{ $lang['back_home'] }}</a><a href="user.php" class="ftx-05">{{ $lang['user_center'] }}</a>
@if($shop_info['merchants_audit'] == 1)
<a href="user.php?act=merchants_upgrade"  class="ftx-05">{{ $lang['seller_Grade'] }}</a>
@endif
</div>
                        </div>
                    </div>
                </div>
            </div>

@endif

            </div>

@endif

        </div>
    </div>
</div>
@include('frontend::library/page_footer_flow')
</body>

<script type="text/javascript" src="{{ asset('js/calendar/calendar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.divbox.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/region.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.validation.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/lib_ecmobanFunc.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<script type="text/javascript">
var process_request = "{{ $lang['process_request'] }}";

@foreach($lang['passport_js'] as $key => $item)

var {{ $key }} = "{!! $item !!}";

@endforeach

var username_exist = "{{ $lang['username_exist'] }}";
var compare_no_goods = "{{ $lang['compare_no_goods'] }}";
var btn_buy = "{{ $lang['btn_buy'] }}";
var is_cancel = "{{ $lang['is_cancel'] }}";
var select_spe = "{{ $lang['select_spe'] }}";

/* 日期勾选永久 */
function get_shopTime_term(t){
	var parent = $(t).parent(".cart-checkbox");
	var input = parent.siblings("input[type='text']");

	if($(t).is(":checked")){
		parent.addClass("cart-checkbox-checked");
		input.val('');
		$(t).val(1);
		input.removeClass("jdate");
	}else{
		parent.removeClass("cart-checkbox-checked");
		$(t).val(0);
		input.addClass("jdate");
	}
}

$(function(){
	//默认加载主营类目
	var shop_categoryMain = $("#shop_categoryMain_id_val").val();
    if (shop_categoryMain) {
        selectChildCate(shop_categoryMain);
    }

	/* 添加详细类目 */
	$("#addCategoryBtn").on("click",function(){
		var content = $("#divSCA .mod").clone();
		$("#divSCA .mod").remove();

		pb({
			id:"dialogCate",
			title:"{{ $lang['Add_category'] }}",
			width:560,
			content:content,
			ok_title:"{{ $lang['assign'] }}",
			cl_title:"{{ $lang['is_cancel'] }}",
			onOk:function(){
				scanCodeDialog("#dialogCate");

				//类目提交
				selectChildCate_cheked();
			},
			onCancel:function(){
				scanCodeDialog("#dialogCate");
			},
			onClose:function(){
				scanCodeDialog("#dialogCate");
			}
		});
	});

	//类目关闭弹窗
	function scanCodeDialog(obj){
		var obj = $(obj).find(".mod");
		var cp = obj.clone();
		$("#divSCA").append(cp);
	}

	//类目复选框选择
	$(document).on("click","input[name='cateChild[]']",function(){
		if($(this).prop("checked") == true){
			$(this).parent().addClass("cart-checkbox-checked");
		}else{
			$(this).parent().removeClass("cart-checkbox-checked");
		}
	});

	//二级类目全选
	$(document).on("click","#getCateAll",function(){
		var t = $(this);

		if(t.prop("checked") == true){
			t.parent().addClass("cart-checkbox-checked");
			$("#steps_re_span").find("input[name='cateChild[]']").prop("checked",true);
			$("#steps_re_span").find(".cart-checkbox").addClass("cart-checkbox-checked");
		}else{
			t.parent().removeClass("cart-checkbox-checked");
			$("#steps_re_span").find("input[name='cateChild[]']").prop("checked",false);
			$("#steps_re_span").find(".cart-checkbox").removeClass("cart-checkbox-checked");
		}
	});

	//返回上一步
	$("#js-pre-step").click(function(){
		var pid_key = {{ $pid_key }} - 2;
		var step='{{ $step }}';

        if(step>-1){
			location.href="merchants_steps.php?step="+step+"&pid_key="+pid_key;
		}else{
			if(step == 'stepTwo' && pid_key == 1){
			    location.href="merchants_steps.php?step="+step+"&pid_key="+pid_key;
			}else if(step == 'stepThree' && pid_key == -1){
			    location.href="merchants_steps.php?step=stepTwo&pid_key=2";
			}else{
			    history.go(-1);
			}
		}
	});

	//入驻验证
	$("#nextStepBtn").click(function(){
		$("#stepForm").validate({
			ignore : ".ignore"
		});

        var is_ok = 0;
        @if ($choose_process == 1)
		$("form[name='stepForm'] :input").each(function (index, element) {

            var process = $(element).data('process');
            var intputVal = $(element).val();

		    if(process != 'undefined' && process == 1 && intputVal == ''){
                var fieldsName = $(element).data('fields_name');
                var id_name = $(element).attr('id');
                $("#error_" + id_name).html(fieldsName + "{{ $lang['not_null'] }}");
                is_ok += 1;
            }
        });
        @endif

        if(is_ok > 0){
            $("#stepForm").submit(function () {
                return false;
            });
        }

		$("input[name='ec_shoprz_type']").rules("add",{
			min : 1,
			messages : {
				min : json_languages.merchants_step_error_one
			}
		});

		$("input[name='ec_subShoprz_type']").rules("add",{
			min : 1,
			messages : {
				min : json_languages.merchants_step_error_three
			}
		});

		$("input[name='ec_shop_categoryMain']").rules("add",{
			min : 1,
			messages : {
				min : json_languages.merchants_step_error_four
			}
		});

		$("input[name='detailed_category']").rules("add",{
			min : 1,
			messages : {
				min : '{{ $lang['please_Add_category'] }}'
			}
		});

		$("input[name='title_brand_list']").rules("add",{
			min : 1,
			messages : {
				min : json_languages.merchants_step_error_two
			}
		});

		$("input[name='ec_bank_name_letter']").rules("add",{
			required : true,
			isEnglish : true,
			messages : {
				required : json_languages.merchants_step_error_En,
				isEnglish : json_languages.merchants_step_error_En_name
			}
		});

		$("input[name='ec_brandFirstChar']").rules("add",{
			required:true,
			messages : {
				required: json_languages.merchants_step_error_six,
			}
		});

		var brandLogo = $("input[name='ec_brandLogo']").siblings("input[name='textfile']").val()

		if(brandLogo == ''){
			$("input[name='ec_brandLogo']").rules("add",{
				required : 1,
				messages : {
					required :  json_languages.merchants_step_error_seven
				}
			});
		}

		$("input[name='ec_brandType']").rules("add",{
			min : 1,
			messages : {
				min :  json_languages.merchants_step_error_Eight
			}
		});

		$("input[name='ec_brand_operateType']").rules("add",{
			min : 1,
			messages : {
				min : json_languages.merchants_step_error_Nine
			}
		});

		$("input[name='ec_shoprz_brandName']").rules("add",{
			required:true,
			messages : {
				required : json_languages.merchants_step_error_brand
			}
		});

		$("input[name='ec_shop_class_keyWords']").rules("add",{
			required:true,
			messages : {
				required : json_languages.merchants_step_cata_key
			}
		});

		$("input[name='ec_rz_shopName']").rules("add",{
			required:true,
			messages : {
				required : json_languages.merchants_step_error_Eleven
			}
		});

		$("input[name='ec_hopeLoginName']").rules("add",{
			required:true,
			messages : {
				required : json_languages.merchants_step_error_Twelve
			}
		});
	});
});


/* 期望店铺类型选择 */
$.divselect("#ec_shoprz_type","#ec_shoprz_type_val",function(obj){
	var val = obj.data("value");

	if(val == 0){
		$("#subShoprz_Html").html(json_languages.merchants_step_error_one);
		$("#ec_subShoprz_type").hide();
		$("*[ectype='shopType']").hide();
	}else{
		if(val == 1){
			$("#ec_subShoprz_type").show();
			$("#ec_subShoprz_type_val").removeClass("ignore");
			$("*[ectype='shopType']").show();
		}else{
			$("#ec_subShoprz_type").hide();
			$("*[ectype='shopType']").hide();
			$("#ec_subShoprz_type_val").addClass("ignore");
		}

		$("*[ectype='shopSellerPrompt']").find(".item").eq(val-1).show().siblings().hide();

		$("#subShoprz_Html").html('');
	}
});

/* 期望旗舰店店铺选择 */
$.divselect("#ec_subShoprz_type","#ec_subShoprz_type_val",function(obj){
	var val = obj.data("value");

	if(val == 0){
		$("#subShoprz_Html").html(json_languages.merchants_step_error_three);
		$("*[ectype='shopType']").hide();
		$("*[ectype='shopType']").find(".item-item").hide();
	}else{
		$("*[ectype='shopType']").show();
		if(val != 1){
			$("*[ectype='shopType']").find(".item-item").eq(val-2).show().siblings().hide();
		}else{
			$("*[ectype='shopType']").find(".item-item").hide();
		}

		$("#subShoprz_Html").html('');
	}
});

/* 主营类目选择 */
$.divselect("#shop_categoryMain_id","#shop_categoryMain_id_val",function(obj){
	var val = obj.data("value");
	var name = obj.text();
	if(val != 0){
		$("#cate_Html").html('');
		selectChildCate(val);

		//给弹出窗口一级目录赋值
		$("#addCategoryMain_Id_val").val(val);
		$("#addCategoryMain_Id .cite").find("span").html(name);
	}else{
		$("#cate_Html").html(json_languages.merchants_step_error_four);
	}
});

$.divselect("#addCategoryMain_Id","#addCategoryMain_Id_val",function(obj){
	var val = obj.data("value");
	var name = obj.text();

	selectChildCate(val);

	//给主营类目赋值
	$("#shop_categoryMain_id_val").val(val);
	$("#shop_categoryMain_id .cite").find("span").html(name);
});

//选择一级类目查找二级类目
function selectChildCate(val,type,cateArr){
	if(typeof(type) == 'undefined'){
		type = 0;
	}

	if(typeof(cateArr) == 'undefined'){
		cateArr = '';
	}

	Ajax.call('merchants_steps.php?step=addChildCate', 'cat_id=' + val + '&type=' + type + '&cateArr=' + $.toJSON(cateArr),function(result){
		if(result.error == 1){
			pbDialog(result.message,"",0);
			location.href = 'user.php';
		}else{
			$('#steps_re_span').html(result.content);
			$('#getCateAll').attr('checked',false);

			if(result.type == 1){ //取消二级类目
				$('#detailCategoryTable').html(result.cate_checked); //删除二级类目列表
				$('#category_permanent').html(result.catePermanent); //以及类目证件列表
			}
		}
	}, 'POST', 'JSON');
}

//添加二级类目
function selectChildCate_cheked(){
	var cateArr = new Object();
	var child = new Array();
	var cateChild = $("#dialogCate").find("input[name='cateChild[]']");
	for(i=0; i<cateChild.length; i++){
		if(cateChild[i].checked == true){
			child[i] = cateChild[i].value;
		}else{
			child[i] = '';
		}
	}

	//记录详细类目数量 验证时用到
	$("input[name='detailed_category']").val(cateChild.length);

	cateArr.cat_id = child;

	Ajax.call('merchants_steps.php?step=addChildCate_checked', 'cat_id=' + $.toJSON(cateArr),function(result){
		if(result.error == 1){
			pbDialog(result.message,"",0);
			location.href = 'user.php';
		}else{
			$('#detailCategoryTable').html(result.content); //二级类目别表
			$('#category_permanent').html(result.catePermanent); //以及类目证件列表
		}
	}, 'POST', 'JSON');
}

//删除二级类目
function deleteChildCate(ct_id){
	Ajax.call('merchants_steps.php?step=deleteChildCate_checked', 'ct_id=' + ct_id,function(result){
		if(result.error == 1){
			pbDialog(result.message,"",0);
			location.href = 'user.php';
		}else{
			$('#detailCategoryTable').html(result.content); //删除类目
			$('#category_permanent').html(result.catePermanent); //以及类目证件列表
		}
	}, 'POST', 'JSON');
}


/* 添加品牌资质 */
function addBrandTable(obj)
{
	var add_num = 1000;
	var num = document.getElementById('numAdd').value;
	if(num < add_num){
		var src  = obj.parentNode.parentNode;
		var idx  = rowindex(src);
		var tbl  = document.getElementById('brand-table');
		var row  = tbl.insertRow(idx + 1);
		//var cell = row.insertCell(-1);
		row.innerHTML = src.innerHTML.replace(/(.*)(addBrandTable)(.*)(\[)(\+)/i, "$1removeBrandTable$3$4-").replace('"expiredDate_permanent"','\"expiredDate_permanent'+num+'\"');
		row.innerHTML = row.innerHTML.replace('"expiredDate_permanent"','\"expiredDate_permanent'+num+'\"');

		num++;
		document.getElementById('numAdd').value = num;
	}else{
		alert(add_limit + add_num +add_ci);
	}

	for(i=0;i<num;i++){
		var expiredDate = document.getElementsByName("ec_expiredDateInput[]");
		expiredDate[i].id = 'expiredDateInput_' + i;
	}
}

/* 删除品牌资质 */
function removeBrandTable(obj,b_fid)
{
	if(b_fid > 0){
		if (confirm(json_languages.merchants_step_remove)){
		   location.href = 'merchants_steps.php?step={{ $step }}&pid_key={{ $b_pidKey }}&ec_shop_bid={{ $ec_shop_bid }}&del_bFid=' + b_fid + '&brandView=brandView';
	   }
	}else{
		var row = rowindex(obj.parentNode.parentNode);
		var tbl = document.getElementById('brand-table');

		tbl.deleteRow(row);

		var num = document.getElementById('numAdd').value;
		num--;
		document.getElementById('numAdd').value = num;

		for(i=0;i<num;i++){
			var radioCheckbox_val = document.getElementsByName("radioCheckbox_val[]");
			radioCheckbox_val[i].value = i;
		}
	}
}

/* 删除品牌 */
function get_deleteBrand(bid){
	if (confirm(json_languages.merchants_step_remove)){
		location.href = 'merchants_steps.php?step={{ $step }}&pid_key={{ $b_pidKey }}&ec_shop_bid=' + bid + '&del=deleteBrand';
	}
}
</script>
</html>
