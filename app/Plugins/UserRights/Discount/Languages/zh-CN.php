<?php

/**
 * 语言包配置用例：
 * & 通用部分
 * 1. 名称：取config.php code值 作语言包key值。例如：affiliate
 * 2. 描述：取config.php description值 作语言包key值。例如：affiliate_desc
 * & 自定义权益配置部分
 * 1. 权益配置项名称  取config.php rights_configure name值 作语言包key值。例如：level_1
 * 2. 权益配置项值 （支持 text、select、radiobox） 取config.php rights_configure name值 作语言包key值。例如：level_1; 若值是单选或多选 则需格式为数组 level_1_range = [0 => '否',1 => '是']
 * 3. 权益配置项描述  取config.php rights_configure name值 拼接 _desc 作语言包key值。例如：level_3_desc
 * 4. 权益配置项单位  取config.php rights_configure name值 拼接 _unit 作语言包key值。例如：expire_unit 需要格式为数组 expire_unit = ['yuan' => '元']
 *
 */

return [
    'discount' => '会员特价',
    'discount_desc' => '不同等级会员，购买商品享受优惠折扣',

    'user_discount' => '享受折扣率',
    'user_discount_unit' => ['per' => '%'],
    'user_discount_desc' => '设置对应会员等级的折扣率，用户达到对应等级后，可享受相应的折扣优惠'
];
