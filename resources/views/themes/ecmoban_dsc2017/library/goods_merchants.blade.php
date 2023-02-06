<div class="g-main g-store-info" ectype="gm-tabs">
	<div class="mt">
		<h3>{{ $goods['rz_shop_name'] }}</h3>
		<a id="IM" onclick="openWin(this)" href="javascript:void(0);"  ru_id="{{ $goods['user_id'] }}" goods_id="{{ $goods['goods_id'] }}" class="s-a-kefu"><i class="icon i-kefu"></i></a>
	</div>
	<div class="mc">
		<div class="mc-warp">
			<div class="g-s-brand">

@if($goods['user_id'])


@if($goods['shopinfo']['brand_thumb'])

					<a href="{{ $goods['store_url'] }}" target="_blank"><img src="{{ $goods['shopinfo']['brand_thumb'] }}" /></a>

@else

					<a href="{{ $goods['goods_brand_url'] }}" target="_blank">{{ $goods['goods_brand'] }}</a>

@endif


@else


@if($goods['brand']['brand_logo'])

					<a href="{{ $goods['brand']['url'] }}" target="_blank"><img src="{{ $goods['brand']['brand_logo'] }}" /></a>

@else

					<a href="{{ $goods['goods_brand_url'] }}" target="_blank">{{ $goods['goods_brand'] }}</a>

@endif


@endif

			</div>
		</div>

@if($filename != 'group_buy' && $filename != 'seckill')


@if($goods['user_id'])

		<div class="mc-warp b-t-gary">
			<div class="s-search">
				<form action="merchants_store.php" method="get">
				<p class="sp-form-item1"><input type="text" name="keyword" class="text" id="sp-keyword" value="" placeholder="{{ $lang['keywords'] }}"></p>
				<p class="sp-form-item2"><input type="text" id="sp-price" name="price_min" class="text" value="" placeholder="{{ $lang['price'] }}"><span>~</span><input type="text" name="price_max" class="text" id="sp-price1" value="" placeholder="{{ $lang['price'] }}"></p>
				<p class="sp-form-item3"><input type="submit" value="{{ $lang['seller_search'] }}" class="search-btn" id="btnShopSearch"></p>
				<input type="hidden" name="merchant_id" value="{{ $goods['user_id'] }}">
				@csrf </form>
			</div>
		</div>

@endif


@endif

	</div>
</div>

