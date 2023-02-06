
<div class="discuss-right">
	<div class="d-main d-goods">
		<div class="d-tit">{{ $lang['goods_info'] }}</div>
		<div class="d-g-info">
			<div class="p-img"><a href="{{ $goodsInfo['goods_url'] }}" target="_blank"><img src="{{ $goodsInfo['goods_thumb'] }}"></a></div>
			<div class="p-price">{{ $goodsInfo['goods_price'] }}</div>
			<div class="p-name"><a href="{{ $goodsInfo['goods_url'] }}" target="_blank" title="{{ $goodsInfo['goods_name'] }}">{{ $goodsInfo['goods_name'] }}</a> 
@if($is_presale)
<span class='red'>{{ $lang['yu'] }}</span>
@endif
</div>
			<div class="p-lie">{{ $lang['cumulative_comment'] }}<b>{{ $comment_all['allmen'] }}</b></div>
			<div class="tc">
				
@if($is_presale)

				<a href="{!! $goodsInfo['goods_url'] !!}" rev="{{ $goodsInfo['goods_thumb'] }}" class="btn">{{ $lang['View_details'] }}</a>
				
@else

				<a href="javascript:void(0);" onClick="addToCart({{ $goodsInfo['goods_id'] }},0,event,this,'flyItem');" rev="{{ $goodsInfo['goods_thumb'] }}" class="btn">{{ $lang['btn_add_to_cart'] }}</a>
				
@endif

				
            </div>
		</div>
	</div>
	
@if($act != 'discuss_show')

	<div class="my-post">
		<a href="#doPost" class="btn sc-redBg-btn"><i class="iconfont icon-edit"></i>{{ $lang['my_dispost_post'] }}</a>
	</div>
	
@endif

	
@if($hot_list['list'])

	<div class="d-main d-hot">
		<div class="d-tit">{{ $lang['hot_dispost_post'] }}</div>
		<div class="d-info">
			<ul>
				
@foreach($hot_list['list'] as $list)

				<li>
					<i class="icon icon-tie 
@if($list['dis_type'] == 1)
icon-tao
@elseif ($list['dis_type'] == 2)
icon-wen
@elseif ($list['dis_type'] == 3)
icon-quan
@elseif ($list['dis_type'] == 4)
icon-shai
@else

@endif
"></i>
					<a href="single_sun.php?act=discuss_show&did={{ $list['dis_id'] }}&dis_type={{ $list['dis_type'] }}" target="_blank">{{ $list['dis_title'] }}</a>
				</li>
				
@endforeach

			</ul>
		</div>
	</div>
	
@endif

</div>
<div id="flyItem" class="fly_item"><img src="" width="40" height="40"></div>