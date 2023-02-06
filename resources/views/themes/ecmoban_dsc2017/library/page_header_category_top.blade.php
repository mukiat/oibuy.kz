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
<div class="header">
    <div class="w w1200">
        <div class="logo">
            <div class="logoImg"><a href="{{ $url_index }}"><img src="
@if($shop_logo)
{{ $shop_logo }}
@else
{{ skin('/images/logo.gif') }}
@endif
" /></a></div>

        </div>
        <div class="dsc-search">
            <div class="form">
                <form id="searchForm" name="searchForm" method="post" action="search.php" onSubmit="return checkSearchForm(this)" class="search-form">
                    <input autocomplete="off" onKeyUp="lookup(this.value);" name="keywords" type="text" id="keyword" value="
@if($search_keywords)
{{ $search_keywords }}
@else
{!! insert_rand_keyword() !!}
@endif
" class="search-text"/>

                    <input type="hidden" value="0" name="search_goods_id" />

                    <input type="hidden" name="store_search_cmt" value="{{ $search_type ?? 0 }}">
                    <button type="submit" class="button button-goods" onclick="checkstore_search_cmt(0)">{{ $lang['serch_goods'] }}</button>
                    <button type="submit" class="button button-store" onclick="checkstore_search_cmt(1)">{{ $lang['serch_shop'] }}</button>
                @csrf </form>

@if($searchkeywords)

                <ul class="keyword">

@foreach($searchkeywords as $val)

                <li><a href="search.php?keywords={{ $val }}" target="_blank">{{ $val }}</a></li>

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
        <div class="shopCart" data-ectype="dorpdown" id="ECS_CARTINFO" data-carteveval="0">
            {{-- DSC 提醒您：根据用户id来调用cart_info.lbi显示不同的界面  --}}
{!! insert_cart_info() !!}
        </div>
    </div>
</div>

@if($cate_info['top_style_tpl'] == 1)

<div class="nav dsc-zoom">
	<div class="w w1200">
		<div class="categorys">
			<div class="categorys-type" id="fuzhuang"><a href="javascript:;">{{ $cate_info['cat_name'] }}</a></div>
			<div class="categorys-tab-content">
				<div class="categorys-items" id="categorys-fuzhuang" ectype="items">

@foreach($categories_child as $cat)


@if($loop->index < 5)

					<div class="categorys-item" ectype="item" data-catid="{{ $cat['cat_id'] }}" data-eveval="0">
						<div class="item-content">
							<i class="iconfont icon-right"></i>
							<div class="categorys-title">
								<strong><a href="{{ $cat['url'] }}" target="_blank">{{ $cat['cat_name'] }}</a></strong>
								<span>

@foreach($cat['cat_list'] as $child)


@if($loop->index<6)

                                    <a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a>

@endif


@endforeach

								</span>
							</div>
						</div>
						<div class="categorys-items-layer" ectype="cateLayer">
							<div class="catetop-layer-con clearfix">
								<h3><a href="javascript:;">{{ $cat['cat_name'] }}</a></h3>
								<ul class="cate-list">

@foreach($cat['cat_list'] as $child)

									<li><a href="{{ $child['url'] }}">{{ $child['cat_name'] }}</a></li>

@endforeach

								</ul>
								<ul class="cate-logo" ectype="subitems_{{ $cat['cat_id'] }}">
								</ul>
							</div>
						</div>
					</div>

@endif


@endforeach

				</div>
			</div>
		</div>
		<div class="nav-main">
			<ul class="navitems">
                <li><a href="index.php"
@if($navigator_list['config']['index'] == 1)
class="curr"
@endif
>{{ $lang['home'] }}</a></li>

@foreach($navigator_list['middle'] as $nav)

                <li><a href="{{ $nav['url'] }}"
@if($nav['active'] == 1)
class="curr"
@endif

@if($nav['opennew'])
target="_blank"
@endif
>{{ $nav['name'] }}</a></li>

@endforeach

			</ul>
		</div>
	</div>
</div>

@elseif ($cate_info['top_style_tpl'] == 2)

<div class="nav dsc-zoom">
    <div class="w w1200">
        <div class="categorys">
            <div class="categorys-type" id="jiadian"><a href="javascript:;">{{ $cate_info['cat_name'] }}</a></div>
            <div class="categorys-tab-content">
                <div class="categorys-items" id="categorys-jiadian" ectype="items">

@foreach($categories_child as $cat)


@if($loop->index < 5)

                    <div class="categorys-item" ectype="item" data-catid="{{ $cat['cat_id'] }}">
                        <div class="item-content">
                            <div class="categorys-title">
                                <strong><a href="{{ $cat['url'] }}" target="_blank">{{ $cat['cat_name'] }}</a></strong>
                                <span>

@foreach($cat['cat_list'] as $child)


@if($loop->index<3)

                                    <a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a>

@endif


@endforeach

                                </span>
                            </div>
                        </div>
                        <div class="categorys-items-layer">
                            <div class="catetop-layer-con clearfix">
                                <h3>{{ $cat['cat_name'] }}</h3>
                                <ul class="cate-list">

@foreach($cat['cat_list'] as $child)

                                    <li><a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a></li>

@endforeach

                                </ul>
                            </div>
                            {{-- DSC 提醒您：动态载入cate_layer_right.lbi，显示全部分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $cat['cate_layer_elec_row'], 'id' => $cat['cat_id']]) !!}
                        </div>
                    </div>

@endif


@endforeach

                </div>
            </div>
        </div>
        <div class="nav-main" id="nav">
            <ul class="navitems">
                <li><a href="index.php"
@if($navigator_list['config']['index'] == 1)
 class="curr"
@endif
>{{ $lang['home'] }}</a></li>

@foreach($navigator_list['middle'] as $nav)

                <li><a href="{{ $nav['url'] }}"
@if($nav['active'] == 1)
 class="curr"
@endif

@if($nav['opennew'])
target="_blank"
@endif
>{{ $nav['name'] }}</a></li>

@endforeach

            </ul>
        </div>
    </div>
</div>

@elseif ($cate_info['top_style_tpl'] == 3)

<div class="nav dsc-zoom">
	<div class="w w1200">
		<div class="categorys">
			<div class="categorys-type" id="shipin"><a href="javascript:;">{{ $cate_info['cat_name'] }}</a></div>
			<div class="categorys-tab-content">
				<div class="categorys-items" id="categorys-shipin" ectype="items">

@foreach($categories_child as $cat)


@if($loop->index < 7)

					<div class="categorys-item" ectype="item" data-catid="{{ $cat['cat_id'] }}">
						<div class="item-content">

@if($cat['style_icon'] == 'other')


@if($cat['cat_icon'])
<div class="icon-other"><img src="{{ $cat['cat_icon'] }}" alt="{{ $lang['category_icon'] }}"></div>
@endif


@else

                            <i class="iconfont icon-{{ $cat['style_icon'] }}"></i>

@endif

							<div class="categorys-title">
								<strong><a href="{{ $cat['url'] }}" target="_blank">{{ $cat['cat_name'] }}</a></strong>
								<span>

@foreach($cat['cat_list'] as $child)


@if($loop->index<3)

                                    <a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a>

@endif


@endforeach

								</span>
							</div>
						</div>
						<div class="categorys-items-layer">
							<div class="catetop-layer-con clearfix">
								<ul class="cate-list">

@foreach($cat['cat_list'] as $child)

                                    <li><a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a></li>

@endforeach

								</ul>
								{{-- DSC 提醒您：动态载入cate_layer_right.lbi，显示全部分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $cat['cate_layer_elec_row'], 'id' => $cat['cat_id']]) !!}
							</div>
						</div>
					</div>

@endif


@endforeach

				</div>
			</div>
		</div>
		<div class="nav-main" id="nav">
			<ul class="navitems">
                <li><a href="index.php"
@if($navigator_list['config']['index'] == 1)
class="curr"
@endif
>{{ $lang['home'] }}</a></li>

@foreach($navigator_list['middle'] as $nav)

                <li><a href="{{ $nav['url'] }}"
@if($nav['active'] == 1)
class="curr"
@endif

@if($nav['opennew'])
target="_blank"
@endif
>{{ $nav['name'] }}</a></li>

@endforeach

			</ul>
		</div>
	</div>
</div>

@else

<div class="nav dsc-zoom">
	<div class="w w1200">
		<div class="categorys">
			<div class="categorys-type" id="default"><a href="javascript:;" target="_blank"><i class="iconfont icon-liebiao"></i>{{ $cate_info['cat_name'] }}</a></div>
			<div class="categorys-tab-content cat-tab-main">
				<div class="categorys-items" ectype="items">

@foreach($categories_child as $cat)


@if($loop->index < 10)

                    <div class="categorys-item" ectype="item" data-defa="1" data-catid="{{ $cat['cat_id'] }}" data-eveval="0">
                        <div class="item-content">
                            <div class="categorys-title">
                                <strong><a href="{{ $cat['url'] }}" target="_blank">{{ $cat['cat_name'] }}</a></strong>
                            </div>
                        </div>
                        <div class="categorys-items-layer" ectype="cateLayer">
                            <div class="catetop-layer-con clearfix">
                                <div class="tit">{{ $cat['cat_name'] }}</div>
                                <ul class="cate-list">

@foreach($cat['cat_list'] as $child)

                                    <li><a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a></li>

@endforeach

                                </ul>
                                <div class="brand-list" ectype="subitems_{{ $cat['cat_id'] }}">
                                </div>
                            </div>
                        </div>
                    </div>

@endif


@endforeach

                </div>
			</div>
		</div>
		<div class="nav-main">
			<ul class="navitems">
                <li><a href="index.php"
@if($navigator_list['config']['index'] == 1)
 class="curr"
@endif
>{{ $lang['home'] }}</a></li>

@foreach($navigator_list['middle'] as $nav)

                <li><a href="{{ $nav['url'] }}"
@if($nav['active'] == 1)
 class="curr"
@endif

@if($nav['opennew'])
 target="_blank"
@endif
>{{ $nav['name'] }}</a></li>

@endforeach

			</ul>
		</div>
	</div>
</div>

@endif

