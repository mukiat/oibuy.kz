<?php

use App\Repositories\Common\DscRepository;

app(DscRepository::class)->pluginsLang('UserRights/', __DIR__);

return [
    'code' => 'customer', // code
    // 描述对应的语言项
    'description' => 'customer_desc',
    // 默认icon 图标
    'icon' => 'assets/user_rights/img/customer.png',// 默认icon 图标
    'trigger_point' => '',
    'version' => '1.0', // 版本号
    'sort' => '50', // 默认排序
    'group' => 'service', // 用于后台分组展示
    'support_module' => 'drp', // 支持的模块才显示 如 drp 分销模块
    // 权益配置
    'rights_configure' => [
        ['name' => 'telephone', 'type' => 'text', 'value' => ''],
    ]
];
