@if(!empty(config('shop.show_copyright')))
<div class="footer">
        @if(!empty(config('shop.copyright_link')))
                <p><a class="color-white" href='{!! config('shop.copyright_link') !!}' target='_blank'>{!! config('shop.copyright_text') !!}</a></p>
        @else
                <p>{!! config('shop.copyright_text') !!}</p>
        @endif
</div>
@endif

<script type="text/javascript">
    function showImg(id, title) {
        var _content = $('#' + id).html();
        art.dialog({
            id: 'showImg',
            padding: 0,
            title: title,
            content: _content,
            lock: false
        });
    }

    $(function () {

        // 删除提示
        $(".delete_confirm").click(function () {
            var url = $(this).attr("data-href");
            //询问框
            layer.confirm('{{ lang('admin/common.confirm_delete') }}', {
                btn: ['{{ lang('admin/common.ok') }}', '{{ lang('admin/common.cancel') }}'] //按钮
            }, function () {
                window.location.href = url;
            });
        });

        $("#explanationZoom").on("click", function () {
            var explanation = $(this).parents(".explanation");
            var width = $(".content").width();
            if ($(this).hasClass("shopUp")) {
                $(this).removeClass("shopUp");
                $(this).attr("title", "{{ lang('admin/common.fold_tips') }}");
                explanation.find(".ex_tit").css("margin-bottom", 10);
                explanation.animate({
                    width: width - 0
                }, 300, function () {
                    $(".explanation").find("ul").show();
                });
            } else {
                $(this).addClass("shopUp");
                $(this).attr("title", "{{ lang('admin/common.relevant_setup_operation') }}");
                explanation.find(".ex_tit").css("margin-bottom", 0);
                explanation.animate({
                    width: "115"
                }, 300);
                explanation.find("ul").hide();
            }
        });
    })
</script>
</body>
</html>
