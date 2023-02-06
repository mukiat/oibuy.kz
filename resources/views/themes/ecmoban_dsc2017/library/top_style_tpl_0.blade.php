<div class="catetop-tree-blank"></div>
<div class="banner catetop-banner">
	{{-- DSC 提醒您：动态载入category_top_banner.lbi，显示顶级分类页（默认）轮播图 --}}
{!! insert_get_adv_child(['ad_arr' => $category_top_banner, 'id' => $cate_info['cat_id']]) !!}
</div>
<div class="catetop-main w w1200">
	
	<div class="toprank" id="toprank">
		<div class="hd">
			<ul>
				<li>{{ $lang['hot_sale_list'] }}</li>
				<li>{{ $lang['promotion_list'] }}</li>
			</ul>
		</div>
		<div class="bd">
			<ul>
				
@foreach($cate_top_hot_goods as $goods)

				
@if($loop->iteration <= 5)

				<li>
					<div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
					<div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
					<div class="p-price">
						
@if($goods['promote_price'] != '')

							 {{ $goods['promote_price'] }}
						
@else

							 {{ $goods['shop_price'] }}
						
@endif
					
					</div>
				</li>
				
@endif

				
@endforeach

			</ul>
			<ul>
				
@foreach($cate_top_promote_goods as $goods)

				
@if($loop->iteration <= 5)

				<li>
					<div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
					<div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
					<div class="p-price">
						
@if($goods['promote_price'] != '')

							 {{ $goods['promote_price'] }}
						
@else

							 {{ $goods['shop_price'] }}
						
@endif
					
					</div>
				</li>
				
@endif

				
@endforeach

			</ul>
		</div>
	</div>
	
	<div class="catetop-brand" id="catetop-brand">
		<div class="hd"><h2>{{ $lang['brand_flagship'] }}</h2></div>
		<div class="bd">
			<ul class="clearfix brand-recommend">
				
@foreach($cat_store as $store)

				<li 
@if($loop->iteration > 3)
class="reverse"
@endif
>
					<div class="cover"><a href="{{ $store['shop_url'] }}"><img src="{{ $store['street_thumb'] }}" alt=""></a></div>
					<div class="info">
						<div class="info-logo"><a href="{{ $store['shop_url'] }}"><img src="{{ $store['brand_thumb'] }}" alt=""></a></div>
						<div class="info-name"><a href="{{ $store['shop_url'] }}">{{ $store['shop_title'] }}</a></div>
						<div class="info-intro">{{ $store['street_desc'] }}</div>
					</div>
				</li>				
				
@endforeach

			{{-- DSC 提醒您：动态载入category_top_default_brand.lbi，显示顶级分类页（默认）品牌旗舰 --}}
{!! insert_get_adv_child(['ad_arr' => $category_top_default_brand, 'id' => $cat_id]) !!}				
			</ul>
			<div class="brand-slide">
				<a href="javascript:;" class="prev iconfont icon-left"></a>
				<a href="javascript:;" class="next iconfont icon-right"></a>
				<div class="bs-bd">
					<ul>
						
@foreach($cat_brand['brands'] as $kid => $brand)

						
@if($kid<100)

						<li><a href="brand.php?id={{ $brand['brand_id'] }}&cat={{ $cate_info['cat_id'] }}"><img src="{{ $brand['brand_logo'] }}" alt="{{ $brand['brand_name'] }}"></a></li>
						
@endif

						
@endforeach
						
					</ul>
				</div>
			</div>
		</div>
	</div>
	
	<div class="catetop-floor-wp">
		<div class="catetop-floor" id="recToday">
			<div class="f-banner">
				{{-- DSC 提醒您：动态载入category_top_default_head.lbi，显示顶级分类页（默认）今日推荐头部广告 --}}
{!! insert_get_adv_child(['ad_arr' => $category_top_default_best_head]) !!}
			</div>
			<div class="f-hd">
				<h2>{{ $lang['today_recommends'] }}</h2>
			</div>
			<div class="f-bd">
				<ul class="clearfix">
					<li class="first">
						{{-- DSC 提醒您：动态载入category_top_default_left.lbi，显示顶级分类页（默认）今日推荐左侧广告 --}}
{!! insert_get_adv_child(['ad_arr' => $category_top_default_best_left]) !!}
					</li>
					
@foreach($cate_top_best_goods as $goods)

					
@if($loop->iteration <= 10)

					<li class="goods-item">
						<div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
						<div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
						<div class="p-price">
							
@if($goods['promote_price'] != '')

								 {{ $goods['promote_price'] }}
							
@else

								 {{ $goods['shop_price'] }}
							
@endif
						
						</div>
					</li>
					
@endif

					
@endforeach

				</ul>
			</div>
		</div>
		<div class="catetop-floor" id="newPic">
			<div class="f-banner">
				{{-- DSC 提醒您：动态载入category_top_default_head.lbi，显示顶级分类页（默认）新品到货头部广告 --}}
{!! insert_get_adv_child(['ad_arr' => $category_top_default_new_head]) !!}
			</div>
			<div class="f-hd">
				<h2>{{ $lang['new_product'] }}</h2>
			</div>
			<div class="f-bd">
				<ul class="clearfix">
					<li class="first">
						{{-- DSC 提醒您：动态载入category_top_default_left.lbi，显示顶级分类页（默认）新品到货左侧广告 --}}
{!! insert_get_adv_child(['ad_arr' => $category_top_default_new_left]) !!}
					</li>
					
@foreach($cate_top_new_goods as $goods)

					
@if($loop->iteration <= 10)

					<li class="goods-item">
						<div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
						<div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
						<div class="p-price">
							
@if($goods['promote_price'] != '')

								 {{ $goods['promote_price'] }}
							
@else

								 {{ $goods['shop_price'] }}
							
@endif
						
						</div>
					</li>
					
@endif

					
@endforeach

				</ul>
			</div>
		</div>
	</div>
	
	{{-- DSC 提醒您：动态载入cate_top_history_goods.lbi，显示浏览记录 --}}
{!! insert_history_goods_pro() !!}

	
	<div class="catetop-lift lift-hide" ectype="lift">
    	<div class="lift-list" ectype="liftList">
            <div class="catetop-lift-item lift-item-current" ectype="liftItem" data-target="#toprank"><span>{{ $lang['toprank'] }}</span></div>
            
@if($cat_store)
<div class="catetop-lift-item" ectype="liftItem" data-target="#catetop-brand"><span>{{ $lang['brand_flagship'] }}</span></div>
@endif

            <div class="catetop-lift-item" ectype="liftItem" data-target="#recToday"><span>{{ $lang['today_recommends'] }}</span></div>
            <div class="catetop-lift-item" ectype="liftItem" data-target="#newPic"><span>{{ $lang['new_product'] }}</span></div>
            <div class="catetop-lift-item lift-history" ectype="liftItem" data-target="#atwillgo"><span>{{ $lang['Browsing_record'] }}</span></div>
            <div class="catetop-lift-item lift-item-top" ectype="liftItem"><span>TOP</span></div>
        </div>
	</div>
    <input name="tpl" value="{{ $cate_info['top_style_tpl'] }}" type="hidden">
</div>