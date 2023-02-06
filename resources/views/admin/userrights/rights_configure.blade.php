@if(isset($rights_configure) && !empty($rights_configure))

    @foreach($rights_configure as $key => $value)

        <div class="item">
            {{--配置名称--}}
            <div class="label-t">@if(isset($value['label']) && !empty($value['label'])) {{ $value['label'] }}：@else &nbsp; @endif</div>

            @if(isset($value['type']) && $value['type'] == 'text')
                {{--输入框--}}
                <div class="label_value">
                    @if(isset($value['unit_range']) && !empty($value['unit_range']))
                        {{--输入框+单位组合--}}
                        @if(is_array($value['unit_range']))

                            @if (count($value['unit_range']) == 1)
                                {{--不可选单位--}}
                                <div class="input-group w340">
                                    <input type="text" name="cfg_value[]" class="form-control" value="{{ $value['value'] ?? '' }}" autocomplete="off" placeholder="" />
                                    @foreach($value['unit_range'] as $v => $unit)
                                    <input name="cfg_unit[]" type="hidden" value="{{ $v }}" />
                                    <span class="input-group-addon">{{ $unit }}</span>
                                    @endforeach
                                </div>
                            @else
                                {{--可选单位--}}
                                <div class="input-group ">
                                    <input type="text" name="cfg_value[]" class="form-control w300" value="{{ $value['value'] ?? '' }}" autocomplete="off" placeholder="" />

                                    <select name="cfg_unit[]" class="form-control w80" style="border-left:none">
                                        @foreach($value['unit_range'] as $v => $unit)
                                            <option label="{{ $unit }}" value="{{ $v }}" @if(isset($value['unit']) && $value['unit'] == $v) selected @endif >{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                        @else

                        <div class="input-group w340">
                            <input type="text" name="cfg_value[]" class="form-control" value="{{ $value['value'] ?? '' }}" autocomplete="off" placeholder="" />
                            <input name="cfg_unit[]" type="hidden" value="{{ $value['unit_range'] ?? '' }}" />
                            <span class="input-group-addon">{{ $value['unit_range'] ?? '' }}</span>
                        </div>

                        @endif

                    @else

                        <div class="w300">
                            <input type="text" name="cfg_value[]" class="form-control" value="{{ $value['value'] ?? '' }}" autocomplete="off" placeholder="" />
                        </div>

                    @endif

                    @if(isset($value['desc']) && $value['desc'])<div class="notice">{!! $value['desc'] !!}</div>@endif

                </div>

            @elseif(isset($value['type']) && $value['type'] == 'textarea')
                {{--文本框--}}
                <div class="label_value">
                    <div class="w300">
                        <textarea name="cfg_value[]" class="form-control" rows="5" placeholder="" >{{ $value['value'] ?? '' }}</textarea>
                    </div>
                    @if(isset($value['desc']) && $value['desc'])<div class="notice">{!! $value['desc'] !!}</div>@endif

                </div>

            @elseif(isset($value['type']) && $value['type'] == 'select')
                {{--select选择框--}}
                <div class="label_value">
                    <div class="w300">
                        <select name="cfg_value[]" class="form-control {{ $value['type'] . '-'. $value['name'] }}">

                            <option value="">{{ lang('admin/common.please_select') }}</option>
                            @if(isset($value['range']))
                                @foreach($value['range'] as $k => $options)

                                    <option value="{{ $k }}" @if($value['value'] == $k) selected @endif >{{ $options }}</option>

                                @endforeach
                            @endif

                        </select>
                    </div>
                    @if(isset($value['desc']) && $value['desc'])<div class="notice">{!! $value['desc'] !!}</div>@endif

                </div>

            @elseif(isset($value['type']) && $value['type'] == 'radiobox')
                {{--radio单选框--}}
                <div class="label_value">
                    <div class="checkbox_items ">
                        @if(isset($value['range']))

                            @foreach($value['range'] as $k => $options)

                                <div class="checkbox_item">
                                    <input type="radio" name="cfg_value[{{$key}}]" class="ui-radio event_zhuangtai" id="value_{{ $value['name'] }}_{{ $k }}" value="{{ $k }}"
                                    @if(isset($value['value']) && $k == $value['value'])
                                        checked
                                    @endif
                                    >
                                    <label for="value_{{ $value['name'] }}_{{ $k }}" class="ui-radio-label

                                        @if(isset($value['value']) && $k == $value['value'])
                                            active
                                        @endif

                                        ">{{ $options }}</label>
                                </div>

                            @endforeach

                        @endif

                        @if(isset($value['desc']) && $value['desc'])<div class="notice">{!! $value['desc'] !!}</div>@endif

                    </div>

                </div>
            @endif

            <input name="cfg_label[]" type="hidden" value="{{ $value['label'] ?? '' }}" />
            <input name="cfg_name[]" type="hidden" value="{{ $value['name'] ?? '' }}" />
            <input name="cfg_type[]" type="hidden" value="{{ $value['type'] ?? '' }}" />
            <input name="cfg_lang[]" type="hidden" value="{{ $value['lang'] ?? '' }}" />
            <input name="cfg_range[]" type="hidden" value="" />
        </div>

    @endforeach

@endif
