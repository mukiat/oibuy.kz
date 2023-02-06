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

<body>
    @include('frontend::library/page_header_common')
    <div class="content">
		{{-- DSC 提醒您：动态载入brand_index_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $brand_index_ad]) !!}
        <div class="brand-main" ectype='brandMain'>
            <div class="w w1200">
                <div class="brand-title"><span>{{ $lang['brand_special_area'] }}</span></div>
                <div class="brand-cate" ectype="brandCate">
                    <a href="javascript:;" class="curr" data-catid="0" ectype="cateItem">{{ $lang['brand_all'] }}</a><i class="point">·</i>

@foreach($top_cat_list as $key => $cat)

					<a href="javascript:;" data-catid="{{ $cat['cat_id'] }}" ectype="cateItem">{{ $cat['cat_name'] }}</a>
@if(!$loop->last)
<i class="point">·</i>
@endif


@endforeach

                </div>
                <div class="brand-list" ectype="brandList">
                    <ul ectype="items">
                        @include('frontend::/library/brand_list')
                    </ul>
                </div>
            </div>
        </div>
		<input type="hidden" name="user_id" value="{{ $user_id ?? 0 }}">
    </div>
    <div class="rTop returnHide" ectype="rTop"><img src="{{ skin('images/returnTop.png') }}"></div>
	{{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}
    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/asyLoadfloor.js') }}"></script>
	<script type="text/javascript">
		$.scrollTop("*[ectype='brandList']","*[ectype='rTop']");
		$.scrollLoad("*[ectype='brandMain']", "*[ectype='brandList'] *[ectype='items']", "li", {url:'brand.php', data:'act=load_more_brand'})
	</script>
</body>
</html>
