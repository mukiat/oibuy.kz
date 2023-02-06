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
    'drp_store' => '微店',
    'drp_store_desc' => '开启后，拥有专属的微店及推广二维码',

    'store_audit' => '开店审核',
    'store_audit_range' => [0 => '关闭', 1 => '开启'],
    'store_audit_desc' => '默认开启审核，开启后，VIP会员申请开店必须通过审核后才可开通店铺',

];
