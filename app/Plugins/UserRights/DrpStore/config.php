<?php

use App\Repositories\Common\DscRepository;

app(DscRepository::class)->pluginsLang('UserRights/', __DIR__);

return [
    'code' => 'drp_store', // code
    // 描述对应的语言项
    'description' => 'drp_store_desc',
    // 默认icon 图标
    'icon' => 'assets/user_rights/img/drp_store.png',// 默认icon 图标
    'trigger_point' => 'manual',
    'version' => '1.0', // 版本号
    'sort' => '1', // 默认排序
    'group' => 'store', // 用于后台分组展示
    'support_module' => 'drp', // 支持的模块才显示 如 drp 分销模块
    // 权益配置
    'rights_configure' => [
        ['name' => 'store_audit', 'type' => 'radiobox', 'value' => 0]
    ]
];
