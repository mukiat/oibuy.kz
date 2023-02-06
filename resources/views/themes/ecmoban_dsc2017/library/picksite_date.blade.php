
<div class="take_their" id="take_their">
    <div class="their—tab-nav">
        <ul>
            <li onclick="doSwith311Tab('311')" id="li_311_id" class="tab-nav-item tab-item-selected">{{ $lang['specified_time'] }}<b></b></li> 
            <li onclick="doSwith311Tab('411')" id="li_411_id" class="tab-nav-item hide">{{ $lang['maximum_speed'] }}<b></b></li>
        </ul>
    </div>
   	<div class="time_list"> 
       <div id="date-delivery1" class="date-delivery">
            <div class="inner"> 
                <dl class="th">
                    <dd class="date"> 
                        <span class="dt">{{ $lang['Time_slot'] }}</span>
                        
@foreach($days as $key => $day)

                            <span col="{{ $key }}" row="{{ $key }}" class="">{{ $day['date'] }}<br />{{ $day['week'] }}</span> 
                        
@endforeach

                    </dd>
                </dl> 
                <div class="data select_shipping_date">
                    
@foreach($shipping_date_list as $key => $shipping_date)

                    <ul> 
                        <li class="time">{{ $shipping_date['start_date'] }}-{{ $shipping_date['end_date'] }}</li>
                        
@foreach($shipping_date['select_day'] as $key => $day)

                            
@if($day['day'])

                                <li class="checkbox">
                                    <input type="radio" data-range="{{ $shipping_date['start_date'] }}-{{ $shipping_date['end_date'] }}" data-shippingDate="{{ $day['shipping_date'] }}" name="shipping_date" value="{{ $shipping_date['shipping_date_id'] }}" style="display:none;">{{ $lang['Optional'] }}
                                </li> 
                            
@else

                                <li data-status="{{ $key }}" class="checkbox disabled"></li> 
                            
@endif

                        
@endforeach

                    </ul> 
                    
@endforeach

                </div> 
            </div>
       </div> 
       <div class="ftx-03 mt20">
         	{{ $lang['reminder_four'] }}
       </div> 
  </div>
</div>
<script>
$(function(){
    $(document).on("click",".select_shipping_date li",function(){
		if(!$(this).hasClass("disabled")){
        	$(this).addClass("item-selected").siblings().removeClass("item-selected");
			$(this).find('input').prop("checked", true);
		}
    });
});
</script>