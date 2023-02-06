<?php

use App\Repositories\Common\DscRepository;

app(DscRepository::class)->pluginsLang('Cron/', __DIR__);

return [
    'code' => 'messtoseller',
    'desc' => 'messtoseller_desc',
    'author' => 'Dscmall Team',
    'website' => 'http://www.ecmoban.com',
    'version' => '1.0.0',
    'config' => [
        ['name' => 'auto_mess_to_seller_count', 'type' => 'select', 'value' => '5'],
        ['name' => 'auto_mess_to_seller_day', 'type' => 'select', 'value' => '1']
    ]
];
