<?php

/**
 * 生成可视化编辑器
 *
 * @param string $input_name 输入框名称
 * @param string $input_value 输入框值
 * @param int $width 编辑器宽度
 * @param int $height 编辑器高度
 *
 * @return string
 */
function create_editor($input_name = '', $input_value = '', $width = 700, $height = 360)
{
    static $ueditor_created = false;
    $editor = '';
    if (!$ueditor_created) {
        $ueditor_created = true;
        $editor .= '<script type="text/javascript" src="' . asset('vendor/ueditor/ueditor.config.js') . '"></script>';
        $editor .= '<script type="text/javascript" src="' . asset('vendor/ueditor/ueditor.all.min.js') . '"></script>';
        $editor .= '<script>window.UEDITOR_CONFIG.serverUrl = "' . config('ueditor.route.name') . '";</script>';
    }

    $px = 'px';

    $editor .= '<script id="ue_' . $input_name . '" name="' . $input_name . '" type="text/plain" style="width:' . $width . $px . ';height:' . $height . $px . ';">' . htmlspecialchars_decode($input_value) . '</script>';
    $editor .= '<script type="text/javascript">

    var config = {toolbars: [[
      "fullscreen", "source", "|", "undo", "redo", "|",
      "bold", "italic", "underline", "fontborder", "strikethrough", "superscript", "subscript", "blockquote",  "|", "forecolor", "backcolor", "insertorderedlist", "insertunorderedlist", "selectall", "cleardoc", "|",
      "rowspacingtop", "rowspacingbottom", "lineheight", "|", "fontfamily", "fontsize", "|",
      "directionalityltr", "directionalityrtl", "indent", "|",
      "justifyleft", "justifycenter", "justifyright", "justifyjustify", "|", "touppercase", "tolowercase", "|",
      "link", "unlink", "anchor", "|", "imagenone", "imageleft", "imageright", "imagecenter", "|",
      "simpleupload", "insertimage", "insertvideo", "attachment", "map", "drafts"
    ]],
    initialFrameWidth : "' . $width . '",
    initialFrameHeight : "' . $height . '",
    };

    var ue_' . $input_name . ' = UE.getEditor("ue_' . $input_name . '", config);

    ue_' . $input_name . '.ready(function() {
        ue_' . $input_name . '.execCommand("serverparam", "_token", "' . csrf_token() . '"); // 设置 CSRF token.
    });

    </script>';

    return $editor;
}


//设置默认筛选 分类。品牌列表
function set_default_filter_new($goods_id = 0, $cat_id = 0, $user_id = 0, $cat_type_show = 0)
{
    $filter = [
        'filter_category_navigation' => '',
        'filter_category_list' => '',
        'filter_brand_list' => '',
    ];
    //分类导航
    if ($cat_id > 0) {
        $parent_cat_list = get_select_category($cat_id, 1, true, $user_id);
        $filter['filter_category_navigation'] = get_array_category_info($parent_cat_list);
    }

    if ($user_id) {
        $seller_shop_cat = seller_shop_cat($user_id);
    } else {
        $seller_shop_cat = [];
    }

    $filter['filter_category_list'] = get_category_list($cat_id, 0, $seller_shop_cat, $user_id);//分类列表
    $filter['filter_brand_list'] = search_brand_list($goods_id, $user_id);//品牌列表
    $filter['cat_type_show'] = $cat_type_show;//平台分类

    return $filter;
}
