@include('admin.base.header')

<style>
    #footer {
        position: static;
        bottom: 0px;
    }
</style>

<div class="fancy">

    <div class="title">{{ $title ?? '' }}</div>
    <div class="content_tips">

        <div class="flexilist of">

            <div class="progress progress-striped">
                <div id="progress" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" >
                    <span class="sr-only">>{{ $percent ?? 0 }} % {{ __('admin/seller_divide.updating_complete') }}</span>
                </div>
            </div>

            <div class="color-red font14 percent-status" >{{ $percent ?? 0 }} %</div>

        </div>
    </div>

</div>

<script type="text/javascript">
    $(function () {

        // 更新
        updateURL(1);

        function updateURL(page) {
            $.ajax({
                type: 'POST',
                url: "{{ $request_url }}",
                async: true,
                cache: false,
                dataType: 'json',
                data: {page: page},
                success: function (res) {
                    if (res.status == 0) {
                        // 显示进度
                        $("#progress").css("width", res.percent + "%");
                        $('.percent-status').text(res.percent + "% {{ __('admin/seller_divide.updating_complete') }}");

                        if (res.next_page <= res.total_page) {
                            updateURL(res.next_page);
                        } else {
                            $('.percent-status').text(res.percent + "%  {{ __('admin/seller_divide.update_completed') }}");

                            layer.msg('{{ __('admin/seller_divide.update_completed') }}');

                            if (window.parent.$.fancybox) {
                                setTimeout(function () {
                                    window.parent.$.fancybox.close();
                                }, 2000);
                            }
                        }

                    } else {
                        layer.msg(res.msg);

                        if (window.parent.$.fancybox) {
                            setTimeout(function () {
                                window.parent.$.fancybox.close();
                            }, 1000);
                        }
                    }
                    return false;
                }
            });
            return false;
        }
        
    });

</script>

@include('admin.base.footer')
