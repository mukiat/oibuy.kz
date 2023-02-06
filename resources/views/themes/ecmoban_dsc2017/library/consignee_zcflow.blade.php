
<script type="text/javascript" src="{{ skin('js/sc_common.js') }}"></script>
<script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
<div class="consignee-addr">
    <div class="consignee-cont">
        <ul class="ui-switchable-panel-main">
            
@foreach($user_address as $address)

            <li 
@if($consignee['address_id'] == $address['address_id'])
class="item-selected"
@endif
 data-addressid="{{ $address['address_id'] }}">
                <input type="radio" 
@if($consignee['address_id'] == $address['address_id'])
checked="checked"
@endif
 class="ui-radio" name="consignee_radio" value="{{ $address['address_id'] }}" id="radio_{{ $address['address_id'] }}" class="hookbox" />
                <label class="ui-radio-label">
                    <div class="name">{{ $address['consignee'] }}</div>
                    <div class="tel">{{ $address['mobile'] }}</div>
                    <div class="address">&nbsp; {{ $address['region'] }} &nbsp; {{ $address['address'] }}</div>
                </label>
                <div class="op-btns">
                    
@if($user_id > 0)

                        <a href="javascript:void(0);" class="ftx-05 del-consignee" data-dialog="edit_address" data-id="{{ $address['address_id'] }}">{{ $lang['edit'] }}</a>
                        <a href="javascript:void(0);" class="ftx-05 del-consignee" data-dialog="del_address" data-id="{{ $address['address_id'] }}" >{{ $lang['drop'] }}</a>
                    
@else

                        <a href="javascript:void(0);" class="ftx-05 del-consignee" data-dialog="edit_address">{{ $lang['edit'] }}</a>
                    
@endif

                </div>
            </li>
            
@endforeach

        </ul>
    </div>
</div>
<div class="address-btns">
    <input id="addNewAddress" class="btn-normal" type="button" value="{{ $lang['add_address_zc'] }}">
    
@if($consignee['address'])

    <input id="confirmAddress" class="btn-normal" type="button" value="{{ $lang['confirm_address_zc'] }}">
    
@endif

</div>

