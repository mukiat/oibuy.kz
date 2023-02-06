
<script type="text/javascript">
    $(function () {
        // 操作提示
        $("#explanationZoom").on("click", function () {
            var explanation = $(this).parents(".explanation");
            var width = $(".content").width();
            if ($(this).hasClass("shopUp")) {
                $(this).removeClass("shopUp");
                $(this).attr("title", "{{ __('admin/common.fold_tips') }}");
                explanation.find(".ex_tit").css("margin-bottom", 10);
                explanation.animate({
                    width: width - 0
                }, 300, function () {
                    $(".explanation").find("ul").show();
                });
            } else {
                $(this).addClass("shopUp");
                $(this).attr("title", "{{ __('admin/common.relevant_setup_operation') }}");
                explanation.find(".ex_tit").css("margin-bottom", 0);
                explanation.animate({
                    width: "118"
                }, 300);
                explanation.find("ul").hide();
            }
        });

    });
</script>
</body>
</html>