

@if($fittings)

<div class="title">{{ $lang['Package_flow_desc'] }}</div>
<div class="tm-combo-content">

@foreach($fittings as $key => $goods_list)

    <form action="javascript:void(0);" method="post" name="ECS_FORMBUY_{{ $goods_list['goods_id'] }}" id="ECS_FORMBUY_{{ $goods_list['goods_id'] }}" >
    <div
@if($key % 2 == 0)
 style="clear:left;"
@else
 style="clear:none;"
@endif
 class="tm-combo-item
@if($goods_list['goods_number'] == 0)
 hover
@endif
" id="tm-combo-item_{{ $goods_list['goods_id'] }}">
        <div class="tm-img">
            <a href="{{ $goods_list['url'] }}" title="{{ $goods_list['short_name'] }}" target="_blank" class="combo_goods_{{ $goods_list['goods_id'] }}"><img src="{{ $goods_list['goods_thumb'] }}" width="60" height="60"></a>
        </div>
        <div class="tm-meta" rev="{{ $goods_list['goods_id'] }}">
        	<dl class="tb-stock tm-clear">
                <dt class="tm-metatit">{{ $lang['goods_name'] }}：</dt>
                <dd><span class="tm-goods-name">{{ $goods_list['short_name'] }}</span></dd>
            </dl>

@foreach($goods_list['properties']['spe'] as $spec_key => $spec)


@if($spec['name'])

            <dl class="tb-prop tm-clear fitt_input">
                <dt class="tm-metatit">{{ $spec['name'] }}：</dt>
                <dd>
                    <ul>

@if($spec['is_checked'] > 0)


@foreach($spec['values'] as $val_key => $value)

                        <li
@if($value['combo_checked'] == 1)
 class="selected"
@endif
 rev="{{ $value['id'] }}" onclick="fitt_changeAtt(this, {{ $goods_list['goods_id'] }}, '{{ $group_rev }}',
@if($key == 0)
1
@else
0
@endif
, $('#fittings_goods').val());">
                           <b></b>
                           <a href="javascript:void(0);">

@if($value['img_flie'])

                            <img width="24" height="24" src="{{ $value['img_flie'] }}" />

@endif

                            <i>{{ $value['label'] }}</i>
                            <input id="fitt_spec_value_{{ $value['id'] }}" type="radio" name="fitt_spec_{{ $spec_key }}" class="fitt_spec_value" value="{{ $value['id'] }}"
@if($value['combo_checked'] == 1)
checked="checked"
@endif
 />
                            </a>
                        </li>

@endforeach


@else


@foreach($spec['values'] as $val_key => $value)

                        <li
@if($value['combo_checked'] == 1)
class="selected"
@endif
 onclick="fitt_changeAtt(this, {{ $goods_list['goods_id'] }}, '{{ $group_rev }}',
@if($key == 0)
1
@else
0
@endif
, $('#fittings_goods').val());">
                            <b></b>
                            <a href="javascript:void(0);">
                                <i>{{ $value['label'] }}</i>
                                <input id="fitt_spec_value_{{ $value['id'] }}" type="radio" name="fitt_spec_{{ $spec_key }}" class="fitt_spec_value" value="{{ $value['id'] }}"
@if($value['combo_checked'] == 1)
checked="checked"
@endif
 />
                            </a>
                        </li>

@endforeach


@endif

                    </ul>
                </dd>
            </dl>

@endif


@endforeach

            <dl class="tb-stock tm-clear">
                <dt class="tm-metatit">{{ $lang['goods_inventory'] }}：</dt>
                <dd>
                    <span class="tm-stock_{{ $goods_list['goods_id'] }}">{{ $goods_list['goods_number'] }}</span>
                    <span class="tm-stock_title_{{ $goods_list['goods_id'] }}" style="display:none;"><font style="color:#F00;">({{ $lang['goods_null'] }})</font></span>
                </dd>
            </dl>
        </div>
        <div class="tm-notice">{{ $lang['goods_info_select'] }}</div>
        <input name="fitt_jq_{{ $goods_list['goods_id'] }}" class="fitt_jq_{{ $goods_list['goods_id'] }}" value="" type="hidden">
    </div>
    @csrf </form>

@endforeach

</div>

@else

<div class="notic">{{ $lang['select_combination'] }}</div>

@endif
