/**
 * 仿下拉js文件
 */

 $(function() {
    /*
     **点击空白处隐藏展开框
    */
    $(document).click(function (e) {
        //仿select
        if (e.target.className != 'cite' && !$(e.target).parents("div").is(".imitate_select")) {
            $('.imitate_select ul').hide();
        }
    });
    
    /* jq仿select下拉选框 start */
    $(document).on("click", ".imitate_select .cite", function () {
        $(this).siblings("ul").toggle();
        document.selection && document.selection.empty && ( document.selection.empty(), 1)|| window.getSelection && window.getSelection().removeAllRanges();
    });

    $(document).on("click", ".imitate_select li a", function () {
        var _this = $(this);
        var val = _this.data('value');
        var text = _this.html();
        var cite = _this.parents(".imitate_select").find(".cite");

        if (cite.find("span").length > 0) {
            cite.find("span").html(text);
        } else {
            cite.html(text);
        }

        _this.parents(".imitate_select").find("input[type=hidden]").val(val);
        _this.parents(".imitate_select").find("ul").hide();
    });
    /* jq仿select下拉选框 end */

    /* select下拉默认值赋值 */
    $('.imitate_select').each(function () {
        var t = $(this);
        var val = t.find('input[type=hidden]').val();
        var cite = t.find(".cite");
        t.find('a').each(function () {
            if ($(this).data('value') == val) {
                if (cite.find("span").length > 0) {
                    cite.find("span").html($(this).html());
                } else {
                    cite.html($(this).html());
                }

            }
        })
    });

 })