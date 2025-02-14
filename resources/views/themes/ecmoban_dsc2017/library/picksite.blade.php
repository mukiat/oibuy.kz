
<div class="form picksite-box">
    <div class="item">
        <span class="label">{{ $lang['Selection_region'] }}：</span>
        <div class="fl">
            <select name="pickRegion" id="pickRegion_select" class="selt pickRegion_select" onchange="getPickSiteListByRegion(this)">
                <option>{{ $lang['All_region'] }}</option>
                
@foreach($district_list as $key => $item)

                <option value="{{ $item['region_id'] }}" 
@if($item['region_id'] == $district)
selected
@endif
>{{ $item['region_name'] }}</option>
                
@endforeach

            </select>
        </div>
    </div>
    <div class="picksite-list">
        <ul id="pickSiteInfo">
            
@foreach($picksite_list as $key => $item)

            <li 
@if($key == 0)
class="item-selected"
@endif
>
                <input type="radio" 
@if($key == 0)
checked="checked"
@endif
 class="ui-radio" name="picksite_radio" value="{{ $item['point_id'] }}" id="picksite_radio_{{ $item['point_id'] }}">
                <label for="picksite_radio_{{ $item['point_id'] }}" class="ui-radio-label">
                    <div class="name">{{ $item['name'] }}</div>
                    <div class="info">
                        <span class="address">{{ $lang['address'] }}：{{ $item['address'] }}</span>
                        <span class="tel">{{ $lang['phone'] }}：{{ $item['mobile'] }}</span>
                    </div>
                    <a href="help.php?id={{ $item['city'] }}#{{ $item['anchor'] }}" target="_blank" class="ftx-05 map-link">{{ $lang['Detailed_map'] }}</a>
                </label>
            </li>
            
@endforeach

        </ul>
        <div class="ztd_tishi">
            <span class="label">&nbsp;</span>
            <div class="fl">
                <div class="ftx-03 mt10">{{ $lang['flow_reminder'] }}：</div>
                <div class="ftx-03">{{ $lang['flow_reminder_one'] }}&nbsp;<a href="article.php?id=55" target="_blank" class="ftx-05">{{ $lang['flow_reminder_two'] }}</a></div>
                <div class="ftx-03">{{ $lang['flow_reminder_three'] }}</div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function getPickSiteListByRegion(obj){
        $.ajax({
             type: "POST",
             url: "ajax_flow.php?step=getPickSiteList",
             data: {id:$(obj).val()},
             dataType: "json",
             success: function(data){
				if(data.error == 1){
					pbDialog(json_languages.Parameter_error,"",0);
					return false;
				}
				 $('#pickSiteInfo').empty();
				 var html = firstChecked = '';
				 $.each(data, function(i, v){
						if(i == 0){
							firstChecked = 'checked="checked"';
							var bg_selected = 'class="item-selected"';
						}else{
							firstChecked = '';
							var bg_selected = '';
						}
						html += '<li '+bg_selected+'>'
								+'<input type="radio" '+firstChecked+' class="ui-radio" name="picksite_radio" value="'+v.point_id+'" id="picksite_radio_'+v.point_id+'">'
								+'<label for="picksite_radio_'+v.point_id+'" class="ui-radio-label">'
								+'<div class="name">'+v.name+'</div>'
								+'<div class="info">'
								+'<span class="address">{{ $lang['address'] }}：'+v.address+'</span>'
								+'<span class="tel">{{ $lang['phone'] }}：'+v.mobile+'</span>'
								+'</div>'
								+'<a href="help.php?id='+v.city+'#'+v.anchor+'" target="_blank" class="ftx-05 map-link">'+json_languages.Detailed_map+'</a>'
								+'</label>'
								+'</li>';

				 });
				 $('#pickSiteInfo').html(html);
			  }
         });
    }
	$(document).on('click',".picksite-list li",function(){
		$(this).addClass("item-selected").siblings().removeClass("item-selected");
    });
</script>
