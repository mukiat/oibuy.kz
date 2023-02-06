@include('admin.base.header')

<div class="warpper">
    <div class="title"><a href="{{ route('admin/touch_oauth/index') }}" class="s-back">{{ lang('common.back') }}</a> {{ $lang['touch_list'] }} - {{ $ur_here }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                <li>{{ $lang['edit_plug_tips']['0'] }}</li>

                @if($info['type'] == 'wechat' || $info['type'] == 'weixin')

                    <li>{!! $lang['edit_plug_tips']['1'] !!}</li>

                @endif

                {!! $callback_notice ?? '' !!}

            </ul>
        </div>
        <div class="flexilist">
            <div class="main-info oauth_list">

                <div class="plugin_item mr0 mb8" style="clear:both">
                    <div class="plugin_icon {{ $info['type'] }}-bgcolor">
                        {{--<img class="img-rounded" src="{{ asset('assets/mobile/img/oauth/sns_'.$info['type'].'.png') }}" alt="">--}}
                        <i class="img-rounded icon iconfont icon-{{ $info['type'] }} bg-{{ $info['type'] }}"></i>
                    </div>
                    <div class="plugin_content">
                        <h3 class="title">{{ $info['name'] }}</h3>
                        <p class="desc">{{ $info['desc'] ?? '' }}</p>
                    </div>
                </div>

                <form action="{{ route('admin/touch_oauth/edit') }}" method="post" class="form-horizontal" role="form">
                    <div class="switch_info">

                        @foreach($info['config'] as $key => $vo)

                            <div class="item">

                                @if($vo['type'] == 'text')

                                    <div class="label-t">{{ $vo['label'] }}：</div>
                                    <div class="label_value">
                                        <input name="cfg_value[]" type="text" maxlength="50" class="text" value="{{ $vo['value'] }}"/> * {{ $vo['help'] }}
                                        <input name="cfg_name[]" type="hidden" value="{{ $vo['name'] }}"/>
                                        <input name="cfg_type[]" type="hidden" value="{{ $vo['type'] }}"/>
                                        <input name="cfg_label[]" type="hidden" value="{{ $vo['label'] }}"/>
                                    </div>

                                @endif

                            </div>

                        @endforeach

                        <div class="item">
                            <div class="label-t">{{ $lang['sort_order'] }}：</div>
                            <div class="label_value">
                                <input type="number" min="0" name='sort' value='{{ $info['sort'] }}' class="text w100" autocomplete="off"/>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ $lang['close_whether'] }}：</div>
                            <div class="label_value">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="status" class="ui-radio event_zhuangtai" id="status_0" value="1"
                                               @if(isset($info['status']) && $info['status'] == 1)
                                               checked
                                                @endif
                                        >
                                        <label for="status_0" class="ui-radio-label
                                            @if(isset($info['status']) && $info['status']==1)
                                                active
                                                @endif
                                                ">{{ $lang['enabled'] }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="status" class="ui-radio event_zhuangtai" id="status_1" value="0"
                                               @if(isset($info['status']) && $info['status'] == 0)
                                               checked
                                                @endif
                                        >
                                        <label for="status_1" class="ui-radio-label
                                                @if(isset($info['status']) && $info['status'] == 0)
                                                active
                                                @endif
                                                ">{{ $lang['disabled'] }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ $lang['web_site'] }}：</div>
                            <div class="label_value">{{ $info['website'] }}
                                <a href="{{ $info['website'] }}" class="btn" target="_blank">{{ $lang['goto_apply'] }}</a>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value info_btn">
                                @csrf
                                <input type="hidden" name="type" value="{{ $info['type'] }}"/>
                                <input type="submit" value="{{ $lang['edit'] }}" class="button btn-danger bg-red"/>
                                <input type="reset" value="{{ lang('admin/common.button_reset') }}" class="button button_reset"/>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<script>
    //验证表单
    $('input[type="submit"]').click(function () {
        var cfg_value = $('input[name="cfg_value[]"]').val();
        if (!cfg_value) {
            layer.msg('{{ $lang['config_data_empty'] }}', {icon: 2});
            return false;
        }
    });

</script>

@include('admin.base.footer')
