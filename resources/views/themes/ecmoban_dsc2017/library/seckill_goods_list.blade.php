
@if($seckill_goods)


@if($ajax_seckill != 1)

<div class="seckill-channel" id="h-seckill">
	<div class="box-hd clearfix">
    	<i class="box_hd_arrow"></i>
    	<i class="box_hd_dec"></i>
		<i class="box-hd-icon"></i>
		<div class="sk-time-cd">
			<div class="sk-cd-tit">
@if($sec_begin_time)
{{ $lang['ju_start'] }}
@else
{{ $lang['ju_end'] }}
@endif
</div>
			<div class="cd-time fl" ectype="time" data-time='
@if($sec_begin_time)
{{ $sec_begin_time }}
@else
{{ $sec_end_time }}
@endif
'>
				<div class="days hide"></div>
				<span class="split hide">{{ $lang['day'] }}</span>
				<div class="hours"></div>
				<span class="split">{{ $lang['goods_js']['time'] }}</span>
				<div class="minutes"></div>
				<span class="split">{{ $lang['goods_js']['branch'] }}</span>
				<div class="seconds"></div>
				<span class="split">{{ $lang['goods_js']['second'] }}</span>
			</div>
		</div>
        <div class="sk-more"><a href="{{ $url_seckill }}" target="_blank">{{ $lang['more_secKill'] }}</a> <i class="arrow"></i></div>
	</div>
	<div class="box-bd clearfix">
    	
@endif

		<ul>
			
@foreach($seckill_goods as $goods)

            
@if($temp == 'backup_festival_1')

            <li class="opacity_img">
                <div class="p-img"><a href="{{ $goods['list_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}"></a></div>
                <div class="p-name"><a href="{{ $goods['list_url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
                <div class="p-price">
                    <span class="shop-price">{{ $goods['sec_price'] }}</span>
                    <span class="original-price">{{ $goods['market_price'] }}</span>
                </div>
            </li>
            
@else

			<li class="opacity_img">
				<div class="p-img"><a href="{{ $goods['list_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" class="img-lazyload"></a></div>
				<div class="p-name"><a href="{{ $goods['list_url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
				<div class="p-over">
					<div class="p-info">
						<div class="p-price">
							<span class="shop-price">{{ $goods['sec_price'] }}</span>
							<span class="original-price">{{ $goods['market_price'] }}</span>
						</div>
					</div>
					<div class="p-btn">
					
@if($sec_begin_time)

					<a href="{{ $goods['url'] }}"  target="_blank">{{ $lang['begin_minute'] }}</a>
					
@else

					
@if($goods['sec_num'] <= 0)

					<a href="javascript:;">{{ $lang['has_been_snatched'] }}</a>
					
@else

					<a href="{{ $goods['url'] }}"  target="_blank">{{ $lang['immediate_rush_buy'] }}</a>
					
@endif

					
@endif

					</div>
				</div>
			</li>
            
@endif

			
@endforeach

		</ul>
        <a href="javascript:void(0);" class="prev"><i class="iconfont icon-left"></i></a>
        <a href="javascript:void(0);" class="next"><i class="iconfont icon-right"></i></a>
        <input type="hidden" name="sec_begin_time" value="{{ $sec_begin_time }}">
        <input type="hidden" name="sec_end_time" value="{{ $sec_end_time }}">
        
@if($ajax_seckill != 1)

	</div>
</div>

@endif


@endif



@if($ajax_seckill != 1)

<input type="hidden" value="
@if($seckill_goods)
1
@else
0
@endif
" name="seckill_goods"/>

@endif
