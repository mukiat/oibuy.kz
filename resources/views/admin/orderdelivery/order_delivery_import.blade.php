@include('admin.base.header')

<div class="warpper">
    <div class="title">{{ lang('admin/common.order_word') }} - {{ lang('admin/order.order_delivery_import') }}</div>
    <div class="content">
        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ lang('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ lang('admin/common.fold_tips') }}"></span>
            </div>
            <ul>

                @foreach(lang('admin/order.order_delivery_import_tips') as $v)
                    <li>{!! $v !!}</li>
                @endforeach

            </ul>
        </div>

        <div class="flexilist">

            {{--<div class="common-head mt10">--}}
                {{--<div class="fbutton fl"><a href="{{ route('admin/order_delivery_download') }}"><div class="csv"><span><i class="fa fa-download"></i>{{ lang('admin/order.order_delivery_download') }}</span></div></a></div>--}}
            {{--</div>--}}

            <div class="main-info">
                <form action="{{ route('admin/order_delivery_import') }}" method="post"  name="listForm" enctype="multipart/form-data" role="form" class="form-horizontal" >
                    <div class="switch_info">

                        <div class="item">
                            <div class="label-t">{{ lang('admin/order.upload_file') }}：</div>
                            <div class="label_value">
                                <div class="type-file-box">
                                    <input type="button" id="button" class="type-file-button">
                                    <input type="file" class="type-file-file" id="file" name="file" data-state="xlsfile" size="30" hidefocus="true" accept=".xls" />

                                    <input type="text" name="file_path" class="type-file-text"  id="textfield"  value="" style="height: 30px" autocomplete="off" readonly />
                                </div>
                                <div class="notice">{{ lang('admin/order.upload_file_notic') }}</div>
                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="label_value info_btn">
                                @csrf
                                <input type="hidden" name="form_token" value="{{ str_random(32) }}">
                                <input type="submit" class="button btn-danger bg-red" value="{{ lang('admin/common.button_submit') }}" />

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">

    /* file上传文件类型 封装函数 start*/
    $(document).on("change", ".type-file-file", function () {
        var state = $(this).data('state');
        var filepath = $(this).val();
        var extStart = filepath.lastIndexOf(".");
        var ext = filepath.substring(extStart, filepath.length).toUpperCase();

        if (state == 'xlsfile' && ext != ".XLS") {
            layer.msg("上传文件限于xls格式");
            $(this).attr('value', '');
            return false;
        }

        $(this).siblings(".type-file-text").val(filepath);
    });

    $(".type-file-box").hover(function () {
        $(this).addClass("hover");
    }, function () {
        $(this).removeClass("hover");
    });
    /* file上传文件类型 封装函数 end*/

    // 提交
    $("form[name='listForm']").submit(function(){
        $('input[type="submit"]').attr('disabled', true);
    });

</script>

@include('admin.base.footer')