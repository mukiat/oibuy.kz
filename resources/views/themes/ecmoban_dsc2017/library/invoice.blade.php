
<div class="invoice-dialog" id="edit_invoice">
	<div class="tab-nav">
    	<ul class="radio-list">
        	<li
@if($invoice_type == 0)
class="item-selected"
@endif
 data-value="0">{{ $lang['need_invoice']['0'] }}<b></b></li>
            <li
@if($invoice_type == 1)
class="item-selected"
@endif
 data-value="1">{{ $lang['need_invoice']['1'] }}<b></b></li>
        </ul>
    </div>
    <div class="invoice-thickbox">
        <div class="form">
            <div class="item">
                <span class="label">{{ $lang['invoice_title'] }}：</span>
                <div class="invoice-list">
                    <div class="invoice-item selected">
                    	<span>
                            <input type="text" value="{{ $lang['personal'] }}" name="inv_payee" class="inv_payee" readonly>
                            <input name="invoice_id" type="radio" class="hide" value="0" checked>
                            <b></b>
                        </span>
                    </div>

@foreach($order_invoice as $invoice)

                    <div class='invoice-item' data-invoiceid="{{ $invoice['invoice_id'] ?? 0 }}">
                        <span>
                            <input type='text' name='inv_payee' class='inv_payee' value='{{ $invoice['inv_payee'] }}' readonly>
                            <input name='invoice_id' type='radio' class='hide' value='{{ $invoice['invoice_id'] }}'>
							<input type='hidden' value="{{ $invoice['tax_id'] }}" name="invoice_tax_{{ $invoice['invoice_id'] ?? 0 }}" ectype="taxId" />
                            <b></b>
                        </span>
                        <div class='btns'><a href='javascript:void(0);' class='ftx-05 edit-tit'>{{ $lang['edit'] }}</a><a href='javascript:void(0);' class='ftx-05 update-tit hide'>{{ $lang['Preservation'] }}</a><a href='javascript:void(0);' class='ftx-05 ml10 del-tit'>{{ $lang['drop'] }}</a></div>
                    </div>

@endforeach

                </div>
                <div class="add-invoice"><a href="javascript:void(0);" class="ftx-05 add-invoice-btn">{{ $lang['add_invoice'] }}</a></div>
            </div>
			<div class="item" ectype="tax" style=" display:none;">
            	<span class="label"><em class="red">*</em>{{ $lang['label_tax_id'] }}：</span>
                <div class="value">
                	<input type="text" name="tax_id" id="tax_id" class="text" value="">
                </div>
            </div>

@if($inv_content_list)

            <div class="item">
                <span class="label">{{ $lang['invoice_content'] }}：</span>
                <div class="radio-list">
                    <ul>

@foreach($inv_content_list as $key => $list)

                        <li
@if($key == 0)
class="item-selected"
@endif
><input type="radio" name="inv_content" value="{{ $list }}"
@if($key == 0)
checked
@endif
>{{ $list }}<b></b></li>

@endforeach

                    </ul>
                </div>
            </div>

@endif

            <div class="item">
                <div class="reminder">
                    <span>{{ $lang['invoice_desc_one'] }}</span>
                    <a href="article_cat.php?id=19" target="_blank" class="ftx-05">{{ $lang['invoice_desc_two'] }}>></a>
                </div>
            </div>
        </div>
        <div class="form" style="display:none;">

@if(isset($audit_status) && $audit_status == 0)

			<div class="iis-state-warp">
				<i class="icon icon-iis-1"></i>
				<div class="iis-state-info">
					<div class="tit">{{ $lang['iis_state_one'] }}</div>
				</div>
			</div>

@elseif (isset($audit_status) && $audit_status == 1)

			<div class="iis-state-warp">
				<i class="icon icon-iis-3"></i>
				<div class="iis-state-info">
					<div class="tit">{{ $lang['iis_state_two'] }}</div><a href="javascript:;" ectype="showVat">{{ $lang['iis_state_three'] }}</a>
				</div>
			</div>
        	<div class="steps" style="display:none;">
                <div class="step">
                	<input type="hidden" id="vatCanEdit" value="false">
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_company_name'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="company_name" readonly value="{{ $vat_info['company_name'] }}"></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_bank_account'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="bank_account" readonly value="{{ $vat_info['bank_account'] }}"></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_tax_id'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="tax_id" readonly value="{{ $vat_info['tax_id'] }}"></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_vat_name'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="consignee_name" readonly value="{{ $vat_info['consignee_name'] }}"></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_vat_phone'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="consignee_mobile_phone" readonly value="{{ $vat_info['consignee_mobile_phone'] }}"></div>
                    </div>

                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_vat_address'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="consignee_address" readonly value="{{ $vat_info['consignee_address'] }}"></div>
                    </div>
                </div>
            </div>

@elseif (isset($audit_status) && $audit_status == 2)

			<div class="iis-state-warp">
				<i class="icon icon-iis-2"></i>
				<div class="iis-state-info">
					<div class="tit">{{ $lagn['iis_state_four'] }}</div>
				</div>
			</div>

@else

			<form action="user.php" method="get" name="inv_form">
        	<div class="steps" ectype="invReturn">
            	<div class="step">
                    <div class="item">
                        <span class="label">{{ $lang['invoice_mode'] }}：</span>
                        <div class="radio-list">
                            <ul>
                                <li class="item-selected"><input type="radio" name="" value="" checked>{{ $lang['order_complete_invoice'] }}<b></b></li>
                            </ul>
                        </div>
                    </div>
                    <div class="item">
                        <span class="label">{{ $lang['invoice_content'] }}：</span>
                        <div class="radio-list">
                            <ul>
                                <li class="item-selected"><input type="radio" name="" value="" checked>{{ $lang['detailed'] }}<b></b></li>
                            </ul>
                        </div>
                    </div>
                    <ul class="invoice-status">
                        <li class="fore curr">1.{{ $lang['iis_state_tab_one'] }}</li>
                        <li class="gap curr"></li>
                        <li class="fore">2.{{ $lang['iis_state_tab_two'] }}</li>
                        <li class="gap"></li>
                        <li class="fore">3.{{ $lang['iis_state_tab_three'] }}</li>
                    </ul>
                    <div class="item" id="vat-inv-type-tips">
                        <span class="label">&nbsp;</span>
                        <div class="fl"><span class="ftx-03">{{ $lang['iis_state_tab_notic'] }}</span></div>
                    </div>
                    <div class="item">
                        <span class="label">&nbsp;</span>
                        <div class="fl">
                            <a href="javascript:;" class="sc-btn sc-redBg-btn" ectype="nextStep" data-type="1">{{ $lang['lang_crowd_next_step'] }}</a>
                        </div>
                    </div>
                </div>
                <div class="step" style="display:none;">
                	<input type="hidden" id="vatCanEdit" value="false">
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_company_name'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="company_name"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_tax_id'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="tax_id"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_company_address'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="company_address"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_company_telephone'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="company_telephone"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_bank_of_deposit'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="bank_of_deposit"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_bank_account'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="bank_account" ectype="bank_card"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label">&nbsp;</span>
                        <div class="fl">
                        	<a href="javascript:;" class="sc-btn sc-redBg-btn" ectype="nextStep" data-type="2">{{ $lang['lang_crowd_next_step'] }}</a>
                        	<a href="javascript:;" class="sc-btn" ectype="backStep" data-type="2">{{ $lang['back'] }}</a>
                        </div>
                    </div>
                </div>
                <div class="step" style="display:none;">
                	<input type="hidden" id="vatConsigneeInfo" value="">
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_vat_name'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="consignee_name"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_vat_phone'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="consignee_mobile_phone"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_vat_address'] }}：</span>
                        <div class="fl">
							<div class="form-value" ectype="regionLinkage">
								<dl class="mod-select mod-select-small" ectype="smartdropdown" id="selCountries_">
									<dt>
										<span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[0] }}</span>
										<input type="hidden" value="{{ $consignee['country'] }}" name="country">
									</dt>
									<dd ectype="layer">

@foreach($country_list as $country)

										<div class="option" data-value="{{ $country['region_id'] }}" data-text="{{ $country['region_name'] }}" ectype="ragionItem" data-type="1">{{ $country['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<dl class="mod-select mod-select-small" ectype="smartdropdown" id="selProvinces_">
									<dt>
										<span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[1] }}</span>
										<input type="hidden" value="{{ $consignee['province'] }}" name="province">
									</dt>
									<dd ectype="layer">
										<div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[1] }}</div>

@foreach($province_list as $province)

										<div class="option" data-value="{{ $province['region_id'] }}" data-text="{{ $province['region_name'] }}" data-type="2" ectype="ragionItem">{{ $province['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<dl class="mod-select mod-select-small" ectype="smartdropdown" id="selCities_">
									<dt>
										<span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[2] }}</span>
										<input type="hidden" value="{{ $consignee['city'] }}" name="city">
									</dt>
									<dd ectype="layer">
										<div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[2] }}</div>

@foreach($city_list as $city)

										<div class="option" data-value="{{ $city['region_id'] }}" data-type="3" data-text="{{ $city['region_name'] }}" ectype="ragionItem">{{ $city['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<dl class="mod-select mod-select-small" ectype="smartdropdown" id="selDistricts_" style="display:none;">
									<dt>
										<span class="txt" ectype="txt">{{ $please_select }}{{ $name_of_region[3] }}</span>
										<input type="hidden" value="{{ $consignee['district'] }}" name="district">
									</dt>
									<dd ectype="layer">
										<div class="option" data-value="0">{{ $please_select }}{{ $name_of_region[3] }}</div>

@foreach($district_list as $district)

										<div class="option" data-value="{{ $district['region_id'] }}" data-type="4" data-text="{{ $district['region_name'] }}" ectype="ragionItem">{{ $district['region_name'] }}</div>

@endforeach

									</dd>
								</dl>
								<span id="consigneeEreaNote" class="status error hide"></span>
							</div>
						</div>
                    </div>
                    <div class="item">
                    	<span class="label"><em class="red">*</em>{{ $lang['label_vat_address'] }}：</span>
                        <div class="fl"><input type="text" class="text" name="consignee_address"><div class="form_prompt"></div></div>
                    </div>
                    <div class="item">
                    	<span class="label">&nbsp;</span>
                        <div class="fl">
                        	<a href="javascript:;" class="sc-btn sc-redBg-btn" ectype="nextStep" data-type="3">{{ $lang['preservation'] }}</a>
                        	<a href="javascript:;" class="sc-btn" ectype="backStep" data-type="3">{{ $lang['back'] }}</a>
							<input type="hidden" name="action" value="flow_inv_form">
							<input type="hidden" name="user_id" value="{{ $user_id }}">
                        </div>
                    </div>
                </div>
            </div>
			@csrf </form>

@endif

        </div>
    </div>
</div>
<script>
	$.levelLink(1);
	$(document).on("click","a[ectype='showVat']",function(){
		 var prompt = $(this).parents(".iis-state-warp");
		 prompt.hide();
		 prompt.next().show();
	})
</script>
