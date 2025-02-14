
<div class="banner catetop-banner">
    <div class="banner-ad">
        <div class="w w1200">
            {{-- DSC 提醒您：动态载入cate_layer_right.lbi，轮播右侧广告 --}}
{!! insert_get_adv_child(['ad_arr' => $top_style_right_banne, 'id' => $cate_info['cat_id']]) !!}
        </div>
    </div>
    <div class="bd">{{-- DSC 提醒您：动态载入cat_top_ad.lbi，轮播广告 --}}
{!! insert_get_adv_child(['ad_arr' => $top_style_elec_banner, 'id' => $cate_info['cat_id']]) !!}</div>
    <div class="hd"><ul></ul></div>
</div>
<div class="catetop-main w w1200" ectype="catetopWarp">
    
    <div class="hotrecommend" id="hotrecommend">
        <div class="hd">
            <h2>{{ $lang['Popular_recommendation'] }}</h2>
            <div class="extra">
                <div class="hr-slide-hd">
                    <ul>
                        <li>{{ $lang['new_first_start'] }}</li>
                        <li>{{ $lang['hot_rankings'] }}</li>
                        <li>{{ $lang['best_goods'] }}</li>
                        <li>{{ $lang['today_deal'] }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="bd">
            <ul class="clearfix">
                
@foreach($cate_top_new_goods as $goods)

                
@if($loop->iteration < 7)

                <li>
                    <div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
                    <div class="p-price">
                    	
@if($goods['promote_price'] != 0 && $goods['promote_price'] != '' )

                    		{{ $goods['promote_price'] }}
                       	
@else

                        	{{ $goods['shop_price'] }}
                       	
@endif

                    </div>
                    <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
                    <a href="{{ $goods['url'] }}" class="p-btn">{{ $lang['View_details'] }}</a>
                </li>
                
@endif

                
@endforeach

            </ul>
            <ul class="clearfix">
                
@foreach($cate_top_hot_goods as $goods)

                
@if($loop->iteration < 7)

                <li>
                    <div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
                    <div class="p-price">
                    	
@if($goods['promote_price'] != 0 && $goods['promote_price'] != '' )

                        	{{ $goods['promote_price'] }}
                    	
@else

                        	{{ $goods['shop_price'] }}
                    	
@endif

                    </div>
                    <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
                    <a href="{{ $goods['url'] }}" class="p-btn">{{ $lang['View_details'] }}</a>
                </li>
                
@endif

                
@endforeach

            </ul>
            <ul class="clearfix">
                
@foreach($cate_top_best_goods as $goods)

                
@if($loop->iteration < 7)

                <li>
                    <div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
                    <div class="p-price">
                        
@if($goods['promote_price'] != 0 && $goods['promote_price'] != '' )

                        	{{ $goods['promote_price'] }}
                        
@else

                        	{{ $goods['shop_price'] }}
                        
@endif

                    </div>
                    <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
                    <a href="{{ $goods['url'] }}" class="p-btn">{{ $lang['View_details'] }}</a>
                </li>
                
@endif

                
@endforeach

            </ul>
            <ul class="clearfix">
                
@foreach($cate_top_promote_goods as $goods)

                
@if($loop->iteration < 7)

                <li>
                    <div class="p-img"><a href="{{ $goods['url'] }}"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
                    <div class="p-price">
                        
@if($goods['promote_price'] != 0 && $goods['promote_price'] != '' )

                        	{{ $goods['promote_price'] }}
                       	
@else

                        	{{ $goods['shop_price'] }}
                       	
@endif

                    </div>
                    <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
                    <a href="{{ $goods['url'] }}" class="p-btn">{{ $lang['View_details'] }}</a>
                </li>
                
@endif

                
@endforeach

            </ul>
        </div>
    </div>
    
    <div class="catetop-brand clearfix" id="catBrand">
        <div class="hd"><h2>{{ $lang['brand_flagship'] }}</h2></div>
        <div class="bd">
            <div class="cb-l">{{-- DSC 提醒您：动态载入cate_layer_right.lbi，显示品牌左侧小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $top_style_elec_brand_left, 'id' => $cate_info['cat_id']]) !!}</div>
            <div class="cb-m">
                {{-- DSC 提醒您：动态载入top_style_elec_brand.lbi，显示品牌右侧小广告 --}}
{!! insert_get_adv_child(['ad_arr' => $top_style_elec_brand, 'id' => $cate_info['cat_id']]) !!}
            </div>
            <div class="cb-r">
                <ul>
                
@foreach($cat_brand['brands'] as $brand)

                
@if($loop->iteration < 13)

                <li><a href="{{ $brand['url'] }}" target="_blank" title="{{ $brand['brand_name'] }}"><img src="{{ $brand['brand_logo'] }}" alt=""></a></li>
                
@endif

                
@endforeach

                </ul>
            </div>
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
        	<div class="catetop-lift-item lift-item-current" ectype="liftItem" data-target="#hotrecommend"><span>{{ $lang['Popular_recommendation'] }}</span></div>
            <div class="catetop-lift-item" ectype="liftItem" data-target="#catBrand"><span>{{ $lang['brand_flagship'] }}</span></div>
        	
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
			Floor.slide({mainCell:".bd-right",titCell:".fgoods-hd ul li"});
		}
	</script>
</div>