@include('admin.base.admin_html_head')

<script type="text/javascript">
    /*这里把JS用到的所有语言都赋值到这里*/
    @foreach(__('admin::user_baitiao_log.js_languages') as $key => $item)
    var {{ $key }} = "{{ $item }}";
    @endforeach

</script>
<script src="{{ asset('assets/mobile/vendor/layer/layer.js') }}"></script>

<div class="warpper">
    <div class="title">{{ __('admin/common.08_members') }} - {{ $ur_here ?? '' }}</div>
    <div class="content">
        <div class="tabs_info mt10">
            <ul>
                <li ><a href="{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=list') }}">{{ __('admin::user_baitiao_log.bt_list') }}</a></li>
                <li class="curr"><a href="{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=setting') }}">{{ __('admin::user_baitiao_log.set_baitiao') }}</a></li>
            </ul>
        </div>

        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @foreach(__('admin::user_baitiao_log.set_baitiao_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist of">
            <div class="mian-info">
                <form class="form-horizontal" action="{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=setting') }}" method="post" onsubmit="return false;">
                    <div class="switch_info" >

                        <div class="item" >
                            <div class="label">{{ __('admin::user_baitiao_log.set_baitiao_open') }}：</div>
                            <div class="label_value">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="enabled" class="ui-radio evnet_1" id="value_1_0" value="0"
                                               @if(isset($enabled) && $enabled == 0) checked="true" @endif
                                        />
                                        <label for="value_1_0" class="ui-radio-label">{{ __('admin/common.no') }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="enabled" class="ui-radio evnet_1" id="value_1_1" value="1"
                                               @if(isset($enabled) && $enabled == 1) checked="true" @endif
                                        />
                                        <label for="value_1_1" class="ui-radio-label">{{ __('admin/common.yes') }}</label>
                                    </div>
                                </div>
                                <div class="form_prompt"></div>
                               <div class="notic">{!! __('admin::user_baitiao_log.set_baitiao_notice') !!}</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label">&nbsp;</div>
                            <div class="label_value info_btn">
                                @csrf
                                <input name="callback" type="hidden" value="{{ urlencode(request()->getRequestUri()) }}">
                                <input type="submit" value="{{ __('admin/common.button_submit') }}" class="button" >
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<div id="footer" class="copyright">
    @include('admin.base.copyright')
</div>
<script type="text/javascript">
    $(function () {
        // 提交
        $(".form-horizontal").submit(function () {

            var ajax_data = $(this).serialize();

            $.post("{{ url(ADMIN_PATH . '/user_baitiao_log.php?act=setting') }}", ajax_data, function (data) {
                layer.msg(data.msg);

                setTimeout(function(){
                    window.location.reload();
                }, 1000);

                return false;
            }, 'json');
        });
    });
</script>
</body>
</html>