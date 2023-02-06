

@if($tpl == 1)


@if($one_cate_child)

<div class="catetop-floor" id="floor_{{ $rome_number }}" ectype="floorItem">
	<div class="f-hd">
		<h2>{{ $one_cate_child['cat_name'] }}</h2>
		<h3>{{ $rome_number }}F</h3>
		<div class="extra">
			<div class="fgoods-hd">
				<ul>
					
@foreach($one_cate_child['cat_list'] as $child)

					
@if($loop->iteration < 6)

					<li 
@if($loop->iteration == 1)
 class="on"
@endif
>{{ $child['cat_name'] }}</li>
					
@endif

					
@endforeach
				
				</ul>
			</div>
		</div>
	</div>
	<div class="f-bd clearfix">
		<div class="bd-left">
			<div class="l-ad"><ul>{{ $cat_top_floor_ad }}</ul></div>
		</div>
		<div class="bd-right">
			<div class="right-top clearfix">
				{{ $cat_top_floor_ad_right }}
			</div>
			<div class="right-bottom">
				
@foreach($one_cate_child['cat_list'] as $child)

				
@if($loop->iteration < 6)

				<ul class="fgoods-list">
					
@foreach($child['goods_list'] as $goods)

					<li>
						<div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
						<div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
						<div class="p-price">{{ $goods['shop_price'] }}</div>
					</li>
					
@endforeach

				</ul>
				
@endif

				
@endforeach

			</div>
		</div>
		<div class="clear"></div>
		
@if($one_cate_child['brands'])

		<ul class="brands">
			
@foreach($one_cate_child['brands'] as $brand)

			
@if($loop->iteration < 11)

			<li><a href="{{ $brand['url'] }}" target="_blank"><img src="{{ $brand['brand_logo'] }}" alt="{{ $brand['brand_name'] }}"></a></li>
			
@endif

			
@endforeach

		</ul>
		
@endif

	</div>
</div>

@endif


@endif




@if($tpl == 2)


@if($one_cate_child)

<div class="catetop-floor" id="floor_{{ $rome_number }}" ectype="floorItem">
    <div class="f-hd">
        <h2>{{ $one_cate_child['cat_name'] }}</h2>
        <div class="extra">
            <div class="fgoods-hd">
                <ul>
                    
@foreach($one_cate_child['cat_list'] as $child)

                    
@if($loop->index<5)

                    <li
@if($loop->iteration == 1)
 class="on"
@endif
>{{ $child['cat_name'] }}</li>
                    
@endif

                    
@endforeach

                </ul>
            </div>
        </div>
    </div>
    <div class="f-bd">
        <div class="bd-left">
            <div class="l-ad"><ul>{{ $top_style_elec_left }}</ul></div>
            <div class="l-menu">
                
@foreach($one_cate_child['cat_list'] as $child)

                    
@if($loop->index<6)

                    <a href="{{ $child['url'] }}" target="_blank">{{ $child['cat_name'] }}</a>
                    
@endif

                
@endforeach

            </div>
        </div>
        <div class="bd-right">
            
@foreach($one_cate_child['cat_list'] as $child)

            
@if($loop->index<5)

            <ul class="fgoods-list"
@if($loop->first)
 style="display:block;"
@else
 style="display:none;"
@endif
>
                
@foreach($child['goods_list'] as $goods)

                    
@if($loop->iteration == 1)

                        <li class="first">
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
                            <div class="p-info">
                                <div class="info-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
                                <div class="info-handle">
                                    <div class="info-price">
                                        
@if($goods['promote_price'] != 0 && $goods['promote_price'] != '' )

                                        {{ $goods['promote_price'] }}
                                        
@else

                                        {{ $goods['shop_price'] }}
                                        
@endif

                                    </div>
                                    <a href="{{ $goods['url'] }}" class="info-btn" target="_blank">{{ $lang['View_details'] }}</a>
                                </div>
                            </div>
                        </li>
                    
@elseif ($loop->iteration>1&&$loop->iteration<8)

                        <li 
@if($loop->iteration == 1)
class="first"
@endif
>
                            <div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" alt=""></a></div>
                            <div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['name'] }}">{{ $goods['name'] }}</a></div>
                            <div class="p-price">
                                
@if($goods['promote_price'] != 0 && $goods['promote_price'] != '' )

                                {{ $goods['promote_price'] }}
                                
@else

                                {{ $goods['shop_price'] }}
                                
@endif

                            </div>
                        </li>
                    
@endif

                
@endforeach

            </ul>
            
@endif

            
@endforeach
   
        </div>
        <div class="clear"></div>
        <ul class="brands">
            
@foreach($one_cate_child['brands'] as $kid => $brand)

            
@if($loop->index<8)

                <li><a href="{{ $brand['url'] }}" target="_blank" title="{{ $brand['brand_name'] }}"><img src="{{ $brand['brand_logo'] }}" alt=""></a></li>
            
@endif

            
@endforeach

        </ul>
        <div class="f-banner">{{ $top_style_elec_row }}</div>
    </div>
</div>

@endif


@endif




@if($tpl == 3)


@if($one_cate_child)

<div class="catetop-floor" id="floor_{{ $rome_number }}" ectype="floorItem">
    <div class="f-hd">
        <h2>{{ $one_cate_child['cat_name'] }}</h2>
        <div class="extra">
            <div class="fgoods-hd">
                <ul>
                
@foreach($one_cate_child['cat_list'] as $child)

                    
@if($loop->index<5)

                    <li>{{ $child['cat_name'] }}</li>
                    
@endif

                
@endforeach

                </ul>
            </div>
        </div>
    </div>
    <div class="f-bd">
        <div class="bd-left">
            <div class="l-slide">
                <div class="l-bd">
                    <ul>
                        {{ $top_style_food_left }}
                    </ul>
                </div>
                <div class="l-hd"><ul></ul></div>
            </div>
        </div>
        <div class="bd-right">
        
@foreach($one_cate_child['cat_list'] as $child)

        
@if($loop->index<5)

            <ul class="fgoods-list" 
@if($loop->iteration >1)
 style="display:none;" 
@endif
>
                
@foreach($child['goods_list'] as $goods)

                
@if($loop->iteration>0&&$loop->iteration<7)

                <li>
                    <div class="p-img"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}" target="_blank"><img src="{{ $goods['thumb'] }}" alt="{{ $goods['name'] }}"></a></div>
                    <div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['name'] }}" target="_blank">{{ $goods['name'] }}</a></div>
                    <div class="p-price">
                        
@if($goods['promote_price'] != 0&&$goods['promote_price'] != '' )

                        {{ $goods['promote_price'] }}
                        
@else

                        {{ $goods['shop_price'] }}
                        
@endif

                    </div>
                    <a href="{{ $goods['url'] }}" class="p-btn" target="_blank"><i class="iconfont icon-cart"></i>{{ $lang['button_buy'] }}</a>
                </li>
                
@endif

                
@endforeach

            </ul>
        
@endif

        
@endforeach
  

        </div>
        <div class="clear"></div>
        <ul class="brands">
            
@foreach($one_cate_child['brands'] as $kid => $brand)

            
@if($loop->iteration<10)

                <li><a href="{{ $brand['url'] }}" target="_blank"><img src="{{ $brand['brand_logo'] }}" alt=""/></a></li>
            
@endif

            
@endforeach

        </ul>
        <div class="f-banner">{{ $top_style_food_row }}</div>
    </div>
</div>

@endif


@endif
