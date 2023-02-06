
@if($goods_list)

	<ul class="list-ul">
	
@foreach($goods_list as $goods)

		<li
@if($loop->iteration % 4 == 0)
 class="last"
@endif
>
			<div class="coll-goods">
				<div class="p-img"><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}"></a></div>
				<div class="p-name"><a href="{{ $goods['url'] }}" target="_blank" title="{{ $goods['goods_name'] }}">{{ $goods['goods_name'] }}</a></div>
				<div class="p-price">
                    
@if($goods['promote_price'] != "")

                    {{ $lang['promote_price'] }}{{ $goods['promote_price'] }}
                    
@else

					{{ $goods['shop_price'] }}
                    
@endif

				</div>
				<div class="p-btn"><a href="{{ $goods['url'] }}" class="sc-btn">{{ $lang['View_details'] }}</a></div>
				<div class="p-oper">
                    
@if($goods['is_attention'])

                    <a class="goods_gz has_gz" href="javascript:void(0);" data-dialog="goods_collect_dialog" data-divid="user_attention" data-url="user_collect.php?act=del_attention&rec_id={{ $goods['rec_id'] }}&goods_id={{ $goods['goods_id'] }}" data-confirmtitle="{{ $lang['del_attention'] }}">{{ $lang['follow_yes'] }}</a>
                    
@else

                    <a class="goods_gz" href="javascript:void(0);" data-dialog="goods_collect_dialog" data-divid="user_attention" data-url="user_collect.php?act=add_to_attention&rec_id={{ $goods['rec_id'] }}&goods_id={{ $goods['goods_id'] }}" data-confirmtitle="{{ $lang['add_to_attention'] }}">{{ $lang['follow_goods'] }}</a>
                    
@endif

                    
					<a href="javascript:void(0);" id="delete_goods_collect" data-dialog="goods_collect_dialog" data-divid="delete_goods_collect" data-url="user_collect.php?act=delete_collection&collection_id={{ $goods['rec_id'] }}&type=1" data-goodsid="0">{{ $lang['cancel_collect'] }}</a>
				</div>
			</div>
		</li>
	
@endforeach

	</ul>

@else

<div class="no_records">
	<i class="no_icon"></i>
    <div class="no_info"><h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3></div>
</div>

@endif
