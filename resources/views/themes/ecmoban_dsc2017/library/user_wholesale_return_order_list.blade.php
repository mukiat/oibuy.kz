<div class="user-order-list">

@forelse($orders as $item)

<dl class="item">
    <dt class="item-t">
        <div class="t-statu">
@if($item['return_status'] == 6)
<span class="red">{{ $lang['rf'][$item['return_status']] }}</span>
@else
{{ $item['reimburse_status'] }}
@endif
</div>
        <div class="t-info">
            <span class="info-item">{{ $lang['return_sn_user'] }}：{{ $item['return_sn'] }}</span>
            <span class="info-item">{{ $lang['apply_time'] }}：{{ $item['apply_time'] }}</span>
        </div>
        
@if($item['return_type'] == 1)
<div class="t-price">{{ $lang['y_amount'] }}：<span class="red">{{ $item['should_return'] }}</span></div>
@endif

    </dt>
    <dd class="item-c">
        <div class="c-left">
            <div class="c-goods">
                <div class="c-img"><a href="wholesale_goods.php?id={{ $item['goods_id'] }}" target="_blank"><img src="{{ $item['goods_thumb'] }}" alt=""></a></div>
                <div class="c-info">
                    <div class="info-name"><a href="wholesale_goods.php?id={{ $item['goods_id'] }}" target="_blank">{{ $item['goods_name'] }}</a></div>
                </div>
            </div>
        </div>
        <div class="c-handle">
            <a href="user_wholesale.php?act=wholesale_return_detail&ret_id={{ $item['ret_id'] }}&order_id={{ $item['order_id'] }}" class="sc-btn">{{ $lang['to_view'] }}</a>
			
@if($item['activation_type'] == 1)

			<a href="#" ectype="activation_return_order" class="sc-btn" data-id="{{ $item['ret_id'] }}" >{{ $lang['activation_return'] }}</a>
			
@endif

            
@if($item['return_status'] == 3)
<a href="user_wholesale.php?act=wholesale_return_delivery&order_id={{ $item['order_id'] }}" class="sc-btn" onclick="if (!confirm('{{ $lang['confirm_received'] }}')) return false;">{{ $lang['received'] }}</a>
@endif

            
@if($item['return_status'] == 0 && $item['refound_status'] == 0)

            <a href="user_wholesale.php?act=wholesale_cancel_return&ret_id={{ $item['ret_id'] }}" onclick="if (!confirm('{{ $lang['user_cancel_return'] }}')) return false;" class="sc-btn">{{ $lang['is_cancel'] }}</a>
            
@endif

            
@if($item['return_status'] == 4)
<a href="javascript:get_wholesale_order_delete_return({{ $item['ret_id'] }});" class="sc-btn">{{ $lang['drop'] }}</a>
@endif

        </div>
    </dd>
</dl>

@empty

<div class="no_records">
    <i class="no_icon"></i>
    <div class="no_info"><h3>{!! insert_get_page_no_records(['filename' => $filename, 'act' => $action]) !!}</h3></div>
</div>

@endforelse

</div>
 <script language="javascript">
    $("*[ectype='activation_return_order']").click(function(){
		if(confirm("{{ $lang['user_activation_return'] }}")){
			var ret_id = $(this).data('id');
			$.jqueryAjax('user_wholesale.php', 'act=wholesale_activation_return_order&ret_id='+ret_id, function(result){
				if(result.error == 1){
					pbDialog(result.err_msg,'',0);
				}else{
					location.href = "user_wholesale.php?act=wholesale_return_list";
				}
			})
		}
	});
</script>
