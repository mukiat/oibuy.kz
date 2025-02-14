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
</head>
<body class="bonusBody">
    @include('frontend::library/page_header_common')
    <div id="content" class="bonus_content">
        <div class="w w1200">
        	<div class="bonus_warp">
            	<div class="bonus_icon"></div>
                <div class="bonus_info">
                	<div class="bonus_info_title">
                    	<h1>{{ $bonus_info['type_name'] }}</h1>
                        <span>{{ $lang['face_value'] }}：{{ $bonus_info['type_money_formatted'] }}</span>
                    </div>
                	<div class="bonus_info_con">
                    	<p>{{ $lang['min_order_money'] }}：{{ $bonus_info['min_goods_amount_formatted'] }}</p>

@if($bonus_info['valid_period'] > 0)

                            <p>{{ $lang['valid_period'] }}：{{ $lang['receive'] }}{{ $bonus_info['valid_period'] }}{{ $lang['valid_period_lost'] }}</p>

@else

                            <p>{{ $lang['use_start_time'] }}：{{ $bonus_info['use_start_date'] }}</p>
                            <p>{{ $lang['use_end_time'] }}：{{ $bonus_info['use_end_date'] }}</p>

@endif


@if($bonus_info['usebonus_type'])

                        <p>{{ $lang['general_audience'] }}</p>

@else

                        <p>{{ $lang['only_limit'] }}{{ $bonus_info['shop_name'] }}{{ $lang['use'] }}</p>

@endif

                    </div>
                    <div class="bonus_info_btn">

@if($exist)

                        <input type="button" value="{{ $lang['already_received'] }}" class="sc-btn sc-btn-disabled btn30 w90 mt10" ectype="get_bonus">

@elseif (!$left)

                        <input type="button" value="{{ $lang['have_finished'] }}" class="sc-btn sc-btn-disabled btn30 w90 mt10" ectype="get_bonus">

@elseif ($receive == 0)

                        <input type="button" value="{{ $lang['overdue_time'] }}" class="sc-btn sc-btn-disabled btn30 w90 mt10">

@else

                        <input type="button" value="{{ $lang['receive'] }}" class="sc-btn sc-redBg-btn btn30 w90 mt10" ectype="get_bonus">

@endif

                        <input type="hidden" name="id" value="{{ request()->get('id') }}"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('frontend::library/page_footer')
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>


@if($receive == 1)

    <script type="text/javascript">
    $(document).on('click', "[ectype='get_bonus']", function(){
        var type_id = $("input[name='id']").val();
        if(user_id > 0){
            $.jqueryAjax('bonus.php', 'act=get_bonus&type_id='+type_id, function(data){
                if(data.error == 0){
                    $("[ectype='get_bonus']").val('{{ $lang['already_received'] }}');
					$("[ectype='get_bonus']").addClass("sc-btn-disabled").removeClass("sc-redBg-btn");
                }
                pbDialog(data.message,"",0,"","",120);
            });
        }else{
            $.notLogin("get_ajax_content.php?act=get_login_dialog",'bonus.php?act=bonus_info&id='+type_id);
        }
    })

@endif

</script>
</body>
</html>
