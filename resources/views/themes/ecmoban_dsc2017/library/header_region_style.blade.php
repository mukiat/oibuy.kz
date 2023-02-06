
@if(!$is_insert)

<div class="city-choice" id="city-choice" data-ectype="dorpdown">
	<div class="dsc-choie dsc-cm" ectype="dsc-choie">
		<i class="iconfont icon-map-marker"></i>
		<span class="ui-areamini-text" data-id="1" id="headerRegionNameId"></span>
	</div>
	<div class="dorpdown-layer" ectype="dsc-choie-content">

@endif


@if($is_insert)


@if($pin_region_list)

		<div class="ui-areamini-content-wrap" id="ui-content-wrap">
			<div class="hot">

@foreach($region_list as $list)

                <a href="javascript:get_district_list({{ $list['region_id'] }}, 0);"
@if($city_top == $list['region_id'])
style="background-color:#eee; color:#f42424;"
@endif
>{{ $list['region_name'] }}</a>

@endforeach

			</div>
			<div class="search-first-letter">

@foreach($pin_region_list as $letter => $pin)

				<a href="javascript:void(0);" data-letter="{{ $letter }}">{{ $letter }}</a>

@endforeach

			</div>
			<div class="scrollBody" id="scrollBody">
				<div class="all-list" id="scrollMap">
					<ul id="ul">

@foreach($pin_region_list as $letter => $pin_region)

						<li data-id="{{ $loop->iteration }}" data-name="{{ $letter }}">
							<em>{{ $letter }}</em>
							<div class="itme-city">

@if($pin_region)


@foreach($pin_region as $region)


@if($region['is_has'])

                                        <a href="javascript:get_district_list({{ $region['region_id'] }}, 0);"
@if($city_top == $region['region_id'])
class="city_selected"
@endif
>{{ $region['region_name'] }}</a>

@else

                                        <a href="javascript:void(0);" class="is_district">{{ $region['region_name'] }}</a>

@endif


@endforeach


@endif

							</div>
						</li>

@endforeach

					</ul>
				</div>
				<div class="scrollBar" id="scrollBar">
                	<p id="city_bar"></p>
                </div>
				<input name="area_phpName" type="hidden" id="phpName" value="{{ $area_phpName }}">
			</div>
		</div>

@endif

		<script type="text/javascript">
        $(function(){
			$("#site-nav").jScroll();
        });
        </script>

@endif


@if(!$is_insert)

	</div>
</div>
<script type="text/javascript">
$(function(){

	headerRegionName();

	function headerRegionName(){

@if($is_jsonp)

	        Ajax.call('crossDomain', 'act=header_region_name', headerRegionNameResponse, 'GET', 'JSON');

@else

	        Ajax.call('ajax_dialog.php', 'act=header_region_name', headerRegionNameResponse, 'GET', 'JSON');

@endif

	}

	function headerRegionNameResponse(res){
        $('#ECS_MEMBERZONE a:first').html(res.nick_name);
		$("#headerRegionNameId").html(res.content);
	}

});
</script>


@endif

