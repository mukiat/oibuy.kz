<div class="tbar-panel-main" ectype="tbpl-main">
	<div class="tbar-panel-content" data-height="48" ectype="tbpl-content">
        <div class="order-list">
            <ul class="s-li-con">

@foreach($order_list as $orders)

                <li>
                    <span class="s-time">{{ $orders['order_time'] }}</span>
                    <div class="s-item">
                        <div class="s-img">
                        	<div class="bd">
                                <ul>

@foreach($orders['order_goods'] as $goods)

                                    <li><a href="{{ $goods['url'] }}" target="_blank"><img src="{{ $goods['goods_thumb'] }}" width="50" height="50" /></a></li>

@endforeach

                                </ul>
                            </div>

@if($loop->iteration > 4)

                            <a href="javascript:void(0);" class="prev"><b class="iconfont icon-left"></b></a>
                            <a href="javascript:void(0);" class="next"><b class="iconfont icon-right"></b></a>

@endif

                        </div>
                        <span class="s-pay-info">
                            <span class="s-price">{{ $orders['total_fee'] }}</span>
                            <span class="s-client">{{ $orders['consignee'] }}</span>
                            <span class="s-pay">{{ $orders['pay_name'] }}</span>
                        </span>
                        <span class="s-status-info">
                            <a href="{{ url('/') }}/user_order.php?act=order_detail&order_id={{ $orders['order_id'] }}" target="_blank" class="s-look-detail">{{ $lang['view'] }}</a>
                            <a href="{{ url('/') }}/user.php?act=track_packages" target="_blank" class="s-track">{{ $lang['Track'] }}&gt;</a>
                        </span>
                    </div>
                </li>

@endforeach

            </ul>
            <a href="{{ url('/') }}/user_order.php?act=order_list" target="_blank" class="s-btn">{{ $lang['View_all_orders'] }}</a>
        </div>
    </div>
</div>
<script>
$(".s-img").slide({titCell:"",mainCell:".bd ul",effect:"left",autoPlay:false,autoPage:true,trigger:"click",vis:4,scroll:1});
</script>
