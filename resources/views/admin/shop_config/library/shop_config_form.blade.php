@foreach($group['vars'] as $key => $var)

<div class="item {{ $var['code'] }}"  data-val="{{ $var['id'] }}">
    <div class="label">{{ $var['name'] }}：</div>

    @if(isset($var['type']) && $var['type'] == 'text')
    <div class="label_value">
        <input type="text" name="value[{{ $var['id'] }}]" class="text {{ $var['code'] }}" value="{{ $var['value'] }}" autocomplete="off" />
        <div class="form_prompt"></div>
        @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
    </div>

    @elseif(isset($var['type']) && $var['type'] == 'number')
    <div class="label_value">
        <input type="number" min="0" name="value[{{ $var['id'] }}]" class="text {{ $var['code'] }}" value="{{ $var['value'] }}" autocomplete="off" />
        <div class="form_prompt"></div>
        @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
    </div>

    @elseif(isset($var['type']) && $var['type'] == 'password')

    <div class="label_value">
        <input type="password" style="display:none" autocomplete="off" />
        <input type="password" name="value[{{ $var['id'] }}]" class="text" value="{{ $var['value'] }}" autocomplete="off" />
        <div class="form_prompt"></div>
        @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
    </div>

    @elseif(isset($var['type']) && $var['type'] == 'textarea')
    <div class="label_value">
        <textarea class="textarea" name="value[{{ $var['id'] }}]" id="role_describe">{{ $var['value'] }}</textarea>
        <div class="form_prompt"></div>
        @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
    </div>

    @elseif(isset($var['type']) && $var['type'] == 'select')

    <div class="label_value">
        <div class="checkbox_items">
            @foreach($var['store_options'] as $k => $opt)
            
            <div class="checkbox_item">
                <input type="radio" name="value[{{ $var['id'] }}]" class="ui-radio evnet_{{ $var['code'] }}" id="value_{{ $var['id'] }}_{{ $k }}" value="{{ $opt }}"
               
                @if(isset($var['value']) && $var['value'] == $opt) checked="true" @endif
                       
                    @if(isset($var['code']) && $var['code'] == 'smtp_ssl' && $opt == 1)
                    onclick="return confirm('{{ __('admin::shop_config.smtp_ssl_confirm') }}');"
                    @endif

                    @if(isset($var['code']) && $var['code'] == 'enable_gzip' && $opt == 1)
                    onclick="return confirm('{{ __('admin::shop_config.gzip_confirm') }}');"
                    @endif

                    @if(isset($var['code']) && $var['code'] == 'retain_original_img' && $opt == 0)
                
                    onclick="return confirm('{{ __('admin::shop_config.retain_original_confirm') }}');"
                    @endif
                
                />
                <label for="value_{{ $var['id'] }}_{{ $k }}" class="ui-radio-label">{{ $var['display_options'][$k] }}</label>
                
                @if(isset($var['code']) && $var['code'] == 'price_style')
                    @if($opt == 1)
                    <div class="price_style_item price_style_1">
                        <div class="price">¥6999.00</div>
                        <div class="price_style_desc">
                            <span>{{ __('admin::shop_config.universal_amount_style') }}</span>
                        </div>
                    </div>
                    @elseif($opt == 2)
                    <div class="price_style_item">
                        <div class="price">¥6999.00</div>
                        <div class="price_style_desc">
                            <span>{{ __('admin::shop_config.tianmao_amount_style') }}</span>
                            <span>{{ __('admin::shop_config.whole_bold') }}</span>
                            <span>{{ __('admin::shop_config.highlight_price') }}</span>
                        </div>
                    </div>
                    @elseif($opt == 3)
                    <div class="price_style_item">
                        <div class="price"><em>¥</em>6999.<em>00</em></div>
                        <div class="price_style_desc">
                            <span>{{ __('admin::shop_config.jingdong_amount_style') }}</span>
                            <span>{{ __('admin::shop_config.part_of_the_bold') }}</span>
                            <span>{{ __('admin::shop_config.weaken_currency_symbol_decimal') }}</span>
                        </div>
                    </div>
                    @elseif($opt == 4)
                    <div class="price_style_item">
                        <div class="price"><em>¥</em>6999.00</div>
                        <div class="price_style_desc">
                            <span>{{ __('admin::shop_config.pindd_amount_style') }}</span>
                            <span>{{ __('admin::shop_config.amount_part_of_the_bold') }}</span>
                            <span>{{ __('admin::shop_config.weaken_currency_symbol') }}</span>
                        </div>
                    </div>
                     @endif
                @endif
            </div>
            @endforeach
        </div>
        <div class="form_prompt"></div>
        @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
    </div>

    @elseif(isset($var['type']) && $var['type'] == 'options')
    <div class="label_value">
        <div id="select{{ $var['id'] }}_{{ $k }}" class="imitate_select select_w320">
            <div class="cite">{{ __('admin/common.please_select') }}</div>
            <ul>
                @foreach(__('admin::shop_config.cfg_range.' . $var['code']) as $k => $options)
                <li><a href="javascript:;" data-value="{{ $k }}" class="ftx-01">{{ $options }}</a></li>
                @endforeach
            </ul>
            <input name="value[{{ $var['id'] }}]" type="hidden" value="{{ $var['value'] }}" id="{{ $var['id'] }}_{{ $k }}_val">
        </div>
        <div class="form_prompt"></div>
        @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
    </div>

    @elseif(isset($var['type']) && $var['type'] == 'file')
    <div class="label_value">
        <div class="type-file-box">
            <input type="button" name="button" id="button" class="type-file-button" value="" />
            <input type="file" class="type-file-file"  name="{{ $var['code'] }}" size="30" data-state="imgfile" hidefocus="true" value="" />
            @if(!empty($var['value']))
            <span class="show">
                <a href="{{ $var['value'] }}" target="_blank" class="nyroModal"><i class="icon icon-picture" data-tooltipimg="{{ $var['value'] }}" ectype="tooltip" title="tooltip"></i></a>
            </span>
            @endif
            <input type="text" class="type-file-text" id="textfield" readonly />
        </div>
        @if(!empty($var['del_img']))
        <a href="{{ url(ADMIN_PATH . '/shop_config.php?act=del&code=' . $var['code'] . '&callback=' . urlencode(request()->getRequestUri())) }}" class="btn red_btn h30 mr10 fl" style="line-height:30px;">{{ __('admin::common.drop') }}</a>
        @else
            @if(!empty($var['value']))
            <img src="{{ asset('/assets/admin/images/yes.gif') }}" alt="yes" class="fl mt10" />
            @else
            <img src="{{ asset('/assets/admin/images/no.gif') }}" alt="no" class="fl mt10" />
            @endif
        @endif
        <div class="form_prompt"></div>
        @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
    </div>

    @elseif(isset($var['type']) && $var['type'] == 'manual')

        @if(isset($var['code']) && $var['code'] == 'shop_country')
        <div class="ui-dropdown smartdropdown alien">
            <input type="hidden" value="{{ config('shop.shop_country') }}" name="value[{{ $var['id'] }}]" id="selcountry">
            <div class="txt">{{ __('admin::shop_config.country') }}</div>
            <i class="down u-dropdown-icon"></i>
            <div class="options clearfix" style="max-height:300px;">
                @foreach($countries as $list)
                <span class="liv" data-text="{{ $list['region_name'] }}" data-type="1" data-value="{{ $list['region_id'] }}">{{ $list['region_name'] }}</span>
                @endforeach
            </div>
        </div>

        @elseif(isset($var['code']) && $var['code'] == 'shop_province')
        <div class="ui-dropdown smartdropdown alien">
            <input type="hidden" value="{{ config('shop.shop_province') }}" name="value[{{ $var['id'] }}]" id="selProvinces">
            <div class="txt">{{ __('admin::shop_config.province_alt') }}</div>
            <i class="down u-dropdown-icon"></i>
            <div class="options clearfix" style="max-height:300px;">
                @foreach($provinces as $list)
                <span class="liv" data-text="{{ $list['region_name'] }}" data-type="2" data-value="{{ $list['region_id'] }}">{{ $list['region_name'] }}</span>
                @endforeach
            </div>
        </div>

        @elseif(isset($var['code']) && $var['code'] == 'shop_city')
        <div id="dlCity" class="ui-dropdown smartdropdown alien">
            <input type="hidden" value="{{ config('shop.shop_city') }}" name="value[{{ $var['id'] }}]" id="selCities">
            <div class="txt">{{ __('admin::shop_config.city') }}</div>
            <i class="down u-dropdown-icon"></i>
            <div class="options clearfix" style="max-height:300px;">
                <span class="liv hide" data-text="{{ __('admin::shop_config.city') }}" data-value="0">{{ __('admin::shop_config.city') }}</span>
                @foreach($cities as $list)
                <span class="liv" data-text="{{ $list['region_name'] }}" data-type="3" data-value="{{ $list['region_id'] }}">{{ $list['region_name'] }}</span>
                @endforeach
            </div>
        </div>

        @elseif(isset($var['code']) && $var['code'] == 'shop_district')
        <div id="dlRegion" class="ui-dropdown smartdropdown alien">
            <input type="hidden" value="{{ config('shop.shop_district') }}" name="value[{{ $var['id'] }}]" id="selDistricts">
            <div class="txt">{{ __('admin::shop_config.area_alt') }}</div>
            <i class="down u-dropdown-icon"></i>
            <div class="options clearfix" style="max-height:300px;">
                @foreach($districts as $list)
                <span class="liv" data-text="{{ $list['region_name'] }}" data-type="4"  data-value="{{ $list['region_id'] }}">{{ $list['region_name'] }}</span>
                @endforeach
            </div>
        </div>

        @elseif(isset($var['code']) && $var['code'] == 'lang')
        <div class="label_value">
            <div id="select{{ $var['id'] }}_{{ $k }}" class="imitate_select select_w320" >
                <div class="cite">{{ __('admin/common.please_select') }}</div>
                <ul>
                    @foreach($lang_list as $key => $options)
                    <li><a href="javascript:;" data-value="{{ $options }}" class="ftx-01">{{ $options }}</a></li>
                    @endforeach
                </ul>
                <input name="value[{{ $var['id'] }}]" type="hidden" value="{{ $var['value'] }}" id="{{ $var['id'] }}_{{ $k }}_val">
            </div>
            <div class="form_prompt"></div>
            @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
        </div>

        @elseif(isset($var['code']) && $var['code'] == 'invoice_type')
        <div class="label_value">
            <table>
                <tr>
                    <td colspan="2">
                        <div id="consumtable">
                            <p>
                                <label class="fl mr10">{{ __('admin::shop_config.invoice_type') }}</label>
                                <input type="text" class="text w120" name="invoice_type[]" size="10" autocomplete="off"/>
                                <label class="fl mr10">{{ __('admin::shop_config.invoice_rate') }}</label>
                                <input type="text" class="text w120" name="invoice_rate[]" size="10" />
                                <input type="button" onclick="addCon_amount(this)" class="button fl" value="{{ __('admin::shop_config.add') }}" autocomplete="off"/>
                                <span class="form_prompt ml10 fl"></span>
                            </p>

                            @if(!empty($invoice_list))
                            @foreach($invoice_list as $invoice)
                                 @if(!empty($invoice['type']))
                                <p class="mt10">
                                    <label class="fl mr10">{{ __('admin::shop_config.invoice_type') }}</label>
                                    <input type="text" name="invoice_type[]" value="{{ $invoice['type'] }}" class="text w120" size="10" autocomplete="off"/>
                                    <label class="fl mr10">{{ __('admin::shop_config.invoice_rate') }}</label>
                                    <input type="text" name="invoice_rate[]" value="{{ $invoice['rate'] ?? 0 }}" size="10" class="text w120" autocomplete="off"/>
                                    <a href='javascript:;' class='removeV' onclick='removeCon_amount(this)'><img src='{{ asset('/assets/admin/images/no.gif') }}' title='{{ __('admin::common.drop') }}'></a>
                                </p>
                                @endif
                            @endforeach
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
            <div class="form_prompt"></div>
            @if(!empty($var['desc']))<div class="notic">{!! $var['desc'] ?? '' !!}</div>@endif
        </div>
        @endif

    @endif
</div>

@endforeach


