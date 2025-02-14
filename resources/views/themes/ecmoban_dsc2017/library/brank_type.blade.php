

@if($brandView == '' && $brandView != 'add_brand' && $brandView != 'brandView')

<div class="panel-body mt40">
    <div class="panel-tit"><span><em class="red">*</em>{{ $lang['add_brand_info'] }}</span></div>
    <div class="cue">{!! $title['titles_annotation'] !!}</div>
    <div class="list">
    	<div class="item">
            <div class="brank_list">
            	<table class="table mt10">
                    <thead>
                        <tr>
                            <th width="80">{{ $lang['Serial_number'] }}</th>
                            <th width="180">{{ $lang['brand_zh'] }}</th>
                            <th width="180">{{ $lang['brand_us'] }}</th>
                            <th width="80">{{ $lang['brand_letter'] }}</th>
                            <th width="130">{{ $lang['brand_logo'] }}</th>
                            <th width="110">{{ $lang['brand_type'] }}</th>
                            <th width="110">{{ $lang['Management_type'] }}</th>
                            <th width="80">{{ $lang['handle'] }}</th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($title['brand_list'] as $key => $brand)

                         <tr id="brand_{{ $brand['bid'] }}">
                            <td>{{ $key }}</td>
                            <td>{{ $brand['brandName'] }}</td>
                            <td>{{ $brand['bank_name_letter'] }}</td>
                            <td>{{ $brand['brandFirstChar'] }}</td>
                            <td align="center">
@if($brand['brandLogo'] != '')
<a href="{{ $brand['brandLogo'] }}" target="_blank">{{ $lang['view'] }}</a>
@endif
</td>
                            <td>
@if($brand['brandType'] == 1)
{{ $lang['brand_domestic'] }}
@elseif ($brand['brandType'] == 2)
{{ $lang['brand_international'] }}
@endif
</td>
                            <td>
@if($brand['brand_operateType'] == 1)
{{ $lang['brand_self'] }}
@elseif ($brand['brand_operateType'] == 2)
{{ $lang['brand_agent'] }}
@endif
</td>
                            <td>
                                <a href="merchants_steps.php?step={{ $step }}&pid_key={{ $b_pidKey }}&ec_shop_bid={{ $brand['bid'] }}&brandView=brandView" class="link-blue">
                                    <span>{{ $lang['modify'] }}</span>
                                </a>&nbsp;
                                <a href="javascript:get_deleteBrand({{ $brand['bid'] }});" class="link-blue">
                                    <span>{{ $lang['drop'] }}</span>
                                </a>
                            </td>
                        </tr>

@endforeach

                     </tbody>
                </table>
            </div>
           	<a id="saveBrandQualificationBtn" class="sc-btn sc-orgBg-btn btn30 mr0" href="merchants_steps.php?step={{ $step }}&pid_key={{ $brandKey ?? 2 }}&brandView=add_brand">{{ $lang['07_brand_add'] }}</a>
            <input type="hidden" name="title_brand_list" id="title_brand_list" value="
@if($title['brand_list'])
1
@else
0
@endif
">
		</div>
    </div>
</div>

@elseif ($brandView == 'add_brand')

<div class="panel-body mt40">
	<div class="list addBrank">
    	<div class="item">
        	<div class="label"><em>*</em> {{ $lang['input_brand_zh'] }}：</div>
            <div class="value">
            	<div class="bsDiv">
                    <input type="text" value="" class="text text-4" name="searchBrandZhInput" id="searchBrandZhInput" ectype="bsKeyup" data-type="0" autoComplete="off">
                    <div class="bsBox" id="searchBrand_name"></div>
                </div>
            </div>
        </div>
        <div class="item">
        	<div class="label">{{ $lang['brand_us_input'] }}：</div>
            <div class="value">
            	<div class="bsDiv">
                    <input type="text" value="" name="searchBrandEnInput" class="text text-4" id="searchBrandEnInput" ectype="bsKeyup" data-type="1" autoComplete="off">
                    <div class="bsBox" id="searchBrand_letter"></div>
                </div>
            </div>
        </div>
        <div class="item">
        	<div class="label">&nbsp;</div>
            <div class="value">
            	<input type="button" onClick="searchBrand_submit();" value="{{ $lang['brand_Library_Search'] }}" class="sc-btn sc-orgBg-btn btn35" >
            </div>
        </div>
	</div>
    <input type="hidden" name="search_brandType" value="" />
    <input type="hidden" name="brandId" value="" />
    <input type="hidden" name="btype" value="" />
</div>

@elseif ($brandView == 'brandView')

<div class="panel-body mt30">
    <div class="panel-tit"><span>
@if($ec_shop_bid == 0)
{{ $title['fields_titles'] }}
@else
{{ $lang['edit_brand'] }}
@endif
</span></div>
    <div class="mc">
    	<div class="list">
            <div class="item">
                <div class="label">
                	<em>*</em>
                	<span>{{ $lang['brand_name'] }}：</span>
                </div>
                <div class="value {{ $brandId }}">
                	<input type="text" name="ec_brandName" id="brandName" size="20" value="
@if($title['parentType']['brandName'])
{{ $title['parentType']['brandName'] }}
@else
{{ $brand_name }}
@endif
" class="text"
@if($brandId)
readonly
@endif
>
                	<label class="error" id="brandNameHTML"></label>
                </div>
            </div>
            <div class="item">
                <div class="label">
                	<em>*</em>
                	<span>{{ $lang['Letter_name'] }}：</span>
                </div>
                <div class="value">
                	<input type="text" name="ec_bank_name_letter" id="bank_name_letter" size="20" value="
@if($title['parentType']['bank_name_letter'])
{{ $title['parentType']['bank_name_letter'] }}
@else
{{ $brand_letter }}
@endif
" class="text">
                	<label class="error" id="letterHTML"></label>
                </div>
            </div>
            <div class="item">
                <div class="label">
                	<em>*</em>
                	<span>{{ $lang['brand_letter'] }}：</span>
                </div>
                <div class="value">
                	<input type="text" name="ec_brandFirstChar" id="brandFirstChar" value="
@if($title['parentType']['brandFirstChar'])
{{ $title['parentType']['brandFirstChar'] }}
@else
{{ $ec_brandFirstChar }}
@endif
" class="text required" maxlength="1">
                	<label class="error" id="brandFirstCharHTML"></label>
                </div>
            </div>
            <div class="item">
                <div class="label">
                	<em>*</em>
                	<span>{{ $lang['brand_logo'] }}：</span>
                </div>
                <div class="value">
                	<div class="type-file-box">
                        <input type="button" name="button" class="type-file-button" id="button" value="" />
                        <input type="file" name="ec_brandLogo" class="type-file-file" value="{{ $title['parentType']['brandLogo'] }}" data-state="img" hidefocus="true" />

@if($title['parentType']['brandLogo'])
<a href="{{ $title['parentType']['brandLogo'] }}" class="chakan" target="_blank">{{ $lang['view'] }}</a>
@endif

                        <input type="text" name="textfile" class="type-file-text" style="width:150px;" value="{{ $title['parentType']['brandLogo'] }}" readonly />
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="label">
                	<em>*</em>
                	<span>{{ $lang['brand_type'] }}：</span>
                </div>
                <div class="value">
                	<div class="imitate_select w120 shop_categoryMain" id="brandType">
                        <div class="cite"><span>{{ $lang['Please_select'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li><a href="javascript:void(0);" data-value="0">{{ $lang['Please_select'] }}</a></li>
                            <li><a href="javascript:void(0);" data-value="1">{{ $lang['brand_domestic'] }}</a></li>
                            <li><a href="javascript:void(0);" data-value="2">{{ $lang['brand_international'] }}</a></li>
                        </ul>
                        <input type="hidden" name="ec_brandType" value="
@if($title['parentType']['brandType'] == 0)
0
@elseif ($title['parentType']['brandType'] == 1)
1
@else
2
@endif
" id="brandType_val" />
                    </div>
                    <label class="error" id="brandTypeHTML"></label>
                </div>
            </div>
            <div class="item">
                <div class="label">
                	<em>*</em>
                	<span>{{ $lang['Management_type'] }}：</span>
                </div>
                <div class="value">
                	<div class="imitate_select w120 shop_categoryMain" id="brand_operateType">
                        <div class="cite"><span>{{ $lang['Please_select'] }}</span><i class="iconfont icon-down"></i></div>
                        <ul>
                            <li><a href="javascript:void(0);" data-value="0">{{ $lang['Please_select'] }}</a></li>
                            <li><a href="javascript:void(0);" data-value="1">{{ $lang['brand_self'] }}</a></li>
                            <li><a href="javascript:void(0);" data-value="2">{{ $lang['brand_agent'] }}</a></li>
                        </ul>
                        <input type="hidden" name="ec_brand_operateType" value="
@if($title['parentType']['brand_operateType'] == 0)
0
@elseif ($title['parentType']['brand_operateType'] == 1)
1
@else
2
@endif
" id="brand_operateType_val" />
                    </div>
                    <label class="error" id="operateTypeHTML"></label>
                </div>
            </div>
            <div class="item">
                <div class="label">
                	<em>*</em>
                	<span>{{ $lang['brand_use_period'] }}：</span>
                </div>
                <div class="value">
                	<input type="text" name="ec_brandEndTime" value="{{ $title['parentType']['brandEndTime'] }}" readonly size="20" class="text text-2 jdate narrow fl" style=" float:left;" id="ec_brandEndTime">
                    <div class="cart-checkbox fl ml10">
                 		<input type="checkbox" class="ui-checkbox CheckBoxShop" onclick="get_shopTime_term(this)" name="ec_brandEndTime_permanent" value="1" id="brandEndTime_permanent"
@if($title['parentType']['brandEndTime_permanent'])
checked
@endif
>
                        <label for="brandEndTime_permanent">{{ $lang['permanent'] }}</label>
                    </div>
                    <input name="ec_shop_bid" type="hidden" value="{{ $ec_shop_bid }}">
                    <label class="error" id="brandEndTimeHTML"></label>
                </div>
            </div>
        	@include('frontend::library/cententFields')
        </div>
        <div class="view-sample" style="display:none">
            <div class="img-wrap">
                <img width="180" height="180" alt="" src="/storage/images/common/images/ruzhu/x_1.jpg">
            </div>
            <div class="t-c mt10">
                <a class="link-blue" target="_blank" href="/storage/images/common/images/ruzhu/1.jpg">{{ $lang['View_larger'] }}</a>
            </div>
        </div>
    </div>
</div>
<div class="panel-body mt40">
    <div class="panel-tit"><span>{{ $lang['Please_file'] }}</span></div>
	<div class="cue">{!! $lang['Please_file_one'] !!}</div>
    <div class="mc" name='brandId[]' id="parentNode_dateTimeDiv">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" id="brand-table" class="table zizhiTable">
            <thead>
            	<tr>
                	<th width="350">{{ $lang['zizhi_name'] }}</th>
                    <th width="350">{{ $lang['zizhi_Electronic'] }}</th>
                    <th width="300">{{ $lang['Due_date'] }}</th>
                    <th width="110">{{ $lang['handle'] }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="qualTemplet">
                    <td><input type="text" name="ec_qualificationNameInput[]" size="35" value="" class="text"></td>
                    <td>
                        <div class="type-file-box">
                            <input type="button" name="button" class="type-file-button" id="button" value="" />
                            <input type="file" name="ec_qualificationImg[]" class="type-file-file" value="" data-state="" hidefocus="true" />
                            <input type="text" name="textfile" class="type-file-text" style="width:150px;" value="" readonly />
                        </div>
                    </td>
                    <td>
                        <input type="text" name="ec_expiredDateInput[]" value="" readonly size="20" class="text text-2 jdate narrow dateTime fl" id="expiredDateInput">
                        <div class="cart-checkbox fl" style=" margin-top:5px;">
                            <input type="checkbox" class="ui-checkbox CheckBoxShop" onclick="get_shopTime_term(this)" name="ec_expiredDate_permanent[]" value="1" id="expiredDate_permanent">
                            <label for="expiredDate_permanent">{{ $lang['permanent'] }}</label>
                        </div>
                        <input name="b_fid[]" type="hidden" value="">
                    </td>
                    <td><a onclick="addBrandTable(this)" href="javascript:;">[+]</a></td>
                </tr>

@if($ec_shop_bid > 0)


@if($title['brandfile_list'])


@foreach($title['brandfile_list'] as $key => $brandfile)

                <tr>
                    <td><input type="text" name="ec_qualificationNameInput[]" size="35" value="{{ $brandfile['qualificationNameInput'] }}" class="text"></td>
                    <td>
                    <div class="type-file-box">
                        <input type="button" name="button" class="type-file-button" id="button" value="" />
                        <input type="file" name="ec_qualificationImg[]" class="type-file-file" value="{{ $brandfile['qualificationImg'] }}" data-state="" hidefocus="true" />

@if($brandfile['qualificationImg'])
<a href="{{ $brandfile['qualificationImg'] }}" class="chakan" target="_blank">{{ $lang['view'] }}</a>
@endif

                        <input type="text" name="textfile" class="type-file-text" style="width:150px;" value="{{ $brandfile['qualificationImg'] }}" readonly />
                    </div>
                    </td>
                    <td>
                    	<input type="text" name="ec_expiredDateInput[]" value="{{ $brandfile['expiredDateInput'] }}" readonly size="20" class="text text-2 jdate narrow dateTime fl" id="expiredDateInput_{{ $brandfile['b_fid'] }}">
                        <div class="cart-checkbox fl" style=" margin-top:5px;">
                            <input type="checkbox" class="ui-checkbox CheckBoxShop" onclick="get_shopTime_term(this)" name="ec_expiredDate_permanent[]" value="1" id="expiredDate_permanent_{{ $brandfile['b_fid'] }}"
@if($brandfile['expiredDate_permanent'] == 1)
checked
@endif
>
                            <label for="expiredDate_permanent_{{ $brandfile['b_fid'] }}">{{ $lang['permanent'] }}</label>
                        </div>
                        <input name="b_fid[]" type="hidden" value="{{ $brandfile['b_fid'] }}">
                    </td>
                    <td><a onclick="removeBrandTable(this,{{ $brandfile['b_fid'] }})" href="javascript:;">[-]</a></td>
                </tr>
                <script type="text/javascript">
					var opts_{{ $brandfile['b_fid'] }} = {
						'targetId':'expiredDateInput_{{ $brandfile['b_fid'] }}',
						'triggerId':['expiredDateInput_{{ $brandfile['b_fid'] }}'],
						'alignId':'expiredDateInput_{{ $brandfile['b_fid'] }}',
						'hms':'off',
						'format':'-'
					}
					xvDate(opts_{{ $brandfile['b_fid'] }});
				</script>

@endforeach


@endif


@endif

            </tbody>
        </table>
    </div>
</div>
<div class="prompt">
	<div class="yel-tip">
        {!! $lang['brand_zizhi_require'] !!}
	</div>
</div>

@endif

<script type="text/javascript">
$(function(){
	//搜索中英文品牌keyup
	$("*[ectype='bsKeyup']").keyup(function(){
		var val = $(this).val(),
			type = $(this).data('type');

		Ajax.call('merchants_steps.php', 'step=brandSearch_cn_en&value=' + val + '&type=' + type,function(res){
			if(res.err_no){
				if(res.type == 1){
					$('#searchBrand_letter').show().html(res.content);
				}else{
					$('#searchBrand_name').show().html(res.content);
				}
			}else{
				$("input[name='search_brandType']").val('');
				$("input[name='brandId']").val('');
			}

			$("input[name='btype']").val(res.type);
		}, 'POST', 'JSON');
	});

	//点击空白处模糊搜索层隐藏
	$(document).click(function(){
		$('#searchBrand_name').hide();
		$('#searchBrand_letter').hide();
	});

	//中英文品牌模糊搜索层点击
	$(document).on("click","li.brandId",function(ev){
		var brand_id = $(this).attr('id');
		var rev = $(this).attr('rev');

		Ajax.call('merchants_steps.php', 'step=brandSearch_info&brand_id=' + brand_id + '&brand_type=' + rev,function(res){
			$('#searchBrandZhInput').val(res.brand_name);
			$('#searchBrandEnInput').val(res.brand_letter);
			$("input[name='brandId']").val(res.brand_id);
			$("input[name='search_brandType']").val(res.brand_type);
		}, 'POST', 'JSON');
	});
});

//提交搜索品牌
function searchBrand_submit(){
	var searchBrandZhInput = $("input[name='searchBrandZhInput']").val();
	var searchBrandEnInput = $("input[name='searchBrandEnInput']").val();

	var rev = $("input[name='search_brandType']").val();
	var brand_id = $("input[name='brandId']").val();
	var brand_submit = 'submit';

	if(searchBrandZhInput != ''){
		Ajax.call('merchants_steps.php', 'step=brandSearch_info&brand_id=' + brand_id + '&brand_type=' + rev + '&submit=' + brand_submit + '&searchBrandZhInput=' + searchBrandZhInput + '&searchBrandEnInput=' + searchBrandEnInput, function(res){
			var str = "",
				state = 0,
				cBtn = true;

			if(res.err_no){
				str = "{{ $lang['Apply_brand'] }}"+res.brand_name+"(" + res.brand_letter + "){{ $lang['brand_in'] }}";
				state = 1;
				cBtn = false;
			}else{
				var zhInput = $("input[name='searchBrandZhInput']").val();
				var enInput = $("input[name='searchBrandEnInput']").val();

				if(enInput != ''){
					enInput = "(" + enInput + ")";
				}
				str = "{{ $lang['Apply_brand'] }}<em class='red'> "+ zhInput + enInput + "</em> {{ $lang['Apply_brand_two'] }}";
			}

			pbDialog(str,"",state,'','',50,cBtn,function(){
				$("form[name='stepForm']").submit();
			},"{{ $lang['submit_Qual_brand'] }}","{{ $lang['search_again'] }}");

		}, 'POST', 'JSON');
	}else{
		pbDialog(json_languages.brand_zh_null,"",0);
		return false;
	}
}


@if($brandView == 'brandView')

//日期选择
var opts5 = {
	'targetId':'ec_brandEndTime',
	'triggerId':['ec_brandEndTime'],
	'alignId':'ec_brandEndTime',
	'hms':'off',
	'format':'-'
},opts6 = {
	'targetId':'expiredDateInput',
	'triggerId':['expiredDateInput'],
	'alignId':'expiredDateInput',
	'hms':'off',
	'format':'-'
}
xvDate(opts5);
xvDate(opts6);

@endif

</script>
