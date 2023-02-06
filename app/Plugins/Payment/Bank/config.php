<?php

use App\Repositories\Common\DscRepository;

app(DscRepository::class)->pluginsLang('Payment', __DIR__);

return [
    // 代码
    'code' => 'bank',

    // 描述对应的语言项
    'desc' => 'bank_desc',

    // 配置信息
    'config' => [
        ['name' => 'bank_payee_name', 'type' => 'text', 'value' => ''],
        ['name' => 'bank_card_number', 'type' => 'text', 'value' => ''],
        ['name' => 'bank_deposit_bank', 'type' => 'text', 'value' => ''],
        ['name' => 'bank_branch', 'type' => 'text', 'value' => ''],
        ['name' => 'bank_explain', 'type' => 'text', 'value' => ''],
    ]
];
