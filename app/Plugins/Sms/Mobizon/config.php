<?php

use App\Repositories\Common\DscRepository;

app(DscRepository::class)->pluginsLang('Sms/', __DIR__);

return [
    'code' => 'mobizon', // code
    // 描述对应的语言项
    'description' => 'mobizon_desc',
    'version' => '1.0', // 版本号
    // 网址
    'website' => 'https://www.mobizon.kz/',
    'sort' => '3', // 默认排序
    // 配置
    'sms_configure' => [
        ['name' => 'sms_api_key', 'type' => 'text', 'value' => ''],
    ]
];
