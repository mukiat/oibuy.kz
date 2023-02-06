<div class="gift_gard_list">
    <h3 class="title">
      <span>{{ $lang['goods_list'] }}</span>
      <em class="ml10 red">{{ $lang['gift_gard_notic'] }}</em>
      <div class="fr">{{ $lang['Credit_Card_Number'] }}：<i>{{ $gift_sn }}</i><a href="gift_gard.php?act=exit_gift">{{ $lang['Exit_gift_card'] }}</a></div>
    </h3>
	<div class="gift_list_form">
        <form name="compareForm" method="post">

@if($goods_list)

        <ul class="relative-list">

@foreach($goods_list as $goods)

            <li>
                <div class="recommend-item-pic"><a href="goods.php?id={{ $goods['goods_id'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" alt="" class="goodsimg" width="220" height="220" /></a></div>
                <div class="recommend-item-info">
                    <p><span class="price">{{ $goods['shop_price'] }}</span></p>
                    <p><a href="goods.php?id={{ $goods['goods_id'] }}" title="{{ $goods['goods_name'] }}" class="item-title" target="_blank">{{ $goods['goods_name'] }}</a></p>
                	<p><a href="javascript:void(0);" ectype="openLayer" data-value="{{ $goods['goods_id'] }}" class="btn_th">{{ $lang['Take_delivery_goods'] }}</a></p>
            	</div>
            </li>

@endforeach

        </ul>
@else
        <p class="nolist">{{ $lang['gift_gard_null'] }}</p>

@endif

        @csrf </form>
    </div>
</div>
<input name="Input" id="test3" type="hidden" />