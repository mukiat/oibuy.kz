<?php

$_LANG['lab_integral'] = '积分购买金额：';
$_LANG['lab_give_integral'] = '赠送消费积分数：';
$_LANG['lab_rank_integral'] = '赠送成长值数：';

//ecmoban模板堂 --zhuo start 仓库
$_LANG['warehouse'] = '仓库';
$_LANG['tab_warehouse_model'] = '仓库模式';
$_LANG['tab_warehouse'] = '仓库库存';
$_LANG['warehouse_name'] = '仓库名称';
$_LANG['warehouse_number'] = '仓库库存';
$_LANG['warehouse_price'] = '仓库价格';
$_LANG['rank_volume'] = '优惠等级';
$_LANG['attr_type'] = '颜色类型';
$_LANG['size_type'] = '尺码类型';
$_LANG['warehouse_promote_price'] = '仓库促销价格';
$_LANG['drop_warehouse'] = '您确实要删除该仓库库存吗？';
$_LANG['drop_warehouse_area'] = '您确实要删除该仓库地区价格吗？';

//地区价格模式
$_LANG['warehouse_region_name'] = '地区名称';
$_LANG['warehouse_region_model'] = '地区模式';
$_LANG['belongs_to_warehouse'] = '所属仓库';
$_LANG['region_price'] = '地区价格';
$_LANG['region_number'] = '地区库存';
$_LANG['region_promote_price'] = '地区促销价格';
$_LANG['region_sort'] = '排序';

$_LANG['goods_info'] = '返回商品详情页';
$_LANG['tab_goodsModel'] = '设置商品模式';

$_LANG['tab_areaRegion'] = '关联地区';
//ecmoban模板堂 --zhuo end 仓库

//ecmoban模板堂 --zhuo start
$_LANG['warehouse_spec_price'] = '仓库属性价格';
$_LANG['area_spec_price'] = '地区属性价格';
$_LANG['add_attr_img'] = '添加属性图片';
$_LANG['confirm_drop_img'] = '你确认要删除该图标吗？';
$_LANG['drop_attr_img'] = '删除图标';
$_LANG['drop_attr_img_success'] = '删除属性图片成功';
$_LANG['add_brand_success'] = '添加品牌成功！';
$_LANG['add_brand_fail'] = '添加品牌异常！';
$_LANG['brand_name_repeat'] = '品牌名称重复！';

$_LANG['edit_freight_template_success'] = '编辑运费模板成功！';
$_LANG['article_title_repeat'] = '文章标题重复！';
$_LANG['add_article_success'] = '添加文章成功！';
$_LANG['add_article_fail'] = '添加文章异常！';

$_LANG['region_name_not_null'] = '地区名称不能为空！';
$_LANG['region_name_repeat'] = '地区名称重复！';
$_LANG['add_region_fail'] = '添加地区异常！';

$_LANG['cat_prompt_notic_one'] = '平台最多只能设置三级分类';
$_LANG['cat_prompt_notic_two'] = '您目前的权限只能添加四级分类';
$_LANG['cat_prompt_file_size'] = '上传图片不得大于200kb';
$_LANG['cat_prompt_file_type'] = '请上传jpg,gif,png,jpeg格式图片';
$_LANG['commission_rate_prompt'] = '佣金比率为0-100以内，请重新设置';

$_LANG['cate_name_not_repeat'] = '同级别下不能有重复的分类名称！';
$_LANG['price_section_range'] = '价格区间数超过范围！';
$_LANG['cat_add_success'] = '分类添加成功！';
//ecmoban模板堂 --zhuo end

/*------------------------------------------------------ */
//-- 商品相册  by kong  start
/*------------------------------------------------------ */

$_LANG['img_count'] = '图片排序';
$_LANG['img_url'] = '上传文件';
$_LANG['img_file'] = '图片外部链接地址';
$_LANG['remind'] = '友情提醒：请勿同时上传本地图片与外部链接图片！';

//办事处
$_LANG['label_region'] = '管辖地区';

//大商创1.5版本新增
$_LANG['category_name'] = '分类名称';
$_LANG['brand_name'] = '品牌名称';

//智能权重语言包
$_LANG['intelligent_goods_name'] = '商品名称：';
$_LANG['intelligent_goods_number'] = '商品购买数量';
$_LANG['intelligent_return_number'] = '商品退换货数量';
$_LANG['intelligent_user_number'] = '购买此商品的会员数量';
$_LANG['intelligent_goods_comment_number'] = '对此商品评价数量';
$_LANG['intelligent_merchants_comment_number'] = '对此商品的商家评价数量';
$_LANG['intelligent_user_attention_number'] = '会员关注此商品数量';
$_LANG['intelligent_manual_intervention'] = '人工干预值';

$_LANG['intelligent_notice'][0] = '注意：1、智能权重统计购买数量必须是会员“确认收货”后的订单。';
$_LANG['intelligent_notice'][1] = '2、智能权重默认不会统计出之前的老数据，在统计过一次后会把老数据统计出来，升级用户会看到第一次统计值不一致，是因为把老数据加载出来了。';
$_LANG['intelligent_notice'][2] = '3、商品权重值计算公式：商品权重值 = 商品购买数量 - 商品退换货数量 + 购买此商品的会员数量 + 对商品评价数量 + 对商品的商家评价数量 + 会员关注此商品数量 + 人工干预值';

return $_LANG;
