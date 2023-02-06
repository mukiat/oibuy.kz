<?php

use App\Repositories\Common\DscRepository;

app(DscRepository::class)->pluginsLang('Express/', __DIR__);

return [
    'code' => 'kuaidiniao', // code
    // 描述对应的语言项
    'description' => 'kuaidiniao_desc',
    'version' => '1.0', // 版本号
    // 网址
    'website' => 'http://www.kdniao.com/',
    'sort' => '1', // 默认排序
    // 配置
    'express_configure' => [
        ['name' => 'customer', 'type' => 'text', 'value' => ''],
        ['name' => 'key', 'type' => 'text', 'value' => '', 'encrypt' => true],
    ]
];
