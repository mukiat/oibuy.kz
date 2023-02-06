

@if($goods_list)

<ul>
	
@foreach($goods_list as $goods)

	
@if($loop->index < 4)

	<li class="opacity_img">
		<div class="p-img"><a href="{{ $goods['goods_url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}"></a></div>
		<div class="p-name"><a href="{{ $goods['goods_url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
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

@else

<div class="no_records">
	<i class="no_icon_two"></i>
	<div class="no_info">
		<h3>{{ $lang['information_null'] }}</h3>
	</div>
</div>

@endif
