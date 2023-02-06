@if(isset($cfg) && !empty($cfg))

    <div class="item">
        <div class="label-t">{{ lang('admin/users.rights_status') }}：</div>
        <div class="label_value">

            <div class="checkbox_items">
                <div class="checkbox_item">
                    <input type="radio" name="data[enable]" class="ui-radio event_zhuangtai" id="value_enable_1" value="1"
                           @if((isset($cfg['enable']) && $cfg['enable'] == 1) || !isset($cfg['enable']))
                           checked
                            @endif
                    >
                    <label for="value_enable_1" class="ui-radio-label
							@if((isset($cfg['enable']) && $cfg['enable'] == 1) || !isset($cfg['enable']))
                            active
                            @endif
                            ">{{ lang('admin/common.open') }}</label>
                </div>
                <div class="checkbox_item">
                    <input type="radio" name="data[enable]" class="ui-radio event_zhuangtai" id="value_enable_0" value="0"
                           @if(isset($cfg['enable']) && $cfg['enable'] == 0)
                           checked
                            @endif
                    >
                    <label for="value_enable_0" class="ui-radio-label
							@if(isset($cfg['enable']) && $cfg['enable'] == 0)
                            active
                            @endif
                            ">{{ lang('admin/common.close') }}</label>
                </div>
            </div>

        </div>
    </div>

    <div class="item">
        <div class="label-t">{{ lang('admin/users.rights_name') }}：</div>
        <div class="label_value">
            <div class="w300">
                <input type="text" name="data[name]" class="form-control" value="{{ $cfg['name'] }}" placeholder="" />
            </div>
        </div>
    </div>

    <div class="item">
        <div class="label-t">{{ lang('admin/users.rights_icon') }}：</div>
        <div class="label_value">
            <div class="type-file-box">
                <input type="button" id="button" class="type-file-button">
                <input type="file" class="type-file-file" name="rights_icon" size="30" data-state="imgfile" >
                <span class="show">
                    <a href="#inline" class="nyroModal fancybox" title="{{ lang('admin/common.preview') }}">
                        <i class="fa fa-picture-o"></i>
                    </a>
                </span>
                <input type="text" name="file_path" class="type-file-text hide" value="{{ $cfg['icon'] }}" id="textfield" style="display:none">
            </div>
            <div class="notice">{{ lang('admin/users.rights_icon_notice') }}</div>
        </div>
    </div>

    <div class="item">
        <div class="label-t">{{ lang('admin/users.rights_desc') }}：</div>
        <div class="label_value">
            <div class="w300">
                <textarea name="data[description]" class="form-control" rows="3" maxlength="100" placeholder="" >{{ $cfg['description'] ?? '' }}</textarea>
            </div>
        </div>
    </div>

    <div class="item">
        <div class="label-t">{{ lang('admin/users.sort_order') }}：</div>
        <div class="label_value">
            <div class="w100">
                <input type="number" min="0" name="data[sort]" class="form-control" value="{{ $cfg['sort'] ?? '50' }}" placeholder="" />
            </div>
        </div>
    </div>

@endif
