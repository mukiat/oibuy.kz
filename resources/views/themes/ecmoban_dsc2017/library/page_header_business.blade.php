
@if($topBanner)

    {!! $topBanner !!}

@else

{{-- DSC 提醒您：动态载入position_get_adv.lbi，显示首页分类小广告 --}}
{!! insert_get_adv(['logo_name' => $top_banner]) !!}

@endif

<div class="site-nav" id="site-nav">
    <div class="w w1200">
        <div class="fl">
        {{-- DSC 提醒您：根据用户id来调用header_region_style.lbi显示不同的界面  --}}
{!! insert_header_region() !!}
            <div class="txt-info" id="ECS_MEMBERZONE">
		{{-- DSC 提醒您：根据用户id来调用member_info.lbi显示不同的界面  --}}
{!! insert_member_info() !!}
            </div>
        </div>
        <ul class="quick-menu fr">

@if($navigator_list['top'])


@foreach($navigator_list['top'] as $key => $nav)


@if($loop->index < 4)

            <li>
                <div class="dt"><a href="{{ $nav['url'] }}"
@if($nav['opennew'])
target="_blank"
@endif
>{{ $nav['name'] }}</a></div>
            </li>
            <li class="spacer"></li>

@endif


@endforeach


@endif


@if($navigator_list['top'])

            <li class="li_dorpdown" data-ectype="dorpdown">
                <div class="dt dsc-cm">{{ $lang['Site_navigation'] }}<i class="iconfont icon-down"></i></div>
                <div class="dd dorpdown-layer">
                    <dl class="fore1">
                        <dt>{{ $lang['characteristic_theme'] }}</dt>
                        <dd>

@foreach($top_cat_list as $key => $topc_cats)


@if($loop->index < 3)

                                    <div class="item"><a href="{{ $topc_cats['url'] }}" target="_blank">{{ $topc_cats['cat_alias_name'] }}</a></div>

@endif


@endforeach

                        </dd>
                    </dl>
                    <dl class="fore2">
                        <dt>{{ $lang['modules_txt_promo'] }}</dt>
                        <dd>

@foreach($navigator_list['top'] as $key => $nav)


@if($loop->index >= 4)

                                    <div class="item"><a href="{{ $nav['url'] }}"
@if($nav['opennew'])
 target="_blank"
@endif
>{{ $nav['name'] }}</a></div>

@endif


@endforeach

                        </dd>
                    </dl>
                </div>
            </li>

@endif

        </ul>
    </div>
</div>
<div class="header header-b2b">
    <div class="w w1200">
        <div class="logo">
            <div class="logoImg">
                <a href="wholesale.php">

@if($suppliers_pc_log)

                        <img src="{{ $suppliers_pc_log }}"/>

@else

                        <img src="{{ skin('images/business_logo.gif') }}" />

@endif

                </a>
            </div>
        </div>
        <div class="dsc-search">
            <div class="form">
                <form id="searchForm" name="searchForm" method="post" action="wholesale_search.php" onSubmit="return checkSearchForm(this)" class="search-form">
                    <input autocomplete="off"  name="keywords" type="text" id="keyword" value="
@if($wholesale_search_keywords)
{{ $wholesale_search_keywords }}
@else
{!! insert_wholesale_rand_keyword() !!}
@endif
" class="search-text"/>
                    <button type="submit" class="button button-goods" onclick="checkstore_search_cmt(0)">{{ $lang['serch_goods'] }}</button>
					<a href="javascript:;" ectype="wantToBuy" class="wantToBuy"><i class="iconfont icon-book"></i>{{ $lang['serch_want_buy'] }}</a>
					<input type="hidden" name="user_id" value="{{ $user_id ?? 0 }}">
                @csrf </form>

@if($wholesale_keywords)

                <ul class="keyword">

@foreach($wholesale_keywords as $val)

                <li><a href="wholesale_search.php?keywords={{ $val }}" target="_blank">{{ $val }}</a></li>

@endforeach

                </ul>

@endif


                <div class="suggestions_box" id="suggestions" style="display:none;">
                    <div class="suggestions_list" id="auto_suggestions_list">
                        &nbsp;
                    </div>
                </div>

            </div>
        </div>
        <div class="shopCart b2b-shopCart" data-ectype="dorpdown" id="ECS_WHOLESALE_CARTINFO" data-carteveval="0">
        {{-- DSC 提醒您：根据用户id来调用wholesale_cart_info.lbi显示不同的界面  --}}
{!! insert_wholesale_cart_info() !!}
        </div>
    </div>
</div>
<div class="nav nav-b2b
@if($filename != 'wholesale')
 dsc-zoom
@endif
">
    <div class="w w1200">
        <div class="b2b-categorys">
        	<div class="b2b-categorys-all"><a href="wholesale_cat.php" target="_blank">{{ $lang['all_b2b_goods'] }}</a></div>
            <div class="b2b-categorys-content
@if($filename != 'wholesale')
 hide
@endif
">
            	<ul>

@foreach($cat_list as $cat)


@if($loop->index < 10)

                	<li>
                    	<div class="b2b-cate-item">

@if($cat['style_icon'] == 'other')


@if($cat['cat_icon'])
<div class="icon-other"><img src="{{ $cat['cat_icon'] }}" alt="{{ $lang['category_icon'] }}"></div>
@endif


@else

                            <i class="iconfont icon-{{ $cat['style_icon'] }}"></i>

@endif

                            <a href="{{ $cat['url'] }}" target="_blank">{{ $cat['name'] }}</a>
                        </div>

@if($cat['cat_list'])

                    	<div class="b2b-cate-layer" ectype="cateLayer">
                        	<div class="b2b-cate-layer-con clearfix">
                                <div class="cate_detail">

@foreach($cat['cat_list'] as $child)

                                	<dl>
                                    	<dt><a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a></dt>

@if($child['cat_list'])

                                        <dd>

@foreach($child['cat_list'] as $childer)

                                        	<a href="{{ $childer['url'] }}" target="_blank">{{ $childer['cat_name'] }}</a>

@endforeach

                                        </dd>

@endif

                                    </dl>

@endforeach

                                </div>
                            </div>
                        </div>

@endif

                    </li>

@endif


@endforeach

                </ul>
            </div>
        </div>
        <div class="nav-main">
        	<ul class="navitems">
            	<li><a href="wholesale.php"
@if($index == 'index')
class="curr"
@endif
>{{ $lang['b2b_home'] }}</a></li>
                <li><a href="wholesale_purchase.php?act=list"
@if($buy == 'list')
class="curr"
@endif
>{{ $lang['want_buy_info'] }}</a></li>

@foreach($get_wholsale_navigator as $get_wholsale_navigator)

				<li><a href="{{ $get_wholsale_navigator['url'] }}"
@if($get_wholsale_navigator['cat_id'] == $get_wholsale_navigator['active'])
class="curr"
@endif
>{{ $get_wholsale_navigator['cat_name'] }}</a></li>

@endforeach

            </ul>
        </div>
    </div>
</div>

@if($filename != 'wholesale')

<script type="text/javascript">
$(document).on("mouseover", ".b2b-categorys", function(){
	$(".b2b-categorys-content").removeClass('hide');
})
$(document).on("mouseout", ".b2b-categorys", function(){
	$(".b2b-categorys-content").addClass('hide');
})
</script>

@endif

<script type="text/javascript">
//发布求购单
$(document).on('click', "[ectype='wantToBuy']", function(){
  var user_id = $("input[name='user_id']").val();
  var url = 'wholesale_purchase.php?act=release';
  if(user_id > 0){
	location.href = url;
  }else{
	$.notLogin("get_ajax_content.php?act=get_login_dialog", url);
  }
})
</script>
