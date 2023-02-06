{{--商家后台导出文件进度弹窗页面--}}
<div>
    <div class="brank_s"></div>
    <div class="delivery_content">
        <div class="items" ectype="download_content">
            <div class="item">
                <div class="label">&nbsp;</div>
                <div class="value red" ectype="prompt_download">{{ lang('admin/common.total_data') }} {{ $page_count ?? 0 }} {{ lang('admin/common.page_ye') }} {{ lang('admin/common.data_export_dont_close') }}</div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // js 语言包
    var jl_processing_export_number = "{{ lang('admin/common.js_languages.jl_processing_export_number') }}";
    var jl_page_data = "{{ lang('admin/common.js_languages.jl_page_data') }}";
    var jl_completed = "{{ lang('admin/common.js_languages.jl_completed') }}";
    var jl_completed_close = "{{ lang('admin/common.js_languages.jl_completed_close') }}";


    // 开始下载
    start(1);

    function start(page_down) {

        var page_count = "{$page_count}";
        var obj = $("[ectype='download_content']");
        var html = '<div class="item"><div class="label">&nbsp;</div><div class="value on red">'+jl_processing_export_number+page_down+jl_page_data+'</div></div>';
        obj.append(html);

        var args = "page_down=" + page_down + "&page_count=" + page_count + get_args();

        $.post("{{ route('seller/order_delivery_download', ['act' => 'ajax_download']) }}", args, function (result) {

            start_response(result)

        }, 'json');
    }

    function start_response(result){
        //处理已完成文字
        $(".on").each(function(){
            $(this).removeClass('red');
            $(this).html(jl_completed);
        });

        order_download_list_csv(result);  // 下载csv文件
    }

    //导出当前分页订单csv文件
    function order_download_list_csv(result) {
        var page_down = result.page
        var page_count = result.page_count;

        var args = "page_down=" + page_down + "&page_count=" + page_count + get_args();

        $.post("{{ route('seller/download_excel') }}", args, function (result) {

            start_response_csv(result)

        }, 'json');

        if(result.is_stop == 1){
            start(result.next_page);
        }
    }

    // 下载csv文件下载完成
    function start_response_csv(result) {

        if(result.is_stop == 0){
            $("[ectype='prompt_download']").html(jl_completed_close);

            // 下载压缩文件
            location.href = "{{ route('seller/download_excel', ['act' => 'download_zip']) }}";
        }
    }


</script>
