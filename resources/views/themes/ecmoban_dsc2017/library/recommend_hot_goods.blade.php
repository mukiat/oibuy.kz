

@if($hot_goods)

<div class="mc-main" style="display:block;">
	<div class="mcm-left">
		<div class="spirit"></div>
		
@foreach($hot_goods as $goods)

		<div class="rank-number rank-number{{ $loop->iteration }}">{{ $loop->iteration }}</div>
		
@endforeach

	</div>
	<div class="mcm-right">
		<ul>
			
@foreach($hot_goods as $goods)

			<li>
				<div class="p-img"><a href="{{ $goods['url'] }}" title="{{ $goods['short_style_name'] }}"><img src="{{ $goods['thumb'] }}" width="130" height="130"></a></div>
				<div class="p-name"><a href="{{ $goods['url'] }}" title="{{ $goods['short_style_name'] }}">{!! $goods['short_style_name'] !!}</a></div>
				<div class="p-price">
					
@if($goods['promote_price'] != '')

						{{ $goods['promote_price'] }}
					
@else

						{{ $goods['shop_price'] }}
					
@endif

				</div>
			</li>
			
@endforeach

		</ul>
	</div>
</div>

@endif
 