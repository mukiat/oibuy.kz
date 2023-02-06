@include('admin.base.header')

<style>
    /*.dates_box {width: 300px;}*/
    .dates_box_top {height: 32px;}
    .dates_bottom {height: auto;}
    .dates_hms {width: auto;}
    .dates_btn {width: auto;}
    .dates_mm_list span {width: auto;}
</style>

<div class="warpper">
    <div class="title"><a href="{{ route('admin/goodsserviceslabel/list') }}" class="s-back">{{ __('admin/common.back') }}</a>{{ __('admin/goods_services_label.goods') }}
        - {{ __('admin/goods_services_label.add_label') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(__('admin/goods_services_label.services_label_notice') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>
        <div class="flexilist">
            <div class="main-info of">
                <form action="{{ route('admin/goodsserviceslabel/update') }}" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
                    <div class="switch_info of">
                        <div class="item">
                            <div class="label-t"><em class="color-red">*</em> {{ __('admin/goods_services_label.label_name') }}：</div>
                            <div class="label_value">
                                <input type="text" name="data[label_name]" value="{{ $label_info['label_name'] }}" class="text"/>
                                <div class="notic">{{ __('admin/goods_services_label.label_name_notice') }}</div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t">{{ __('admin/goods_services_label.label_explain') }}：</div>
                            <div class="label_value">
                                <textarea  class="textarea" name="data[label_explain]">{{ $label_info['label_explain'] }}</textarea>
                                <div class="notic">{{ __('admin/goods_services_label.label_explain_notice') }}</div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="label-t"><em class="color-red">*</em> {{ __('admin/goods_services_label.label_image') }}：</div>
                            <div class="label_value">
                                <div class="type-file-box">
                                    <input type="button" id="button" class="type-file-button">
                                    <input type="file" class="type-file-file" name="label_image" size="30" data-state="imgfile">
                                    <span class="show">
                                        <a href="#inline" class="nyroModal fancybox" title="{{ __('admin/common.preview') }}">
                                            <i class="fa fa-picture-o"></i>
                                        </a>
                                    </span>
                                    <input type="text" name="file_path" class="type-file-text hide" value="{{ $label_info['label_image'] ?? '' }}" style="display:none">
                                </div>
                                @if($type == 1)
                                    <div class="notic">{{ __('admin/goods_services_label.label_image_notice_1') }}</div>
                                @else
                                    <div class="notic">{{ __('admin/goods_services_label.label_image_notice') }}</div>
                                @endif

                            </div>
                        </div>

                        @if($type == 1)
                        <div class="item">
                            <div class="label-t"><em class="color-red">*</em> {{ __('admin/goods_services_label.label_show_time') }}：</div>
                            <div class="label_value">
                                <div id="text_time1" class="text_time">
                                    <input type="text" class="text" name="data[start_time]" id="promote_start_date" value="{{ $label_info['start_time_formated'] ?? date('Y-m-d H:i:s') }}"  autocomplete="off" readonly />
                                </div>
                                <span class="bolang">~&nbsp;&nbsp;</span>
                                <div id="text_time2" class="text_time">
                                    <input type="text" class="text" name="data[end_time]" id="promote_end_date" value="{{ $label_info['end_time_formated'] ?? date('Y-m-d H:i:s', mktime(0,0,0,date('m')+1, date('d'), date('Y'))) }}" autocomplete="off" readonly />
                                </div>
                                <div class="notic">{{ __('admin/goods_services_label.label_show_time_notice') }}</div>
                            </div>
                        </div>

                        @endif

                        <div class="item">
                            <div class="label-t"><em class="color-red">*</em> {{ __('admin/goods_services_label.sort') }}：</div>
                            <div class="label_value">
                                <input type="text" name="data[sort]" value="{{ $label_info['sort'] ?? 50 }}" class="text w100"/>
                                <div class="notic">{{ __('admin/goods_services_label.sort_notice') }}</div>
                            </div>
                        </div>

                        @if ($label_info['label_code'] != 'no_reason_return')
                        <div class="item">
                            <div class="label-t">{{ __('admin/goods_services_label.merchant_use') }}：</div>
                            <div class="label_value">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[merchant_use]" class="ui-radio event_zhuangtai" id="merchant_use_1" value="1"
                                               @if($label_info['merchant_use'] == 1) checked @endif>
                                        <label for="merchant_use_1" class="ui-radio-label @if($label_info['merchant_use'] == 1) active @endif">{{ __('admin/common.yes') }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[merchant_use]" class="ui-radio event_zhuangtai" id="merchant_use_0" value="0"
                                               @if($label_info['merchant_use'] == 0) checked @endif>
                                        <label for="merchant_use_0" class="ui-radio-label @if($label_info['merchant_use'] == 0) active @endif">{{ __('admin/common.no') }}</label>
                                    </div>
                                </div>
                                <div class="notic">{{ __('admin/goods_services_label.merchant_use_notice') }}</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">{{ __('admin/goods_services_label.status') }}：</div>
                            <div class="label_value">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[status]" class="ui-radio event_zhuangtai" id="status_1" value="1" @if($label_info['status'] == 1) checked @endif>
                                        <label for="status_1" class="ui-radio-label @if($label_info['status'] == 1) active @endif">{{ __('admin/goods_services_label.use') }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[status]" class="ui-radio event_zhuangtai" id="status_0" value="0" @if($label_info['status'] == 0) checked @endif>
                                        <label for="status_0" class="ui-radio-label @if($label_info['status'] == 0) active @endif">{{ __('admin/goods_services_label.no_use') }}</label>
                                    </div>
                                </div>
                                <div class="notic">{{ __('admin/goods_services_label.status_notice') }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value info_btn">
                                @csrf
                                <input type="hidden" name="id" value="{{ $id ?? 0 }}"/>
                                <input type="submit" value="{{ __('admin/common.button_submit') }}" class="button btn-danger bg-red"/>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel panel-default" style="display: none;" id="inline">
            <div class="panel-body">
                <img src="{{ $label_info['fromated_label_image'] ?? '' }}" class="img-responsive label_image"/>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    @if($type == 1)

    // 选择时间
    var opts1 = {
        'targetId':'promote_start_date',
        'triggerId':['promote_start_date'],
        'alignId':'text_time1',
        'format':'-',
        'hms':'on',
        'min':'{{ $label_info['start_time_formated'] ?? '' }}', //最小时间
    },opts2 = {
        'targetId':'promote_end_date',
        'triggerId':['promote_end_date'],
        'alignId':'text_time2',
        'format':'-',
        'hms':'on',
        'min':'{{ $label_info['end_time_formated'] ?? '' }}', //最小时间
    }

    xvDate(opts1);
    xvDate(opts2);

    @endif

    $(function () {
        //file移动上去的js
        $(".type-file-box").hover(function () {
            $(this).addClass("hover");
        }, function () {
            $(this).removeClass("hover");
        });

        // fancybox 弹出框
        $(".fancybox").fancybox({
            width: '60%',
            height: '50%',
            closeBtn: true,
            title: ''
        });

        // 上传图片预览
        $("input[name='label_image']").change(function (event) {
            // 根据这个 id 获取文件的 HTML5 js 对象
            var files = event.target.files, file;
            if (files && files.length > 0) {
                // 获取目前上传的文件
                file = files[0];

                @if($type == 0)
                // 那么我们可以做一下诸如文件大小校验的动作
                if (file.size > 1024 * 200) {
                    layer.msg('{{ __('file.file_size_limit') }}');
                    return false;
                }
                @endif

                // 预览图片
                var reader = new FileReader();
                // 将文件以Data URL形式进行读入页面
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    $(".label_image").attr("src", this.result);
                };
            }
        });

        $(".form-horizontal").submit(function () {

            var name = $('input[name="data[label_name]"]').val();
            if (!name) {
                layer.msg('{{ __('admin/goods_services_label.label_name_not_null') }}');
                return false;
            }

            var label_image = $(".label_image").attr("src");
            if (!label_image) {
                layer.msg('{{ __('admin/goods_services_label.label_image_not_null') }}');
                return false;
            }

            var sort = $('input[name="data[sort]"]').val();
            if (!sort) {
                layer.msg('{{ __('admin/goods_services_label.sort_not_null') }}');
                return false;
            }
        });
    });
</script>
@include('admin.base.footer')
