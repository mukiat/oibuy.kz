<div class="banner catetop-banner">
	<div class="bd">{{-- DSC 提醒您：动态载入cat_top_ad.lbi，显示首页分类小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $cat_top_ad, 'id' => $cate_info['cat_id']]) !!}</div>
	<div class="cloth-hd"><ul></ul></div>
</div>
<div class="catetop-main w w1200" ectype="catetopWarp">
	
	<div class="limitime" id="limitime">
		<div class="hd">
			<h2>{{ $lang['Flash_sale'] }}</h2>
			<h3>{{ $lang['every_day'] }}</h3>
		</div>
		<div class="bd">
			<ul class="limitime-list clearfix">
				
@foreach($cate_top_promote_goods as $goods)

				<li class="mod-shadow-card">
					<a href="{{ $goods['url'] }}" class="img"><img src="{{ $goods['thumb'] }}" alt=""></a>
					<p class="price">
						
@if($goods['promote_price'] != '')

							 {{ $goods['promote_price'] }}
						
@else

							 {{ $goods['shop_price'] }}
						
@endif
					
						<del>{{ $goods['market_price'] }}</del>
					</p>
					<a href="{{ $goods['url'] }}" class="name" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a>
					<a href="{{ $goods['url'] }}" class="limitime-btn">{{ $lang['View_details'] }}</a>
				</li>
				
@endforeach

			</ul>
		</div>
	</div>
	
	{{-- DSC 提醒您：动态载入recommend_merchants.lbi，显示首页推荐店铺小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $recommend_merchants, 'id' => $cat_id]) !!}
	
	
	<div class="catetop-floor-wp" ectype="goods_cat_level"></div>
	
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
        	<div class="catetop-lift-item lift-item-current" ectype="liftItem" data-target="#limitime"><span>{{ $lang['Flash_sale'] }}</span></div>
        	
@foreach($categories_child as $cat)

            <div class="catetop-lift-item lift-floor-item" ectype="liftItem"><span>{{ $cat['cat_name'] }}</span></div>
            
@endforeach

            <div class="catetop-lift-item" ectype="liftItem" data-target="#atwillgo"><span>{{ $lang['purchase_hand'] }}</span></div>
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
			Floor.slide({mainCell:".right-bottom",titCell:".fgoods-hd ul li",effect:"fold"});
		}
	</script>
</div>