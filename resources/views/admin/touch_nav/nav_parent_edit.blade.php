@include('admin.base.header')

<div class="fancy">
    <div class="title">{{ __('admin/touch_nav.menu_' . $device) }} - {{ __('admin/touch_nav.touch_nav_title') }}</div>
    <div class="content">

        <div class="flexilist of">
            <div class="main-info">
                <form action="{{ route('admin/touch_nav/nav_parent_edit', ['device' => $device]) }}" method="post" class="form-horizontal" role="form" onsubmit="return false;">
                    <div class="switch_info">

                        <div class="item hide">
                            <div class="label-t">{{ __('admin/touch_nav.touch_nav_page') }}：</div>
                            <div class="label_value col-md-4">
                                <div style="width:299px;">
                                    <select name="data[page]" class="form-control input-sm w300">
                                        <option value="">{{ __('admin/common.select_please') }}</option>
                                        <option value="user" selected>user</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/touch_nav.nav_parent_name') }}：</div>
                            <div class="label_value col-md-4">
                                <input type="text" name="data[name]" class="form-control input-sm w300" value="{{ $info['name'] ?? '' }}"/>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/common.sort_order') }}：</div>
                            <div class="label_value col-md-2">
                                <input type="text" name="data[vieworder]" class="form-control input-sm w80" value="{{ $info['vieworder'] ?? '50' }}"/>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/common.whether_display') }}：</div>
                            <div class="label_value col-md-10">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[ifshow]" class="ui-radio evnet_show" id="value_117_0" value="1"
                                               @if((isset($info['ifshow']) && $info['ifshow'] == 1) || !isset($info['ifshow']))
                                               checked
                                                @endif
                                        >
                                        <label for="value_117_0" class="ui-radio-label @if((isset($info['ifshow']) && $info['ifshow'] == 1) || !isset($info['ifshow'])) active @endif ">{{ __('admin/common.yes') }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[ifshow]" class="ui-radio evnet_show" id="value_117_1" value="0"
                                               @if(isset($info['ifshow']) && $info['ifshow'] == 0)
                                               checked
                                                @endif
                                        >
                                        <label for="value_117_1" class="ui-radio-label @if(isset($info['ifshow']) && $info['ifshow'] == 0) active @endif ">{{ __('admin/common.no') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value col-md-4">
                                <div class="info_btn">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $info['id'] ?? '' }}"/>
                                    <input type="hidden" name="data[device]" value="{{ $device ?? 'h5' }}"/>
                                    <input type="submit" value="{{ __('admin/common.button_submit') }}" class="button btn-danger bg-red fn"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<script type="text/javascript">
    $(function () {

        $(".form-horizontal").submit(function () {

            var name = $('input[name="data[name]"]').val();
            if (!name) {
                layer.msg('{{ __('admin/touch_nav.validate_nav_name_empty') }}');
                return false;
            }

            var ajax_data = $(this).serialize();

            $.post("{{ route('admin/touch_nav/nav_parent_edit', ['device' => $device]) }}", ajax_data, function (data) {
                layer.msg(data.msg);
                if (data.error == 0) {
                    window.parent.location = "{{ route('admin/touch_nav/nav_parent', ['device' => $device]) }}";
                }
                return false;
            }, 'json');
        });
    })
</script>
@include('admin.base.footer')