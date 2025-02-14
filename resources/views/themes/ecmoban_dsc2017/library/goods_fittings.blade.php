


@if($fittings)

<div class="combo-inner">
	<ul class="tab-nav">

@foreach($fittings_tab_index as $key => $tab_item)


@if($key == 1)

        <li class="curr">{{ $comboTab[$key] }}</li>

@else

        <li>{{ $comboTab[$key] }}</li>

@endif


@endforeach

	</ul>
	<div class="tab-content">

@foreach($fittings_tab_index as $key => $tab_item)

		<form name="m_goods_{{ $key }}" method="post" action="" onSubmit="return false;"
@if($key > 1)
 style="display:none;"
@endif
>
            <div class="tab-content-warp">
                <div class="master">
                    <div class="p-img"><img src="{{ $goods['goods_thumb'] }}" width="160" height="160"></div>
                    <div class="p-name">{{ $goods['goods_name'] }}</div>
                    <div class="p-oper">
                        <div class="dsc-enable">
                            <input type="hidden" name="stock" value="{{ $goods['group_number'] }}" />
                            <input type="checkbox" class="ui-all-checkbox" id="primary_goods" checked="checked" disabled="disabled" />
                            <label class="ui-all-label" for="primary_goods"></label>
                        </div>
                        <div class="p-price ECS_fittings_interval"></div>
                    </div>
                </div>
                <div class="combo-spliter"><i class="iconfont icon-plus"></i></div>
                <div class="combo-items">
                    <div class="combo-items-warp">
                        <ul>

@foreach($fittings as $k => $goods_list)


@if($goods_list['group_id'] == $key)

                            <li class="combo-item" id="{{ $goods_list['goods_id'] }}_{{ $key }}">
                                <div class="p-img"><a href="{{ $goods_list['url'] }}" target="_blank"><img src="{{ $goods_list['goods_thumb'] }}" width="160" height="160"></a></div>
                                <div class="p-name"><a href="{{ $goods_list['url'] }}" target="_blank" title="{{ $goods_list['goods_name'] }}">{{ $goods_list['goods_name'] }}</a></div>
                                <div class="p-oper">
                                    <div class="dsc-enable" ectype="enable">
                                        <input type="checkbox" item="m_goods_{{ $key }}" class="ui-all-checkbox m_goods_list m_goods_{{ $key }} m_goods_list_m_goods_{{ $key }}_{{ $goods_list['goods_id'] }}" ectype="checkbox" id="goods_{{ $k }}" value="{{ $goods_list['goods_id'] }}" item="m_goods_{{ $key }}" data="{{ $goods_list['fittings_price_ori'] }}" spare="{{ $goods_list['spare_price_ori'] }}" stock="{{ $goods['group_number'] }}" name="goods_list_{{ $goods_list['goods_id'] }}_{{ $key }}" />
                                        <label class="ui-all-label" for="goods_{{ $k }}" rev="{{ $goods_list['goods_id'] }}"></label>
                                    </div>
                                    <div class="p-price">{{ config('shop.currency_format', '¥') }}{{ $goods_list['fittings_price_ori'] }}</div>
                                </div>
                            </li>

@endif


@endforeach

                        </ul>
                    </div>
                    <div class="oper">
                        <a href="javascript:void(0);" class="o-prev"><i class="iconfont icon-left"></i></a>
                        <a href="javascript:void(0);" class="o-next"><i class="iconfont icon-right"></i></a>
                    </div>
                </div>
                <div class="combo-action">
                    <div class="combo-action-info">
                        <div class="combo-price"><span>{{ $lang['Set_price'] }}：</span><strong id="m_goods_{{ $key }}" name="combo_shopPrice[]"></strong></div>
                        <div class="combo-o-price"><span>{{ $lang['combo_markPrice'] }}：</span><span class="original-price" name="combo_markPrice[]" id="m_goods_reference_{{ $key }}"></span></div>
                        <div class="save-price">{{ $lang['collocation_shen'] }} <span id="m_goods_save_{{ $key }}" name="combo_savePrice[]"></span></div>
                    </div>
                    <div class="input_combo_stock">

@if($goods['group_number'] > 0)
<div id="combo_stock_number" class="gns_item">{{ $lang['limit_shop'] }}：<font id="stock_number">{{ $goods['group_number'] }}</font> {{ $lang['tao'] }}</div>
@endif

                        <div class="gns_item">
                            <span>{{ $lang['btn_buy'] }}：</span>
                            <input type="text" class="combo_stock" name="m_goods_{{ $key }}_number" id="mGoods_number" value="1" size="1" />
                            <span>{{ $lang['tao'] }}</span>
                        </div>
                    </div>
                    <div class="combo-btn">
                        <a href="javascript:void(0);" rev='m_goods_{{ $key }}_{{ $goods_id }}_{{ $region_id }}_{{ $area_id }}' class="combo-btn-buynow ncs_buy" ectype="comboBuy">{{ $lang['button_buy'] }}</a>
                    </div>
                </div>
            </div>
		@csrf </form>

@endforeach

	</div>
</div>

@endif

