<div class="banner catetop-banner">
	<div class="banner-ad">
		<div class="w w1200">
			<ul class="list">
				{{-- DSC 提醒您：动态载入cate_layer_right.lbi，轮播右侧广告 --}}
{!! insert_get_adv_child(['ad_arr' => $top_style_right_banne, 'id' => $cate_info['cat_id']]) !!}
			</ul>
		</div>
	</div>
	<div class="bd">
		{{-- DSC 提醒您：动态载入cat_top_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $top_style_food_banner, 'id' => $cate_info['cat_id']]) !!}
	</div>
    <div class="food-hd"><ul></ul></div>
</div>

<div class="catetop-main w w1200" ectype="catetopWarp">
	
	<div class="bestad" id="bestad">
		<div class="hd"><h2>{{ $lang['best_goods'] }}</h2></div>
		<div class="bd clearfix">
        	{{-- DSC 提醒您：动态载入top_style_food_hot.lbi，显示顶级分类页热门广告 --}}
{!! insert_get_adv_child(['ad_arr' => $top_style_food_hot, 'id' => $cate_info['cat_id']]) !!}
		</div>
	</div>
	
	
	<div class="catetop-floor-wp" ectype="goods_cat_level"></div>
    <div class="floor-loading" ectype="floor-loading"><div class="floor-loading-warp"><img src="{{ skin('images/load/loading.gif') }}"></div></div>

	
	<div class="atwillgo" id="atwillgo">
		<div class="awg-hd">
			<h2>{{ $lang['purchase_hand'] }}</h2>
		</div>
		<div class="awg-bd">
			<div class="atwillgo-slide">
				<a href="javascript:;" class="prev"><i class="iconfont icon-left"></i></a>
				<a href="javascript:;" class="next"><i class="iconfont icon-right"></i></a>
				<div class="hd">
					<ul></ul>
				</div>
				<div class="bd">
					<ul>
                        
@foreach($havealook as $look)

                        <li>
                            <div class="p-img"><a href="{{ $look['url'] }}" target="_blank"><img src="{{ $look['thumb'] }}" alt=""></a></div>
                            <div class="p-price">
                                
@if($look['promote_price'] != '')

                                {{ $look['promote_price'] }}
                                
@else

                                {{ $look['shop_price'] }}
                                
@endif

                            </div>
                            <div class="p-name"><a href="{{ $look['url'] }}" target="_blank" title="{{ $look['name'] }}">{{ $look['name'] }}</a></div>
                            <div class="p-btn"><a href="{{ $look['url'] }}" target="_blank">{{ $lang['View_details'] }}</a></div>
                        </li>
                        
@endforeach

                    </ul>
				</div>
			</div>
		</div>
	</div>
	
    <div class="catetop-lift lift-hide" ectype="lift">
    	<div class="lift-list" ectype="liftList">
        	<div class="catetop-lift-item lift-item-current" ectype="liftItem" data-target="#bestad"><span>{{ $lang['best_goods'] }}</span></div>
        	
@foreach($categories_child as $cat)

            <div class="catetop-lift-item lift-floor-item" ectype="liftItem"><span>{{ $cat['cat_name'] }}</span></div>
            
@endforeach

        	<div class="catetop-lift-item lift-item-top" ectype="liftItem"><span><i class="iconfont icon-up"></i></span></div>
        </div>
    </div>
    <input name="region_id" value="{{ $region_id }}" type="hidden">
    <input name="area_id" value="{{ $area_id }}" type="hidden">
    <input name="area_city" value="{{ $area_city }}" type="hidden">
    <input name="cat_id" value="{{ $cate_info['cat_id'] }}" type="hidden">
    <input name="tpl" value="{{ $cate_info['top_style_tpl'] }}" type="hidden">
    <script type="text/javascript">
		//楼层以后加载后使用js
		function loadCategoryTop(key){
			var Floor = $("#floor_"+key);
			var length = Floor.find(".l-bd li").length;
			Floor.slide({titCell:".fgoods-hd ul li",mainCell:".bd-right"});
			if(length>1){
				Floor.slide(".catetop-floor .l-slide").slide({mainCell: '.l-bd ul',titCell: '.l-hd ul',effect: 'left',autoPage: '<li></li>',autoPlay: 3000});
			}
		}
	</script>
</div>