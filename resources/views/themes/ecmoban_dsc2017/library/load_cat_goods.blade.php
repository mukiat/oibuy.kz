{{ $get_adv }}
<div class="floor-line-con floorOne floor-color-type-{{ $goods_cat['floor_sort_order'] }}" data-title="{{ $goods_cat['alias_name'] }}" data-idx="{{ $goods_cat['floor_sort_order'] }}" id="floor_{{ $goods_cat['floor_sort_order'] }}" ectype="floorItem">
    <div class="floor-hd" ectype="floorTit">
    	<i class="box_hd_arrow"></i>
    	<i class="box_hd_dec"></i>
        <div class="hd-tit">{{ $goods_cat['name'] }}</div>
        <div class="hd-tags">
            <ul>
                <li class="first current"><span>{{ $lang['new_arrivals'] }}</span><i class="arrowImg"></i></li>
                
@foreach($goods_cat['goods_level2'] as $key => $cat)

                
@if($key < 7)

                <li data-value='{"id":{{ $cat['id'] ?? 0 }},"floornum":{{ $goods_cat['floor_num'] ?? 0 }},"warehouse":{{ $goods_cat['warehouse_id'] ?? 0 }},"area":{{ $goods_cat['area_id'] ?? 0 }},"city":{{ $goods_cat['area_city'] ?? 0 }}}' data-flooreveval="0" ectype="floor_cat_content"><span>{{ $cat['name'] }}</span><i class="arrowImg"></i></li>
                
@endif

                
@endforeach

            </ul>
        </div>
    </div>
	
@if($goods_cat['floor_style_tpl'] == 1)

	<div class="floor-bd bd-mode-02">
		<div class="bd-left">
			<div class="floor-left-slide">
				<div class="bd">
					{{ $cat_goods_banner }}
				</div>
				<div class="hd"><ul></ul></div>
			</div>
			<div class="floor-left-adv mt10">
				
@if($cat_goods_ad_left)

				{{ $cat_goods_ad_left }}
				
@endif

			</div>
		</div>
		<div class="bd-right">
			<div class="floor-tabs-content clearfix">
				<div class="f-r-main f-r-m-adv">
					
@if($cat_goods_ad_right)

					{{ $cat_goods_ad_right }}
					
@endif

				</div>
				
@foreach($goods_cat['goods_level3'] as $key => $goods_level3)

				<div class="f-r-main" id="floor_cat_{{ $goods_level3['cats'] }}">
					<ul class="p-list">
						
@foreach($goods_level3['goods'] as $goods)

						<li class="opacity_img">
							<a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">
								<div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
								<div class="p-name">{{ $goods['goods_name'] }}</div>
								<div class="p-price">
									
@if($goods['promote_price'] != '')

										{{ $goods['promote_price'] }}
									
@else

										{{ $goods['shop_price'] }}
									
@endif

								</div>
							</a>
						</li>
						
@endforeach

					</ul>
				</div>
				
@endforeach

			</div>
		</div>
	</div>
	
@elseif ($goods_cat['floor_style_tpl'] == 2)

    <div class="floor-bd bd-mode-03">
        <div class="bd-left">
            <div class="floor-left-adv">
                
@if($cat_goods_ad_left)

                {{ $cat_goods_ad_left }}
                
@endif

            </div>
        </div>
        <div class="bd-right">
            <div class="floor-tabs-content clearfix">
                <div class="f-r-main f-r-m-adv">
                    
@if($cat_goods_ad_right)

                    {{ $cat_goods_ad_right }}
                    
@endif

                </div>
                
@foreach($goods_cat['goods_level3'] as $key => $goods_level3)

                <div class="f-r-main" id="floor_cat_{{ $goods_level3['cats'] }}">
                    <ul class="p-list">
                        
@foreach($goods_level3['goods'] as $goods)

                        <li class="opacity_img">
                            <a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">
                                <div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
                                <div class="p-name">{{ $goods['goods_name'] }}</div>
                                <div class="p-price">
                                    
@if($goods['promote_price'] != '')

                                        {{ $goods['promote_price'] }}
                                    
@else

                                        {{ $goods['shop_price'] }}
                                    
@endif

                                </div>
                            </a>
                        </li>
                        
@endforeach

                    </ul>
                </div>
                
@endforeach

            </div>
        </div>
    </div>
	
@elseif ($goods_cat['floor_style_tpl'] == 3)

    <div class="floor-bd bd-mode-04">
        <div class="bd-left">
            <div class="floor-left-adv">
                
@if($cat_goods_ad_left)

                {{ $cat_goods_ad_left }}
                
@endif

            </div>
            <div class="floor-left-slide mt10">
                <div class="bd">
                    {{ $cat_goods_banner }}
                </div>
                <div class="hd"><ul></ul></div>
            </div>
        </div>
        <div class="bd-right">
            <div class="floor-tabs-content clearfix">
                <div class="f-r-main f-r-m-adv">
                    
@if($cat_goods_ad_right)

                    {{ $cat_goods_ad_right }}
                    
@endif

                </div>
                
@foreach($goods_cat['goods_level3'] as $key => $goods_level3)

                <div class="f-r-main" id="floor_cat_{{ $goods_level3['cats'] }}">
                    <ul class="p-list">
                        
@foreach($goods_level3['goods'] as $goods)

                        <li class="opacity_img">
                            <a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">
                                <div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
                                <div class="p-name">{{ $goods['goods_name'] }}</div>
                                <div class="p-price">
                                    
@if($goods['promote_price'] != '')

                                        {{ $goods['promote_price'] }}
                                    
@else

                                        {{ $goods['shop_price'] }}
                                    
@endif

                                </div>
                            </a>
                        </li>
                        
@endforeach

                    </ul>
                </div>
                
@endforeach

            </div>
        </div>
    </div>
	
@else

    <div class="floor-bd bd-mode-01">
        <div class="bd-left">
            <div class="floor-left-slide">
                <div class="bd">
                    {{ $cat_goods_banner }}
                </div>
                <div class="hd"><ul></ul></div>
            </div>
            <div class="floor-left-adv">
                
@if($cat_goods_ad_left)

                {{ $cat_goods_ad_left }}
                
@endif

            </div>
        </div>
        <div class="bd-right">
            <div class="floor-tabs-content clearfix">
                <div class="f-r-main f-r-m-adv">
                    
@if($cat_goods_ad_right)

                    {{ $cat_goods_ad_right }}
                    
@endif

                </div>
                
@foreach($goods_cat['goods_level3'] as $key => $goods_level3)

                <div class="f-r-main" id="floor_cat_{{ $goods_level3['cats'] }}">
                    <ul class="p-list">
                        
@foreach($goods_level3['goods'] as $goods)

                        <li class="opacity_img">
                            <a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">
                                <div class="p-img"><img src="{{ $goods['goods_thumb'] }}"></div>
                                <div class="p-name">{{ $goods['goods_name'] }}</div>
                                <div class="p-price">
                                    
@if($goods['promote_price'] != '')

                                        {{ $goods['promote_price'] }}
                                    
@else

                                        {{ $goods['shop_price'] }}
                                    
@endif

                                </div>
                            </a>
                        </li>
                        
@endforeach

                    </ul>
                </div>
                
@endforeach

            </div>
        </div>
    </div>
	
@endif

    
@if($brands_theme2)

    <div class="floor-fd">
        <div class="floor-fd-brand clearfix">
            
@foreach($brands_theme2 as $key1 => $brands)

            
@foreach($brands as $key2 => $brands)

            <div class="item">
                <a href="{{ $brands['url'] }}" target="_blank">
                    <div class="link-l"></div>
                    <div class="img"><img src="{{ $brands['brand_logo'] }}" title="{{ $brands['brand_name'] }}"></div>
                    <div class="link"></div>
                </a>
            </div>
            
@endforeach

            
@endforeach

        </div>
    </div>
    
@endif

</div>