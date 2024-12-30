<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Order Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Order for various
    | messages. You are free to modify
    | these language lines according to your application's requirements.
    |
    */
    'edit_pay_status' => 'Төлем күйін өзгерту,бұрынғы төлем түрі：%s',
    'pay_status' => 'Төлем күйі',
    'pay_success' => 'Төлем сәтті орындалды,тез арада тауар жіберіледі',
    'pay_fail' => 'Төлем сәтсіз,бізбен хабарласыңыз',
    'pay_disabled' => 'Сіз таңдаған төлем жолы тоқтатылған',
    'pay_invalid' => 'Сіз жарамсыз төлем жолын таңдадыңыз,бұл төлем түрі жоқ н/е тоқтатылған,бізбен хабарласыңыз.',

    'receipt_code_order' => 'Төлем код заказы',
    'seller_automatic_settlement' => 'Төлем коды сатушының есебін автоматты жасайды',
    'return_online' => 'Онлайн төлемді қайтару',
    'order_return_prompt' => 'Төлем қайтарылды,заказды қайтару ',
    'buy_integral' => ' Сатып алған бонус',
    'order_return_running_number' => 'Қайтару-сериялық нөмір',
    'merchant_handle_recharge' => 'Шот толтыру,атқарушы：Сатушы онлайн төлем',
    'order_remark' => "【Заказ】%s",
    'order_number' => 'Заказ саны',
    'individual' => 'тал',
    'order_individual_count' => 'Заказ саны',
    'sale_money' => 'Сату сомасы',
    'money_unit' => ' тг',
    'not_audited' => 'Тексерілмеді',
    'audited_yes_adopt' => 'Тексеруден өтті',
    'audited_not_adopt' => 'Тексеруден өтпеді',
    'goods_salevolume' => 'Сатылым',
    'max_value' => 'Max',
    'min_value' => 'Min',
    'average_value' => 'Орташа',
    'order_amount' => 'Заказ сомасы',
    'payorder_goods_number' => 'Заказ тауар саны',
    'seller_area_distribution' => 'Дүкен таралу аймағы',
    'order_confirm_receipt' => 'Бұл заказ жеткізілінді',

    /* Заказ күйі */
    'os' => [
        OS_UNCONFIRMED => 'Расталмаған',
        OS_CONFIRMED => 'Расталған',
        OS_CANCELED => 'Болдырмау',
        OS_INVALID => 'Жарамсыз',
        OS_RETURNED => 'Қайтару',
        OS_SPLITED => 'Бөлінді',
        OS_SPLITING_PART => 'Ішнара бөлінді',
        OS_RETURNED_PART => 'Ішнара қайтарылды',
        OS_ONLY_REFOUND => 'Тек төлемді қайтару',
    ],
    'ps' => [
        PS_UNPAYED => 'Төленбеді',
        PS_PAYING => 'Төленуде',
        PS_PAYED => 'Төленді',
        PS_PAYED_PART => 'Ішнара төленді(алғытөлем)',
        PS_REFOUND => 'Төлем қайтарылды',
        PS_REFOUND_PART => 'Ішнара төлем қайтарылды',
        PS_MAIN_PAYED_PART => 'Ішнара төленді',
    ],
    'ss' => [
        SS_UNSHIPPED => 'Жіберілмеді',
        SS_PREPARING => 'Жіберуде',
        SS_SHIPPED => 'Жіберілді',
        SS_RECEIVED => 'Жеткізілді',
        SS_SHIPPED_PART => 'Жіберілді(Ішнара)',
        SS_SHIPPED_ING => 'Жіберуде(бөлуді өңдеу)',
        OS_SHIPPED_PART => 'Жіберілді(Ішнара)',
        SS_PART_RECEIVED => 'Ішнара қабылданды',
        SS_TO_BE_SHIPPED => 'Жіберуді күту',
    ],

    // Тасымалға ілесу
    'location_tracking_progress' => 'Ізіне түсу процессі',
    'time' => 'Уақыты',
    'tracking_tips' => [
        'Қателік туындады',
        'Кешіріңіз,қазірше дерек жоқ'
    ],
    'order_action_user' => 'Жоспарлы міндет',
    'order_pay_timeout' => 'Төлем үзілісі',
    'order_failure' => 'Заказ жіберу сәтсіз,дерек табылмады,қайта сынап көріңіз',
    'order_pay_failure' => 'Заказды төлеу сәтсіз,қайта заказ беріңіз',
    'order_status_not_support' => 'Заказ күйі бұл атқаруды жасай алмайды',
    'track_shipping_info_one' => 'Тасымал ақпараты',
    'track_shipping_info_two' => 'Жинау',
    'track_shipping_info_three' => 'Басқа тасымалдар',
    'pay_order_sn' => 'Төлем заказ коды：%s',
    'upload_pay_document' => 'Төлем дәлелін жүктеу',
    'see_pay_document' => 'Дәлелді көру',

    'order_return_sn' => 'Қайтару-коды'
];
