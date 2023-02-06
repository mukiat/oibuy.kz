@include('admin.base.header')

<link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/font-awesome.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/purebox.css') }}" />
<script src="{{ asset('js/transport_jquery.js') }}"></script>
<script src="{{ asset('js/utils.js') }}"></script>
<script src="{{ asset('js/jquery.cookie.js') }}"></script>
<script src="{{ asset('js/lib_ecmobanFunc.js') }}"></script>
<script src="{{ asset('js/jquery.nyroModal.js') }}"></script>
<script src="{{ asset('assets/admin/js/listtable.js') }}"></script>
<script src="{{ asset('assets/admin/js/listtable_pb.js') }}"></script>
<style>
    .consult_kefu_url {
        display: none;
    }
    .consult_kefu_url.active {
        display: block
    }
</style>

<div class="wrapper">
    <div class="title">{{ lang('admin/common.20_ectouch') }} - {{ $ur_here }}</div>
    <div class="content_tips">
        <div class="flexilist">
                <div class="main-info">
                    <form method="post" action="shop_config.php?act=post" enctype="multipart/form-data" class="form-horizontal" role="form">
                    <div class="switch_info">
                        @foreach($consult_set as  $value)
                            <div class="item {{ $value['code'] }} @if($value['code'] == 'consult_kefu_url') active @endif" data-val="{{ $value['id'] }}">
                                <div class="label-t">{{ $value['name'] }}：</div>

                                @if($value['type'] == 'text')
                                    <div class="label_value">
                                        <input type="text" name="value[{{ $value['id'] }}]" class="text w450" value="{{ $value['value'] }}">
                                        <p class="notic">{!! $value['warning'] ?? '' !!}</p>
                                        @if(!empty($value['desc']))
                                            <div class="notic">{{ $value['desc'] }}</div>
                                        @endif
                                    </div>

                                @elseif($value['type'] == 'select')
                                    <div class="label_value">
                                        <div class="checkbox_items">
                                            @foreach($value['display_options'] as $k => $val)
                                                <div class="checkbox_item">
                                                    <input type="radio" name="value[{{ $value['id'] }}]"
                                                           class="ui-radio evnet_{{ $value['code'] }}
                                                           @if($value['code'] == 'consult_kefu_type')
                                                           @if($value['store_options'][$k] == 0) checkbox_item-1  @else j-checkbox_item @endif
                                                           @endif"
                                                           id="value_{{ $value['id'] }}_{{ $k }}"
                                                           value="{{ $value['store_options'][$k] }}"
                                                           @if($value['value'] == $value['store_options'][$k]) checked="true" @endif
                                                    />
                                                    <label for="value_{{ $value['id'] }}_{{ $k }}"
                                                           class="ui-radio-label">{{ $value['display_options'][$k] }}</label>
                                                </div>

                                            @endforeach
                                        </div>
                                        <div class="form_prompt"></div>

                                        @if(!empty($value['desc']))
                                            <div class="notic">{{ $value['desc'] }}</div>
                                        @endif
                                    </div>
                                @elseif($value['type'] == 'options')
                                    <div class="label_value">
                                        <div id="select{{ $value['id'] }}_{{ $k }}"
                                             class="imitate_select select_w320">
                                            <div class="cite">{{ $lang['please_select'] }}</div>
                                            <ul>
                                                @foreach($value['display_options'] as $k => $val)
                                                    <li><a href="javascript:;"
                                                           data-value="{{ $value['store_options'][$k] }}"
                                                           class="ftx-01">{{ $val }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <input name="value[{{ $value['id'] }}]" type="hidden"
                                                   value="{{ $value['value'] }}"
                                                   id="{{ $value['id'] }}_{{$k}}_val">
                                        </div>
                                        <div class="form_prompt"></div>
                                        @if(!empty($value['desc']))
                                            <div class="notic">{{ $value['desc'] }}</div>
                                        @endif
                                    </div>

                                @elseif($value['type'] == 'file')

                                    <div class="label_value">
                                        <div class="type-file-box">
                                            <input type="button" name="button" id="button" class="type-file-button" value="" />
                                            <input type="file" class="type-file-file"  name="{{$value['code']}}" size="30" data-state="imgfile" hidefocus="true" value="" />

                                            @if($value['value'])
                                            <span class="show">
                                                <a href="{{$value['value']}}" target="_blank" class="nyroModal"><i class="icon icon-picture" data-tooltipimg="{{$value['value']}}" ectype="tooltip" title="tooltip"></i></a>
                                            </span>
                                            @endif
                                            <input type="text" name="textfile" class="type-file-text" id="textfield" readonly />
                                        </div>
                                        <div class="form_prompt"></div>
                                        @if($value['desc'])<div class="notic">{{$value['desc']}}</div>@endif
                                    </div>

                                @endif
                            </div>
                        @endforeach

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value info_btn export_btn">
                                @csrf
                                <input name="type" type="hidden" value="consult_set">
                                <input name="query"   type="submit" class="button" value="{{ lang('admin/common.button_submit') }}" />
                            </div>
                        </div>

                    </div>
                    </form>
                </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/admin/js/jquery.purebox.js') }}"></script>
<script src="{{ asset('js/jquery.picTip.js') }}"></script>

<script type="text/javascript">

    // 客服模式切换
    $(function () {
        var type = $(".evnet_consult_kefu_type:checked").val();
        if (type == 1) {
            $(".consult_kefu_url").addClass("active")
        } else {
            $(".consult_kefu_url").removeClass("active")
        }
    });

    $(".j-checkbox_item").on("click", function () {
        $(".consult_kefu_url").addClass("active")
    });
    $(".checkbox_item-1").on("click", function () {
        $(".consult_kefu_url").removeClass("active")
    });

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
