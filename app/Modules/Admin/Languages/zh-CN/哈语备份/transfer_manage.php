<?php

$_LANG['Import_data_remind'] = "请选择需要导入模块数据！";

$_LANG['set_up_config'] = "请配置源站点数据库信息";
$_LANG['select_method'] = '选择商品的方式：';
$_LANG['by_cat'] = '根据商品分类、品牌';
$_LANG['by_sn'] = '根据商品货号';
$_LANG['select_cat'] = '选择商品分类：';
$_LANG['select_brand'] = '选择商品品牌：';
$_LANG['goods_list'] = '商品列表：';
$_LANG['src_list'] = '待选列表：';
$_LANG['dest_list'] = '选定列表：';
$_LANG['input_sn'] = '输入商品货号：<br />（每行一个）';
$_LANG['edit_method'] = '编辑方式：';
$_LANG['edit_each'] = '逐个编辑';
$_LANG['edit_all'] = '统一编辑';
$_LANG['go_edit'] = '进入编辑';
$_LANG['error_transfer'] = '处理 %s 表时，出现一下错误：%s 字段';

$_LANG['notice_edit'] = '会员价格为-1表示会员价格将根据会员等级折扣比例计算';

$_LANG['goods_class'] = '商品类别';
$_LANG['g_class'][G_REAL] = '实体商品';
$_LANG['g_class'][G_CARD] = '虚拟卡';

$_LANG['goods_sn'] = '货号';
$_LANG['goods_name'] = '商品名称';
$_LANG['market_price'] = '市场价格';
$_LANG['shop_price'] = '本店价格';
$_LANG['integral'] = '积分购买';
$_LANG['give_integral'] = '赠送积分';
$_LANG['goods_number'] = '库存';
$_LANG['brand'] = '品牌';
$_LANG['transfer_manage'] = '源站点信息设置';
$_LANG['select_file_template'] = '请选择导入模块';
$_LANG['category_manage'] = '分类管理';
$_LANG['user_manage'] = '会员管理';
$_LANG['seller_list_info'] = '商家列表信息';
$_LANG['order_list_info'] = '订单列表信息';
$_LANG['order_goods_list'] = '订单商品列表';
$_LANG['attr_list'] = '属性列表';
$_LANG['exec'] = '执行程序';
$_LANG['silent_1'] = '出错时忽略错误,继续执行程序';
$_LANG['silent_0'] = '出错时立即提示，并中止程序';

$_LANG['batch_edit_ok'] = '批量修改成功';
$_LANG['manage_date_import'] = '管理数据导入';
$_LANG['import_success'] = '导入成功';

$_LANG['nav_list'] = '商品列表|goods.php?act=list,订单列表|order.php?act=list,用户评论|comment_manage.php?act=list,会员列表|users.php?act=list,商店设置|shop_config.php?act=list_edit';

$_LANG['export_format'] = '数据格式';
$_LANG['export_dscmall'] = 'dscmall支持数据格式';
$_LANG['goods_cat'] = '所属分类：';
$_LANG['csv_file'] = '上传批量csv文件：';
$_LANG['notice_file'] = '（CSV文件中一次上传商品数量最好不要超过1000，CSV文件大小最好不要超过500K.）';
$_LANG['file_charset'] = '文件编码：';
$_LANG['download_file'] = '下载批量CSV文件（%s）';
$_LANG['use_help'] = '使用说明：' .
    '<ol>' .
    '<li>迁移数据应注意，是否需要保留本站数据信息；选择不保留时，导入这部分数据时将会覆盖相应表数据；<br />' .
    '如保留本站数据时，导入的数据将是最新添加；序号及其他关联表信息不能对应上。<br />' .
    '</ol>';

$_LANG['js_languages']['please_select_goods'] = '请您选择商品';
$_LANG['js_languages']['please_input_sn'] = '请您输入商品货号';
$_LANG['js_languages']['goods_cat_not_leaf'] = '请选择底级分类';
$_LANG['js_languages']['please_select_cat'] = '请您选择所属分类';
$_LANG['js_languages']['please_upload_file'] = '请您上传批量csv文件';

// 批量上传商品的字段
$_LANG['upload_goods']['goods_id'] = '商品ID';
$_LANG['upload_goods']['cat_id'] = '分类ID';
$_LANG['upload_goods']['user_id'] = '会员ID';
$_LANG['upload_goods']['goods_sn'] = '商品货号';
$_LANG['upload_goods']['goods_name'] = '商品名称';
$_LANG['upload_goods']['goods_name_style'] = '商品名称显示的样式';
$_LANG['upload_goods']['click_count'] = '商品点击数';
$_LANG['upload_goods']['brand_id'] = '商品品牌';
$_LANG['upload_goods']['provider_name'] = '供货人的名称';
$_LANG['upload_goods']['goods_number'] = '库存数量';
$_LANG['upload_goods']['goods_weight'] = '商品重量（kg）';
$_LANG['upload_goods']['market_price'] = '市场售价';
$_LANG['upload_goods']['shop_price'] = '本店售价';
$_LANG['upload_goods']['promote_price'] = '促销价格';
$_LANG['upload_goods']['promote_start_date'] = '促销价格开始日期';
$_LANG['upload_goods']['promote_end_date'] = '促销结束日期';
$_LANG['upload_goods']['warn_number'] = '商品报警数量';
$_LANG['upload_goods']['keywords'] = '商品关键字';
$_LANG['upload_goods']['goods_brief'] = '商品的简短描述';
$_LANG['upload_goods']['goods_desc'] = '商品的详细描述';
$_LANG['upload_goods']['goods_thumb'] = '商品微缩图片';
$_LANG['upload_goods']['goods_img'] = '商品的实际大小图片';
$_LANG['upload_goods']['original_img'] = '商品的原始图片';
$_LANG['upload_goods']['is_real'] = '是否是实物';
$_LANG['upload_goods']['extension_code'] = '商品的扩展属性';
$_LANG['upload_goods']['is_on_sale'] = '商品是否开放销售';
$_LANG['upload_goods']['is_alone_sale'] = '是否能单独销售';
$_LANG['upload_goods']['is_shipping'] = '是否免运费';
$_LANG['upload_goods']['integral'] = '积分购买额度';
$_LANG['upload_goods']['add_time'] = '添加时间';
$_LANG['upload_goods']['sort_order'] = '显示顺序';
$_LANG['upload_goods']['is_delete'] = '商品是否已经删除';
$_LANG['upload_goods']['is_best'] = '是否是精品';
$_LANG['upload_goods']['is_new'] = '是否是新品';
$_LANG['upload_goods']['is_hot'] = '是否热销';
$_LANG['upload_goods']['is_promote'] = '是否特价促销';
$_LANG['upload_goods']['bonus_type_id'] = '购买该商品所能领到的红包类型';
$_LANG['upload_goods']['last_update'] = '最近一次更新商品配置的时间';
$_LANG['upload_goods']['goods_type'] = '商品所属类型id';
$_LANG['upload_goods']['seller_note'] = '商品的商家备注';
$_LANG['upload_goods']['give_integral'] = '购买该商品时每笔成功交易赠送的积分数量';
$_LANG['upload_goods']['rank_integral'] = '成长值';
$_LANG['upload_goods']['suppliers_id'] = '供货商ID';
$_LANG['upload_goods']['is_check'] = '是否审核';

//批量上传分类
$_LANG['upload_category']['cat_id'] = '分类ID';
$_LANG['upload_category']['cat_name'] = '分类名称';
$_LANG['upload_category']['keywords'] = '分类关键词';
$_LANG['upload_category']['cat_desc'] = '分类描述';
$_LANG['upload_category']['parent_id'] = '分类父id';
$_LANG['upload_category']['sort_order'] = '显示排序';
$_LANG['upload_category']['template_file'] = '该分类的单独模板文件的名字';
$_LANG['upload_category']['measure_unit'] = '该分类的计量单位';
$_LANG['upload_category']['show_in_nav'] = '是否显示在导航栏';
$_LANG['upload_category']['style'] = '该分类的单独的样式表的包括文件名部分的文件路径';
$_LANG['upload_category']['is_show'] = '是否在前台页面显示';
$_LANG['upload_category']['grade'] = '该分类的最高和最低价之间的价格分级';
$_LANG['upload_category']['filter_attr'] = '属性筛选';

//批量上传会员
$_LANG['upload_users']['user_id'] = '用户ID';
$_LANG['upload_users']['email'] = '会员邮箱';
$_LANG['upload_users']['user_name'] = '用户名';
$_LANG['upload_users']['password'] = '用户密码';
$_LANG['upload_users']['question'] = '安全问题答案';
$_LANG['upload_users']['answer'] = '安全问题';
$_LANG['upload_users']['sex'] = '性别';
$_LANG['upload_users']['birthday'] = '生日';
$_LANG['upload_users']['user_money'] = '用户现有资金';
$_LANG['upload_users']['frozen_money'] = '用户冻结资金';
$_LANG['upload_users']['pay_points'] = '消费积分';
$_LANG['upload_users']['rank_points'] = '会员成长值';
$_LANG['upload_users']['address_id'] = '收货信息id';
$_LANG['upload_users']['reg_time'] = '注册时间';
$_LANG['upload_users']['last_login'] = '最后一次登录时间';
$_LANG['upload_users']['last_time'] = '应该是最后一次修改信息时间';
$_LANG['upload_users']['last_ip'] = '最后一次登录ip';
$_LANG['upload_users']['visit_count'] = '登录次数';
$_LANG['upload_users']['user_rank'] = '会员等级id';
$_LANG['upload_users']['is_special'] = '特殊用户';
$_LANG['upload_users']['salt'] = '登录标识';
$_LANG['upload_users']['parent_id'] = '推荐人会员id';
$_LANG['upload_users']['flag'] = '标记';
$_LANG['upload_users']['alias'] = '昵称';
$_LANG['upload_users']['msn'] = 'msn';
$_LANG['upload_users']['qq'] = 'qq';
$_LANG['upload_users']['office_phone'] = '办公电话';
$_LANG['upload_users']['home_phone'] = '家庭电话';
$_LANG['upload_users']['mobile_phone'] = '手机';
$_LANG['upload_users']['is_validated'] = '邮箱是否验证';
$_LANG['upload_users']['credit_line'] = '信用额度';
$_LANG['upload_users']['passwd_question'] = '密码问题';
$_LANG['upload_users']['passwd_answer'] = '密码答案';

//批量上传订单列表
$_LANG['upload_order_info']['order_id'] = '';
$_LANG['upload_order_info']['main_order_id'] = '';
$_LANG['upload_order_info']['order_sn'] = '';
$_LANG['upload_order_info']['user_id'] = '';
$_LANG['upload_order_info']['order_status'] = '';
$_LANG['upload_order_info']['shipping_status'] = '';
$_LANG['upload_order_info']['pay_status'] = '';
$_LANG['upload_order_info']['consignee'] = '';
$_LANG['upload_order_info']['country'] = '';
$_LANG['upload_order_info']['province'] = '';
$_LANG['upload_order_info']['city'] = '';
$_LANG['upload_order_info']['district'] = '';
$_LANG['upload_order_info']['address'] = '';
$_LANG['upload_order_info']['zipcode'] = '';
$_LANG['upload_order_info']['tel'] = '';
$_LANG['upload_order_info']['mobile'] = '';
$_LANG['upload_order_info']['email'] = '';
$_LANG['upload_order_info']['best_time'] = '';
$_LANG['upload_order_info']['sign_building'] = '';
$_LANG['upload_order_info']['postscript'] = '';
$_LANG['upload_order_info']['shipping_id'] = '';
$_LANG['upload_order_info']['shipping_name'] = '';
$_LANG['upload_order_info']['pay_id'] = '';
$_LANG['upload_order_info']['pay_name'] = '';
$_LANG['upload_order_info']['how_oos'] = '';
$_LANG['upload_order_info']['how_surplus'] = '';
$_LANG['upload_order_info']['pack_name'] = '';
$_LANG['upload_order_info']['card_name'] = '';
$_LANG['upload_order_info']['card_message'] = '';
$_LANG['upload_order_info']['inv_payee'] = '';
$_LANG['upload_order_info']['inv_content'] = '';
$_LANG['upload_order_info']['goods_amount'] = '';
$_LANG['upload_order_info']['shipping_fee'] = '';
$_LANG['upload_order_info']['insure_fee'] = '';
$_LANG['upload_order_info']['pay_fee'] = '';
$_LANG['upload_order_info']['pack_fee'] = '';
$_LANG['upload_order_info']['card_fee'] = '';
$_LANG['upload_order_info']['money_paid'] = '';
$_LANG['upload_order_info']['surplus'] = '';
$_LANG['upload_order_info']['integral'] = '';
$_LANG['upload_order_info']['integral_money'] = '';
$_LANG['upload_order_info']['bonus'] = '';
$_LANG['upload_order_info']['order_amount'] = '';
$_LANG['upload_order_info']['from_ad'] = '';
$_LANG['upload_order_info']['referer'] = '';
$_LANG['upload_order_info']['add_time'] = '';
$_LANG['upload_order_info']['confirm_time'] = '';
$_LANG['upload_order_info']['pay_time'] = '';
$_LANG['upload_order_info']['shipping_time'] = '';
$_LANG['upload_order_info']['pack_id'] = '';
$_LANG['upload_order_info']['card_id'] = '';
$_LANG['upload_order_info']['bonus_id'] = '';
$_LANG['upload_order_info']['invoice_no'] = '';
$_LANG['upload_order_info']['extension_code'] = '';
$_LANG['upload_order_info']['extension_id'] = '';
$_LANG['upload_order_info']['to_buyer'] = '';
$_LANG['upload_order_info']['pay_note'] = '';
$_LANG['upload_order_info']['agency_id'] = '';
$_LANG['upload_order_info']['inv_type'] = '';
$_LANG['upload_order_info']['tax'] = '';
$_LANG['upload_order_info']['is_separate'] = '';
$_LANG['upload_order_info']['parent_id'] = '';
$_LANG['upload_order_info']['discount'] = '';

//批量上传订单商品表
$_LANG['upload_order_goods']['rec_id'] = '';
$_LANG['upload_order_goods']['order_id'] = '';
$_LANG['upload_order_goods']['goods_id'] = '';
$_LANG['upload_order_goods']['goods_name'] = '';
$_LANG['upload_order_goods']['goods_sn'] = '';
$_LANG['upload_order_goods']['goods_number'] = '';
$_LANG['upload_order_goods']['market_price'] = '';
$_LANG['upload_order_goods']['goods_price'] = '';
$_LANG['upload_order_goods']['goods_attr'] = '';
$_LANG['upload_order_goods']['send_number'] = '';
$_LANG['upload_order_goods']['is_real'] = '';
$_LANG['upload_order_goods']['extension_code'] = '';
$_LANG['upload_order_goods']['parent_id'] = '';
$_LANG['upload_order_goods']['is_gift'] = '';
$_LANG['upload_order_goods']['goods_attr_id'] = '';
$_LANG['upload_order_goods']['ru_id'] = '';

//批量上传商品类型
$_LANG['upload_goods_type']['cat_id'] = '';
$_LANG['upload_goods_type']['cat_name'] = '';
$_LANG['upload_goods_type']['enabled'] = '';
$_LANG['upload_goods_type']['attr_group'] = '';

//批量上传属性列表
$_LANG['upload_attribute']['attr_id'] = '';
$_LANG['upload_attribute']['cat_id'] = '';
$_LANG['upload_attribute']['attr_name'] = '';
$_LANG['upload_attribute']['attr_input_type'] = '';
$_LANG['upload_attribute']['attr_type'] = '';
$_LANG['upload_attribute']['attr_values'] = '';
$_LANG['upload_attribute']['attr_index'] = '';
$_LANG['upload_attribute']['sort_order'] = '';
$_LANG['upload_attribute']['is_linked'] = '';
$_LANG['upload_attribute']['attr_group'] = '';

//批量上传文章列表
$_LANG['upload_article']['article_id'] = '文章ID';
$_LANG['upload_article']['cat_id'] = '文章的分类ID';
$_LANG['upload_article']['title'] = '标题';
$_LANG['upload_article']['content'] = '内容';
$_LANG['upload_article']['author'] = '作者';
$_LANG['upload_article']['author_email'] = '作者邮箱';
$_LANG['upload_article']['keywords'] = '文章的关键字';
$_LANG['upload_article']['article_type'] = '文章类型';
$_LANG['upload_article']['is_open'] = '是否显示';
$_LANG['upload_article']['add_time'] = '文章添加时间';
$_LANG['upload_article']['file_url'] = '上传文件或者外部文件的url';
$_LANG['upload_article']['open_type'] = '连接地址等于file_url的值';
$_LANG['upload_article']['link'] = '文章标题所引用的连接';
$_LANG['upload_article']['description'] = '描述';

//批量上传文章分类
$_LANG['upload_article_cat']['cat_id'] = '分类ID';
$_LANG['upload_article_cat']['cat_name'] = '分类名称';
$_LANG['upload_article_cat']['cat_type'] = '分类类型';
$_LANG['upload_article_cat']['keywords'] = '关键词';
$_LANG['upload_article_cat']['cat_desc'] = '分类说明';
$_LANG['upload_article_cat']['sort_order'] = '排序';
$_LANG['upload_article_cat']['show_in_nav'] = '是否在导航栏显示';
$_LANG['upload_article_cat']['parent_id'] = '父节点id，取值于该表cat_id字段';

$_LANG['batch_upload_ok'] = '批量上传成功';
$_LANG['goods_upload_confirm'] = '批量上传确认';

$_LANG['notes'] = "图片批量处理允许您重新生成商品的缩略图以及重新添加水印。<br />该处理过程可能会比较慢，请您耐心等候。";
$_LANG['change_link'] = '为处理后图片生成新链接';
$_LANG['yes_change'] = '新生成图片使用新名称，并删除旧图片';
$_LANG['do_album'] = '处理商品相册';
$_LANG['do_icon'] = '处理商品图片';
$_LANG['all_goods'] = '所有商品';
$_LANG['action_notice'] = '请选上“处理商品相册”或“处理商品图片”';
$_LANG['no_change'] = '新生成图片覆盖旧图片';
$_LANG['thumb'] = '重新生成缩略图';
$_LANG['watermark'] = '重新生成商品详情图';
$_LANG['page'] = '页数';
$_LANG['total'] = '总页数';
$_LANG['time'] = '处理时间';
$_LANG['wait'] = '正在处理.....';
$_LANG['page_format'] = '第 %d 页';
$_LANG['total_format'] = '共 %d 页';
$_LANG['time_format'] = '耗时 %s 秒';
$_LANG['goods_format'] = '商品图片共 %d 张，每页处理 %d 张';
$_LANG['gallery_format'] = '商品相册图片共 %d 张，每页处理 %d 张';

$_LANG['done'] = '图片批量处理成功';
$_LANG['error_pos'] = '在处理商品ID为 %s 的商品图片时发生以下错误：';
$_LANG['error_rename'] = '无法将文件 %s 重命名为 %s';

$_LANG['js_languages']['no_action'] = '你没选择任何操作';

$_LANG['silent'] = '出错时忽略错误,继续执行程序';
$_LANG['no_silent'] = '出错时立即提示，并中止程序';

$_LANG['data_migration'] = '数据迁移';
$_LANG['data_host'] = '数据库主机';
$_LANG['data_host_notic'] = '示例：127.0.0.1';
$_LANG['port'] = '端口';
$_LANG['port_notic'] = '示例：3306';
$_LANG['user_notic'] = '示例：root';
$_LANG['pwd'] = '密码';
$_LANG['pwd_notic'] = '示例：123456';
$_LANG['database_name'] = '数据库名';
$_LANG['database_name_notic'] = '示例：ecmoban_dsc';
$_LANG['s_db_prefix'] = '表前缀';
$_LANG['s_db_prefix_notic'] = '示例：dsc_  (说明：比如表dsc_users)';
$_LANG['s_db_retain'] = '本站数据表数据是否保留';
$_LANG['get_sql_basic'] = '检测数据库连接';
$_LANG['ws_zhandian_set'] = '请完善源站点配置信息！';

$_LANG['databases_message_one'] = '连接 数据库失败，请检查您输入的 数据库帐号 是否正确。';
$_LANG['databases_message_two'] = '连接 数据库失败，请检查您输入的 数据库名称 是否存在。';
$_LANG['databases_message_three'] = '连接数据库成功！';

$_LANG['seller_apply_info'] = '商家入驻信息';

$_LANG['operator'] = '操作员：【';
$_LANG['operator_two'] = '】，订单退款【';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['info'][0] = '获取转移的数据库表内容。';

$_LANG['operation_prompt_content']['config'][0] = '设置导入来源网站数据库配置信息';

return $_LANG;
