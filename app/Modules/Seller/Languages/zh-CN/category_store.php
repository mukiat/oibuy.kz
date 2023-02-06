<?php

$_LANG['category_separate'] = '分离店铺分类';

//ecmoban模板堂 --zhuo start
$_LANG['categoryFile'] = '类目证件';
$_LANG['document_title'] = '证件标题';
$_LANG['title_catedit_succed'] = '证件标题编辑成功!';
//ecmoban模板堂 --zhuo end

/* 商品分类字段信息 */
$_LANG['cat_id'] = '编号';
$_LANG['cat_name'] = '分类名称';
$_LANG['isleaf'] = '不允许';
$_LANG['noleaf'] = '允许';
$_LANG['keywords'] = '关键字';
$_LANG['cat_desc'] = '分类描述';
$_LANG['parent_id'] = '上级分类';
$_LANG['sort_order'] = '排序';
$_LANG['measure_unit'] = '数量单位';
$_LANG['delete_info'] = '删除选中';
$_LANG['category_edit'] = '编辑商品分类';
$_LANG['move_goods'] = '转移商品';
$_LANG['cat_top'] = '顶级分类';
$_LANG['show_in_nav'] = '是否显示在导航栏';
$_LANG['cat_style'] = '分类的样式表文件';
$_LANG['is_show'] = '是否显示';
$_LANG['show_in_index'] = '设置为首页推荐';
$_LANG['notice_show_in_index'] = '该设置可以在首页的最新、热门、推荐处显示该分类下的推荐商品';
$_LANG['goods_number'] = '商品数量';
$_LANG['grade'] = '价格区间个数';
$_LANG['notice_grade'] = '该选项表示该分类下商品最低价与最高价之间的划分的等级个数，填0表示不做分级，最多不能超过10个。';
$_LANG['short_grade'] = '价格分级';
$_LANG['phone_icon'] = '手机小图标';
$_LANG['level'] = '级';
$_LANG['level_alt'] = '级别';

$_LANG['nav'] = '导航栏';
$_LANG['index_new'] = '最新';
$_LANG['index_best'] = '精品';
$_LANG['index_hot'] = '热门';

$_LANG['back_list'] = '返回分类列表';
$_LANG['continue_add'] = '继续添加分类';
$_LANG['category_separate'] = '分离平台分类';

$_LANG['notice_style'] = '您可以为每一个商品分类指定一个样式表文件。例如文件存放在 themes 目录下则输入：themes/style.css';

/* 操作提示信息 */
$_LANG['catname_empty'] = '分类名称不能为空!';
$_LANG['catname_exist'] = '已存在相同的分类名称!';
$_LANG["parent_isleaf"] = '所选分类不能是末级分类!';
$_LANG["cat_isleaf"] = '不是末级分类或者此分类下还存在有商品,您不能删除!';
$_LANG["cat_noleaf"] = '底下还有其它子分类,不能修改为末级分类!';
$_LANG["is_leaf_error"] = '所选择的上级分类不能是当前分类或者当前分类的下级分类!';
$_LANG['grade_error'] = '价格分级数量只能是0-10之内的整数';

$_LANG['catadd_succed'] = '新商品分类添加成功!';
$_LANG['catedit_succed'] = '商品分类编辑成功!';
$_LANG['catdrop_succed'] = '商品分类删除成功!';
$_LANG['catremove_succed'] = '商品分类转移成功!';
$_LANG['move_cat_success'] = '转移商品分类已成功完成!';

$_LANG['cat_move_desc'] = '什么是转移商品分类?';
$_LANG['select_source_cat'] = '选择要转移的分类';
$_LANG['select_target_cat'] = '选择目标分类';
$_LANG['source_cat'] = '从此分类';
$_LANG['target_cat'] = '转移到';
$_LANG['start_move_cat'] = '开始转移';
$_LANG['cat_move_notic'] = '在添加商品或者在商品管理中,如果需要对商品的分类进行变更,那么你可以通过此功能,正确管理你的商品分类。';

$_LANG['cat_move_empty'] = '你没有正确选择商品分类!';

$_LANG['sel_goods_type'] = '请选择商品类型';
$_LANG['sel_filter_attr'] = '请选择筛选属性';
$_LANG['filter_attr'] = '筛选属性';
$_LANG['filter_attr_notic'] = '筛选属性可在前分类页面筛选商品';
$_LANG['filter_attr_not_repeated'] = '筛选属性不可重复';
$_LANG['phone_icon_notic'] = '（注：手机端专用,建议上传正方形图片（100*100））';
$_LANG['parent_id_notic'] = '不选择分类默认为顶级分类';

/*JS 语言项*/
$_LANG['js_languages']['catname_empty'] = '分类名称不能为空!';
$_LANG['js_languages']['unit_empyt'] = '数量单位不能为空!';
$_LANG['js_languages']['is_leafcat'] = '您选定的分类是一个末级分类。\r\n新分类的上级分类不能是一个末级分类';
$_LANG['js_languages']['not_leafcat'] = '您选定的分类不是一个末级分类。\r\n商品的分类转移只能在末级分类之间才可以操作。';
$_LANG['js_languages']['filter_attr_not_repeated'] = '筛选属性不可重复';
$_LANG['js_languages']['filter_attr_not_selected'] = '请选择筛选属性';
$_LANG['show_category_top'] = '是否在顶级分类页显示';
$_LANG['show_seller_nav'] = '显示店铺导航栏';
$_LANG['js_languages']['grade_min_0'] = '价格区间不能小于0';
$_LANG['js_languages']['grade_max_10'] = '价格区间不能大于10';
$_LANG['js_languages']['category_top_use_null'] = '该分类必须是顶级分类才能使用!';
$_LANG['js_languages']['is_delete_info'] = '确实要删除该信息吗!';
$_LANG['js_languages']['delete_fail'] = '删除失败';
$_LANG['js_languages']['catename_level_transfer_yes'] = '执行此操作时，当前分类所有下级分类也同时转移，确定执行吗？';

/*大商创1.5新增 sunle*/
$_LANG['category_name_notic'] = '如需批量添加分类，格式：分类名称一,分类名称二,分类名称三 （注：以英文逗号分隔开）';
$_LANG['show_category_top'] = '是否在顶级分类页显示';
$_LANG['show_category_top_css'] = '是否使用顶级分类页样式';
$_LANG['show_seller_nav'] = '显示店铺导航栏';
$_LANG['category_herf'] = '分类跳转链接';
$_LANG['category_herf_notic'] = '请根据分类名称顺序填写链接地址，每行一条；此功能只支持PC端。';
$_LANG['see_zj_list'] = '查看证件列表';
$_LANG['no_permission'] = '暂无权限';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['list'][0] = '展示了店铺所有的商品分类';
$_LANG['operation_prompt_content']['list'][1] = '可查看下一级分类。';
$_LANG['operation_prompt_content']['list'][2] = '鼠标移动“设置”位置，可新增下一级分类、查看下一级分类、转移商品等操作。';
$_LANG['operation_prompt_content']['list'][4] = '<span class="red">设置分类显示会同步操作分类及子分类下所有商品是否显示，请谨慎操作</span>';

$_LANG['operation_prompt_content']['info'][0] = '请按提示信息填写每一个字段。';

return $_LANG;
