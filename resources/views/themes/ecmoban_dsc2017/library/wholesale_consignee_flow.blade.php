<div class="consignee-warp">
    
@if($user_address)

        
@foreach($user_address as $address)

        <div class="cs-w-item
@if($consignee['address_id'] == $address['address_id'])
 cs-selected
@endif
" data-addressid="{{ $address['address_id'] }}" ectype="w-cs-w-item">
            <div class="item-tit">
                <h3 class="username">{{ $address['consignee'] }}</h3>
                <span class="remark">{{ $address['sign_building'] }}</span>
            </div>
            <div class="item-tel">
                <span class="contact">{{ $address['mobile'] }}</span>
            </div>
            <div class="item-address">{{ $address['region'] }} &nbsp; {{ $address['address'] }}</div>
            <i class="icon"></i>
            <a href="javascript:void(0);" class="edit" ectype="wholesale_dialog_checkout" data-value='{"divId":"edit_address","id":{{ $address['address_id'] }},"url":"wholesale_flow.php?step=edit_Consignee","width":900,"title":"{{ $lang['edit_consignee_address'] }}"}'>修改</a>
            <a href="javascript:void(0);" class="delete" ectype="wholesale_dialog_checkout" data-value='{"divId":"del_address","id":{{ $address['address_id'] }},"url":"wholesale_flow.php?step=delete_Consignee","width":450,"height":50,"title":"{{ $lang['remove_consignee_address'] }}"}'>{{ $lang['drop'] }}</a>
            <input type="radio" 
@if($consignee['address_id'] == $address['address_id'])
checked="checked"
@endif
 class="ui-radio" name="consignee_radio" value="{{ $address['address_id'] }}" id="radio_{{ $address['address_id'] }}" class="hookbox" />
        </div>
        
@endforeach

        <div class="cs-w-item">
            <a href="javascript:void(0);" class="add-new-address" ectype="wholesale_dialog_checkout" data-value='{"divId":"new_address","id":0,"url":"wholesale_flow.php?step=edit_Consignee","width":900,"height":450,"title":"{{ $lang['add_consignee_address'] }}"}'>
                <i class="iconfont icon-add-quer"></i><span>添加新地址</span>
            </a>
        </div>
    
@else

        <div class="cs-w-item">
            <a href="javascript:void(0);" class="add-new-address" ectype="wholesale_dialog_checkout" data-value='{"divId":"new_address","id":0,"url":"wholesale_flow.php?step=edit_Consignee","width":900,"height":450,"title":"{{ $lang['add_consignee_address'] }}"}'>
                <i class="iconfont icon-add-quer"></i><span>添加新地址</span>
            </a>
        </div>
    
@endif

    <div class="clear"></div>
    <input type="hidden" id="address_count" value="{{ config('app.address_count', 50) }}"/>
</div>
