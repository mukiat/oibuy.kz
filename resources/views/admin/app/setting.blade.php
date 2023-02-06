@include('admin.base.header')

<link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/main.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/purebox.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/font-awesome.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/js/jquery-ui/jquery-ui.min.css') }}" />
<script src="{{ asset('js/jquery.nyroModal.js') }}"></script>

<div class="wrapper">
    <div class="title">{{ lang('admin/app.app_config') }}</div>
    <div class="content_tips">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4>
                <span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @foreach(lang('admin/app.app_config_tips') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist">
            <div class="main-info">
                <form method="post" action="../shop_config.php?act=post&callback={{ urlencode(request()->getRequestUri()) }}"
                      enctype="multipart/form-data"
                      class="form-horizontal" role="form">
                    <div class="switch_info">
                        @foreach($group_list as $value)
                            <div
                                class="item {{ $value['code'] }}"
                                data-val="{{ $value['id'] }}">
                                <div class="label-t">{{ trans('admin/app.'.$value['name']) }}</div>

                                @if($value['type'] == 'text')
                                    <div class="label_value">
                                        <input type="text" name="value[{{ $value['id'] }}]" class="text"
                                               value="{{ $value['value'] }}">
                                        <p class="notic">{!! $value['warning'] ?? '' !!}</p>
                                        <div
                                            class="notic">{{ lang('admin/app.'.$value['name'] . '_desc') }}</div>
                                    </div>

                                @elseif($value['type'] == 'file')
                                    <div class="label_value">
                                        <div class="type-file-box">
                                            <input type="button" name="button" id="button" class="type-file-button"
                                                   value=""/>
                                            <input type="file" class="type-file-file" name="{{$value['code']}}"
                                                   size="30" data-state="imgfile" hidefocus="true" value=""/>

                                            @if($value['value'])
                                                <span class="show">
                                                        <a href="{{$value['value']}}" target="_blank" class="nyroModal"><i
                                                                class="icon icon-picture"
                                                                data-tooltipimg="{{$value['value']}}"
                                                                ectype="tooltip" title="tooltip"></i></a>
                                                    </span>
                                            @endif
                                            <input type="text" name="textfile" class="type-file-text" id="textfield"
                                                   readonly/>
                                        </div>
                                        @if(!empty($value['del_img']))
                                            <a href="{{ url(ADMIN_PATH . '/shop_config.php?act=del&code=' . $value['code'] . '&callback=' . urlencode(request()->getRequestUri())) }}" class="btn red_btn h30 mr10 fl" style="line-height:30px;">{{ __('admin::common.drop') }}</a>
                                        @else
                                            @if(!empty($value['value']))
                                                <img src="{{ asset('/assets/admin/images/yes.gif') }}" alt="yes" class="fl mt10" />
                                            @else
                                                <img src="{{ asset('/assets/admin/images/no.gif') }}" alt="no" class="fl mt10" />
                                            @endif
                                        @endif
                                        <div class="form_prompt"></div>
                                        <div
                                            class="notic">{{ lang('admin/app.'.$value['name'] . '_desc') }}</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value info_btn export_btn">
                                @csrf
                                <input name="type" type="hidden" value="app_config">
                                <input name="query" type="submit" class="button"
                                       value="{{ lang('admin/common.button_submit') }}"/>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/admin/js/jquery.purebox.js') }}"></script>
<script src="{{ asset('assets/admin/js/jquery-ui/jquery-ui.min.js') }}"></script>

<script type="text/javascript">
    $(function(){
        //图片点击放大
        $('.nyroModal').nyroModal();

        /* jquery.ui tooltip.js title美化 */
        $("[data-toggle='tooltip']").tooltip({
            position: {
                my: "left top+5",
                at: "left bottom"
            }
        });

        /* jquery.ui tooltip.js 图片放大 */
        jQuery.tooltipimg = function(){
            $("[ectype='tooltip']").tooltip({
                content: function(){
                    var element = $(this);
                    var url = element.data("tooltipimg");
                    if(element.is('[data-tooltipImg]')){
                        return "<img src='" + url + "' />";
                    }
                },
                position:{
                    using:function(position,feedback){
                        $(this).css(position).addClass("ui-tooltipImg");
                    }
                }
            });
        }
        $.tooltipimg();
    })
</script>

@include('admin.base.footer')
