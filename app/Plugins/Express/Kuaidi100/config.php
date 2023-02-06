<?php

use App\Repositories\Common\DscRepository;

app(DscRepository::class)->pluginsLang('Express/', __DIR__);

return [
    'code' => 'kuaidi100', // code
    // 描述对应的语言项
    'description' => 'kuaidi100_desc',
    'version' => '1.0', // 版本号
    // 网址
    'website' => 'https://www.kuaidi100.com/',
    'sort' => '1', // 默认排序
    // 配置
    'express_configure' => [
        ['name' => 'customer', 'type' => 'text', 'value' => ''],
        ['name' => 'key', 'type' => 'text', 'value' => '', 'encrypt' => true],
    ]
];
