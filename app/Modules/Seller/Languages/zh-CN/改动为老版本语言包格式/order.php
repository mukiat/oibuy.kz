<?php

$_LANG['admin_order_list_motion'] = "Адммин заказ тізімдер журналы";
$_LANG['update_shipping'] = "【%s】 жеткізу түрін 【%s】 қылып өзгертті";
$_LANG['self_motion_goods'] = "Автоматты тауарды қабылдау";

$_LANG['order_ship_delivery'] = "Тасымал коды：【%s】";
$_LANG['order_ship_invoice'] = "Трек-нөмірі：【%s】";
$_LANG['edit_order_invoice'] = "【%s】трек-нөмірін：【%s】 қылып өзгертті";

$_LANG['add_order_info'] = "【%s】 қолмен енгізілген заказ";
$_LANG['shipping_refund'] = "Заказды өзгерткендіктен қайтарылған сома：%s";

$_LANG['01_stores_pick_goods'] = "Заказ төленбеді,тауарды ала алмайсыз";
$_LANG['02_stores_pick_goods'] = "Сәтті атқарылды,тауар алынды";
$_LANG['03_stores_pick_goods'] = "Тексеріс коды қате,қайта енгізіңіз";
$_LANG['04_stores_pick_goods'] = "6 орынды алу кодын енгізіңіз";

$_LANG['order_label'] = 'Инфо.таңбасы';
$_LANG['amount_label'] = 'Сома таңбасы';

$_LANG['bar_code'] = 'Штрих-код';
$_LANG['label_bar_code'] = 'Штрих-код：';

$_LANG['region'] = 'Жеткізу аймағы';
$_LANG['order_shipped_sms'] = 'Заказыңыз:%s,%s жіберілді'; //wang

$_LANG['setorder_nopay'] = 'Төленбеді деп кою';
$_LANG['setorder_cancel'] = 'Төленбеді деп қою';

//退换货 start
$_LANG['rf'][RF_APPLICATION] = 'Клиент кері жібереді';
$_LANG['rf'][RF_RECEIVE] = 'Қайтаруды қабылдап алу';
$_LANG['rf'][RF_SWAPPED_OUT_SINGLE] = 'Алмастырған тауарды жіберу【бөліп】';
$_LANG['rf'][RF_SWAPPED_OUT] = 'Алмастырған тауарды жіберу';
$_LANG['rf'][RF_COMPLETE] = 'Аяқталды';
$_LANG['rf'][RF_AGREE_APPLY] = 'Мақұлдау';
$_LANG['rf'][REFUSE_APPLY] = 'Өтінім қабылданбады';
$_LANG['ff'][FF_NOMAINTENANCE] = 'Жөнделмеді';
$_LANG['ff'][FF_MAINTENANCE] = 'Жөнделді';
$_LANG['ff'][FF_REFOUND] = 'Ақша қайтарылды';
$_LANG['ff'][FF_NOREFOUND] = 'Ақша қайтарылмады';
$_LANG['ff'][FF_NOEXCHANGE] = 'Алмастырылмады';
$_LANG['ff'][FF_EXCHANGE] = 'Алмастырылды';
$_LANG['refund_money'] = 'Қайтару сомасы';
$_LANG['money'] = 'Сома';
$_LANG['shipping_money'] = 'тг тасымал ақы';
$_LANG['is_shipping_money'] = 'Тасымал ақыны қайтару';
$_LANG['no_shipping_money'] = 'Тасымал ақыны қайтармау';
$_LANG['return_user_line'] = 'Оффлайн қайтару';
$_LANG['return_reason'] = 'Қайтару себебі';
$_LANG['whether_display'] = 'Көрсету';
$_LANG['return_baitiao'] = 'Бөліп төлеу сомасын қайтару(Егерде бөліп төлеумен теңгерім аралас төленген болса,онда теңгерімнен төленген сома теңгерімге қайтарылады)';
$_LANG['return_online'] = 'Онлайн қайтару';
$_LANG['return_online_notice'] = 'Онлайн төлемнің ішінде теңгеріммен төленген сома бар болса,онда теңгерімнің өзіне тиесілі сомасы қайтарылады.';
$_LANG['return_reason_desc'] = 'Себебін сипаттау';
$_LANG['status_agree_apply'][0] = 'Тексерісте';
$_LANG['status_agree_apply'][1] = 'Тексерілді';
$_LANG['status_is_check'][0] = 'Күтілуде';
$_LANG['status_is_check'][1] = 'Мақұлданды';
$_LANG['label_return_check'] = 'Ақша қайтаруды мақұлдау';
$_LANG['label_return_check_2'] = 'Тексеріс керек емес';
$_LANG['return_apply'] = 'Ақша қайтару өтініші';
$_LANG['refound_agree'] = 'Қайтаруға келісу';
//退换货 end

//Oibuy командасы --zhuo start
$_LANG['auto_delivery_time'] = 'Автоматты тауарды қабылдау уақыты：';
$_LANG['dateType'][0] = 'күн';
//Oibuy командасы --zhuo end

/* 订单搜索 */
$_LANG['order_sn'] = 'Заказ коды';
$_LANG['consignee'] = 'Қабылдаушы';
$_LANG['all_status'] = 'Заказ күйі';

$_LANG['cs'][OS_UNCONFIRMED] = 'Растау';
$_LANG['cs'][CS_AWAIT_PAY] = 'Төлеу';
$_LANG['cs'][CS_AWAIT_SHIP] = 'Жіберу';
$_LANG['cs'][CS_FINISHED] = 'Аяқталды';
$_LANG['cs'][PS_PAYING] = 'Төленуде';
$_LANG['cs'][OS_CANCELED] = 'Болдырмау';
$_LANG['cs'][OS_INVALID] = 'Жарамсыз';
$_LANG['cs'][OS_RETURNED] = 'Қайтару';
$_LANG['cs'][OS_SHIPPED_PART] = 'Қабылдау';

/* 订单状态 */
$_LANG['os'][OS_UNCONFIRMED] = 'Расталмады';
$_LANG['os'][OS_CONFIRMED] = 'Расталды';
$_LANG['os'][OS_CANCELED] = '<font color="red">Болдырмау</font>';
$_LANG['os'][OS_INVALID] = '<font color="red">Жарамсыз</font>';
$_LANG['os'][OS_RETURNED] = '<font color="red">Қайтару</font>';
$_LANG['os'][OS_SPLITED] = 'Бөлінді';
$_LANG['os'][OS_SPLITING_PART] = 'Ішнара бөлінді';
$_LANG['os'][OS_RETURNED_PART] = '<font color="red">Ішнара ақша қайтарылды</font>';
$_LANG['os'][OS_ONLY_REFOUND] = '<font color="red">Тек ақша қайтару</font>';

$_LANG['ss'][SS_UNSHIPPED] = 'Жіберілмеді';
$_LANG['ss'][SS_SHIPPED] = 'Жіберілді';
$_LANG['ss'][SS_RECEIVED] = 'Тауарды қабылдау';
$_LANG['ss'][SS_PREPARING] = 'Қораптауда';
$_LANG['ss'][SS_SHIPPED_PART] = 'Жіберілді(Ішнара)';
$_LANG['ss'][SS_SHIPPED_ING] = 'Жіберуде';
$_LANG['ss'][OS_SHIPPED_PART] = 'Жіберілді(Ішнара)';
$_LANG['ss'][SS_PART_RECEIVED] = 'Ішнара қабылданды';
$_LANG['ss'][SS_TO_BE_SHIPPED] = 'Жіберуде';

$_LANG['ps'][PS_UNPAYED] = 'Төленбеді';
$_LANG['ps'][PS_PAYING] = 'Төленуде';
$_LANG['ps'][PS_PAYED] = 'Төленді';
$_LANG['ps'][PS_PAYED_PART] = 'Ішнара төленді(депозит)';
$_LANG['ps'][PS_REFOUND] = 'Ақша қайтарылды';
$_LANG['ps'][PS_REFOUND_PART] = 'Ішнара қайтарылды';
$_LANG['ps'][PS_MAIN_PAYED_PART] = 'Ішнара төленді'; //主订单

$_LANG['ss_admin'][SS_SHIPPED_ING] = 'Жіберуде（Сайттағы күйі：Жіберілмеді）';
/* 订单操作 */
$_LANG['label_operable_act'] = 'Қазір атқара алатын атқарулар：';
$_LANG['label_action_note'] = 'Атқару жазбасы：';
$_LANG['label_invoice_note'] = 'Жіберу жазбасы：';
$_LANG['label_invoice_no'] = 'Трек-нөмірі：';
$_LANG['label_cancel_note'] = 'Болдырмау себебі：';
$_LANG['notice_cancel_note'] = '（Сатушының клиентке деген жазбасында сақталады）';
$_LANG['op_confirm'] = 'Растау';
$_LANG['refuse'] = 'Бас тарту';
$_LANG['refuse_apply'] = 'Бастарту';
$_LANG['is_refuse_apply'] = 'Бастартылған';
$_LANG['op_pay'] = 'Төлеу';
$_LANG['op_prepare'] = 'Қораптау';
$_LANG['op_ship'] = 'Жіберу';
$_LANG['op_cancel'] = 'Болдырмау';
$_LANG['op_invalid'] = 'Жарамсыз';
$_LANG['op_return'] = 'Қайтару';
$_LANG['op_unpay'] = 'Төленбеді деп қою';
$_LANG['op_unship'] = 'Жіберілмеді';
$_LANG['op_cancel_ship'] = 'Жіберуді болдырмау';
$_LANG['op_receive'] = 'Қабылданды';
$_LANG['op_assign'] = 'Тағайындалды';
$_LANG['op_after_service'] = 'Сатудан кейінгі қызмет';
$_LANG['act_ok'] = 'Сәтті атқарылды';
$_LANG['act_false'] = 'Атқару сәтсіз';
$_LANG['act_ship_num'] = 'Бұл заказдың жіберу саны заказдағы тауар санынан асып кетпеуі керек';
$_LANG['act_good_vacancy'] = 'Тауар қоры жеткіліксіз';
$_LANG['act_good_delivery'] = 'Тауар жіберілді';
$_LANG['notice_gb_ship'] = 'Жазба：Топтасу шарасы сәтті деп қойылмағанға дейін,тауарды жіберуге болмайды';
$_LANG['back_list'] = 'Тізімге қайту';
$_LANG['op_remove'] = 'Жою';
$_LANG['op_you_can'] = 'Атқара алатын операциялар';
$_LANG['op_split'] = 'Жіберу жапсырмасын жасау';
$_LANG['op_to_delivery'] = 'Жіберу';
$_LANG['op_swapped_out_single'] = 'Алмастырған тауарды жіберу【бөлінді】';
$_LANG['op_swapped_out'] = 'Алмастырған тауарды жіберу';
$_LANG['op_complete'] = 'Қайтаруды аяқтау';
$_LANG['op_refuse_apply'] = 'Бастарту';
$_LANG['baitiao_by_stages'] = 'Бөліп төлеу';
$_LANG['expired'] = 'Мерзімі бітті';
$_LANG['unable_to_click'] = 'Баса алмайсыз';

/* 订单列表 */
$_LANG['order_amount'] = 'Төлейтін сома';
$_LANG['total_fee'] = 'Жалпы сома';
$_LANG['shipping_name'] = 'Жеткізу түрі';
$_LANG['pay_name'] = 'Төлем түрі';
$_LANG['address'] = 'Мекен-жай';
$_LANG['order_time'] = 'Заказ уақыты';
$_LANG['trade_snapshot'] = 'Сауда суреті';
$_LANG['detail'] = 'Көру';
$_LANG['phone'] = 'Телефон';
$_LANG['group_buy'] = '（Топтасу）';
$_LANG['error_get_goods_info'] = 'Заказдағы тауар инфосын алудан қателік шықты';
$_LANG['exchange_goods'] = '（Бонус айырбастау）';
$_LANG['auction'] = '（Аукцион）';
$_LANG['snatch'] = '（Талас）';
$_LANG['presale'] = '（Presale）';

$_LANG['js_languages']['remove_confirm'] = 'Заказды жойсаңыз бұл заказдың бүкіл ақпараты жойылады.Жоюға сенімдісіз бе？';

/* 订单搜索 */
$_LANG['label_buyer'] = 'Алушы：';
$_LANG['label_order_sn'] = 'Заказ коды：';
$_LANG['label_all_status'] = 'Заказ күйі：';
$_LANG['label_user_name'] = 'Алушы：';
$_LANG['label_consignee'] = 'Қабылдаушы：';
$_LANG['label_email'] = 'E-mail：';
$_LANG['label_post_form_company'] = 'Тасымал：';
$_LANG['label_address'] = 'Мекен-жай：';
$_LANG['label_zipcode'] = 'Индекс：';
$_LANG['label_tel'] = 'Үй телефоны：';
$_LANG['label_mobile'] = 'Тел нөмірі：';
$_LANG['label_shipping'] = 'Жеткізу түрі：';
$_LANG['label_payment'] = 'Төлем түрі：';
$_LANG['label_order_status'] = 'Заказ күйі：';
$_LANG['label_pay_status'] = 'Төлем күйі：';
$_LANG['label_shipping_status'] = 'Жіберу күйі：';
$_LANG['label_area'] = 'Орналасуы：';
$_LANG['label_time'] = 'Заказ уақыты：';

/* 订单详情 */
$_LANG['self_mention'] = 'Барып алу';
$_LANG['prev'] = 'Алдыңғы заказ';
$_LANG['next'] = 'Келесі заказ';
$_LANG['print_order'] = 'Басып шығару';
$_LANG['print_shipping'] = 'Тасымал қағазын шығару';
$_LANG['print_order_sn'] = 'Коды：';
$_LANG['print_buy_name'] = 'Алушы：';
$_LANG['label_consignee_address'] = 'Мекен-жай：';
$_LANG['no_print_shipping'] = 'Кешіріңіз,сіз әлі басып шығару үлгісін қоймадыңыз,басып шығара алмайсыз.';
$_LANG['suppliers_no'] = 'Поставщикті көрсетпей өзіміз өңдейміз';
$_LANG['restaurant'] = 'Дүкен';

$_LANG['order_info'] = 'Заказ инфосы';
$_LANG['base_info'] = 'Негізгі инфо.';
$_LANG['other_info'] = 'Басқа инфо.';
$_LANG['consignee_info'] = 'Қабылдаушы инфосы';
$_LANG['fee_info'] = 'Төлем инфосы';
$_LANG['action_info'] = 'Атқару инфосы';
$_LANG['shipping_info'] = 'Жеткізу инфосы';

$_LANG['label_how_oos'] = 'Қор толтыру：';
$_LANG['consignee_name'] = 'Аты-жөні';
$_LANG['consignee_mobile_phone'] = 'Телефоны';
$_LANG['invoice_consignee_address'] = 'Мекен-жайы';
$_LANG['label_how_surplus'] = 'Теңгерім：';
$_LANG['label_pack'] = 'Қорап：';
$_LANG['label_card'] = 'Силық карта：';
$_LANG['label_card_message'] = 'Құтықтау сөзі：';
$_LANG['label_order_time'] = 'Тапсырыс беру：';
$_LANG['label_pay_time'] = 'Төлеу：';
$_LANG['label_shipping_time'] = 'Жіберу：';
$_LANG['label_sign_building'] = 'Адресс аты：';
$_LANG['label_best_time'] = 'Жеткізу уақыты：';
$_LANG['label_inv_type'] = 'Фактура түрі：';
$_LANG['label_inv_payee'] = 'Тақырып：';
$_LANG['label_inv_content'] = 'Мазмұн：';
$_LANG['label_postscript'] = 'Хабарлама：';
$_LANG['label_region'] = 'Аймағы：';

$_LANG['label_shop_url'] = 'Сайты：';
$_LANG['label_shop_address'] = 'Адресс：';
$_LANG['label_service_phone'] = 'Телефон：';
$_LANG['label_print_time'] = 'Басып шығару：';

$_LANG['label_suppliers'] = 'Поставщик таңдау：';
$_LANG['label_agency'] = 'Кеңсе：';
$_LANG['suppliers_name'] = 'Поставщик';

$_LANG['product_sn'] = 'Тип коды';
$_LANG['goods_info'] = 'Тауар инфо';
$_LANG['goods_name'] = 'Тауар аты';
$_LANG['goods_name_brand'] = 'Тауар аты [Бренд]';
$_LANG['goods_sn'] = 'Тауар коды';
$_LANG['goods_price'] = 'Баға';
$_LANG['give_integral'] = 'Силық бонус';
$_LANG['goods_number'] = 'Саны';
$_LANG['goods_attr'] = 'Төлсипат';
$_LANG['goods_delivery'] = 'Жіберілген саны';
$_LANG['goods_delivery_curr'] = 'Жіберілген саны';
$_LANG['storage'] = 'Қоры';
$_LANG['subtotal'] = 'Жины';
$_LANG['amount_return'] = 'Ақша қайтару';
$_LANG['label_total'] = 'Жалпы：';
$_LANG['label_total_weight'] = 'Жалпы салмағы：';
$_LANG['label_total_cost'] = 'Өзіндік құны：';
$_LANG['measure_unit'] = 'Бірлігі';
$_LANG['contain_content'] = 'Қамтылды';
$_LANG['application_refund'] = 'Ақша қайтару өтініші';

$_LANG['return_discount'] = 'Жеңілдік';

$_LANG['label_goods_amount'] = 'Жалпы сомасы：';
$_LANG['label_discount'] = 'Жеңілдік：';
$_LANG['label_tax'] = 'Салық：';
$_LANG['label_shipping_fee'] = 'Жеткізу：';
$_LANG['label_insure_fee'] = 'Сақтандыру：';
$_LANG['label_insure_yn'] = 'Сақтандыру：';
$_LANG['label_pay_fee'] = 'Төлем құны：';
$_LANG['label_pack_fee'] = 'Қораптау құны：';
$_LANG['label_card_fee'] = 'Силық карта：';
$_LANG['label_money_paid'] = 'Төленген сома：';
$_LANG['label_surplus'] = 'Теңгеріммен：';
$_LANG['label_integral'] = 'Бонуспен：';
$_LANG['label_bonus'] = 'Конвертпен：';
$_LANG['label_value_card'] = 'Төлем картамен：';
$_LANG['label_vc_dis_money'] = 'Карта жеңілдігі：';
$_LANG['label_coupons'] = 'Купонмен：';
$_LANG['label_order_amount'] = 'Заказдың жалпы сомасы：';
$_LANG['label_money_dues'] = 'Төлеу керек сома：';
$_LANG['label_money_refund'] = 'Қайтаратын сома：';
$_LANG['label_to_buyer'] = 'Сатушының клиентке жазбасы：';
$_LANG['save_order'] = 'Заказды сақтау';
$_LANG['notice_gb_order_amount'] = '（Ескерту：Егерде Топтасуда депозит бар болса,бірінші рет тек депозитпен сәйкесті төлем ақысын төлеу керек）';
$_LANG['formated_order_amount'] = 'Заказ жалпы сомасы';
$_LANG['stores_info'] = 'Бутик инфосы';

$_LANG['action_user'] = 'Атқарушы';
$_LANG['action_time'] = 'Уақыты';
$_LANG['return_status'] = 'Атқару';
$_LANG['refound_status'] = 'Күйі';
$_LANG['order_status'] = 'Заказ күйі';
$_LANG['pay_status'] = 'Төлем күйі';
$_LANG['shipping_status'] = 'Жіберу күйі';
$_LANG['action_note'] = 'Ескерту';
$_LANG['pay_note'] = 'Төлем хабары：';
$_LANG['action_jilu'] = 'Атқару дерегі';
$_LANG['not_action_jilu'] = 'Қазірше атқару дерегі жоқ';

$_LANG['sms_time_format'] = 'm Ай j күн G сағат';
$_LANG['order_splited_sms'] = '%s заказыңыз,%s %s жасалу үстінде [%s]';
$_LANG['order_removed'] = 'Заказ сәтті жойылды.';
$_LANG['return_list'] = 'Заказ тізіміне қайту';
$_LANG['order_remove_failure'] = '%s жалғыз тауарды қайтару тапсырысы бар,заказды жою сәтсіз.';

/* 订单处理提示 */
$_LANG['surplus_not_enough'] = 'Бұл заказда %s теңгерім қолданылған,қазір клиенттің теңгерімі жеткіліксіз.';
$_LANG['integral_not_enough'] = 'Бұл заказда %s бонус қолданылған,қазір клиенттің бонусы жеткіліксіз.';
$_LANG['bonus_not_available'] = 'Бұл заказда конверт қолданылған,қазір клиенттің конверті жарамсыз.';

/* 购货人信息 */
$_LANG['display_buyer'] = 'Алушы ақпаратын көрсету';
$_LANG['buyer_info'] = 'Алушы инфосы';
$_LANG['pay_points'] = 'Бонус';
$_LANG['rank_points'] = 'Дәреже өсімі';
$_LANG['user_money'] = 'Теңгерім';
$_LANG['email'] = 'E-mail';
$_LANG['rank_name'] = 'Дәреже';
$_LANG['bonus_count'] = 'Конверт саны';
$_LANG['zipcode'] = 'Индекс';
$_LANG['tel'] = 'Үй телефоны';
$_LANG['mobile'] = 'Телефоны';
$_LANG['leaving_message'] = 'Жазба';

/*增值税发票信息*/
$_LANG['vat_info'] = 'Фактура ақпараты';
$_LANG['vat_name'] = 'Мекеме аты';
$_LANG['vat_taxid'] = 'БСН/ЖСН';
$_LANG['vat_company_address'] = 'Мекен-жай';
$_LANG['vat_company_telephone'] = 'Телефоны';
$_LANG['vat_bank_of_deposit'] = 'Банк';
$_LANG['vat_bank_account'] = 'Банк шоты';

/* 合并订单 */
$_LANG['seller_order_sn_same'] = 'Біріктірілетін тапсырыстардың екеуі де бір сатушыға тиесілі болуы керек';
$_LANG['merge_order_main_count'] = 'Біріктіретін тапсырыс негізгі заказ болмауы керек';
$_LANG['order_sn_not_null'] = 'Біріктіретін заказ нөмірін енгізіңіз';
$_LANG['two_order_sn_same'] = 'Біріктіретін екі заказдың коды ұқсас болмауы керек';
$_LANG['order_not_exist'] = '%s тапсырысы табылмады';
$_LANG['os_not_unconfirmed_or_confirmed'] = '%s тапсырыстың күйі“Растау”немесе“Расталды”емес';
$_LANG['ps_not_unpayed'] = '%s тапсырыстың төлем күйі“Төленбеді”емес';
$_LANG['ss_not_unshipped'] = '%s тапсырыстың жіберу күйі“Жіберілмеді”емес';
$_LANG['order_user_not_same'] = 'Біріктіретін екі тапсырыс бір клиентке тиесілі емес';
$_LANG['merge_invalid_order'] = 'Кешіріңіз,сіз біріктіргіңіз келген тапсырысқа біріктіру атқаруын жасай алмайсыз';

$_LANG['merge_order'] = 'Біріктіру';
$_LANG['from_order_sn'] = 'Қосымша：';
$_LANG['to_order_sn'] = 'Негізгі：';
$_LANG['merge'] = 'Біріктіру';
$_LANG['notice_order_sn'] = 'Екі тапсырыс сәйкес келмесе, біріктірілген тапсырыс ақпараты (мысалы: төлем түрі, жеткізу түрі,қораптау, құттықтау картасы,конверт және т.б.) негізгі тапсырысқа бағынады.';
$_LANG['js_languages']['confirm_merge'] = 'Бұл екі тапсырысты біріктіресіз бе?';

/* 批处理 */
$_LANG['pls_select_order'] = 'Атқару жасағыңыз келген тапсырысты таңдаңыз';
$_LANG['no_fulfilled_order'] = 'Атқару шартына сай тапсырыс табылмады';
$_LANG['updated_order'] = 'Жаңартылған заказ：';
$_LANG['order'] = 'Заказ：';
$_LANG['confirm_order'] = 'Төмендегі заказдарды растау күйіне қоя алмайсыз';
$_LANG['invalid_order'] = 'Төмендегі заказдарды жарамсыз деп коя алмайсыз';
$_LANG['cancel_order'] = 'Төмендегі заказдардан бас тарта алмайсыз';
$_LANG['remove_order'] = 'Төмендегі заказдарды алып тастай алмайсыз';

/* 编辑订单打印模板 */
$_LANG['edit_order_templates'] = 'Басып шығару үлгісін өңдеу';
$_LANG['template_resetore'] = 'Қалпына келтіру';
$_LANG['edit_template_success'] = 'Басып шығару үлгісі сәтті өңделді!';
$_LANG['remark_fittings'] = '（Қосымша）';
$_LANG['remark_gift'] = '（Силық）';
$_LANG['remark_favourable'] = '（Арнайы）';
$_LANG['remark_package'] = '（Пакет）';
$_LANG['remark_package_goods'] = '（Пакеттегі тауар）';

/* 订单来源统计 */
$_LANG['from_order'] = 'Заказ көзі';
$_LANG['referer'] = 'Көзі';
$_LANG['from_ad_js'] = 'Жарнама：';
$_LANG['from_goods_js'] = 'Сыртқы JS жарнама';
$_LANG['from_self_site'] = 'Порталдан';
$_LANG['from'] = 'Келу көзі：';

/* 添加、编辑订单 */
$_LANG['add_order'] = 'Заказ енгізу';
$_LANG['edit_order'] = 'Заказ өңдеу';
$_LANG['step']['user'] = 'Қай клиент үшін заказ беретініңізді таңдаңыз';
$_LANG['step']['goods'] = 'Тауарды таңдау';
$_LANG['step']['consignee'] = 'Қабылдаушы инфосын қою';
$_LANG['step']['shipping'] = 'Жеткізу түрін таңдау';
$_LANG['step']['payment'] = 'Төлем түрін таңдау';
$_LANG['step']['other'] = 'Басқа инфоны баптау';
$_LANG['step']['money'] = 'Ақысын қою';
$_LANG['anonymous'] = 'Анон.клиент';
$_LANG['by_useridname'] = 'Клиент коды немесе клиент аты бойынша іздеңіз';
$_LANG['button_prev'] = 'Алдыңғы';
$_LANG['button_next'] = 'Келесі';
$_LANG['button_finish'] = 'Аяқталды';
$_LANG['button_cancel'] = 'Болдырмау';
$_LANG['name'] = 'Аты';
$_LANG['desc'] = 'Сипаты';
$_LANG['shipping_fee'] = 'Жеткізу ақысы';
$_LANG['free_money'] = 'Тегіндік лимиті';
$_LANG['insure'] = 'Сақтандыру';
$_LANG['pay_fee'] = 'Комиссия';
$_LANG['pack_fee'] = 'Қораптау';
$_LANG['card_fee'] = 'Құттықтау карта';
$_LANG['no_pack'] = 'Қорапсыз';
$_LANG['no_card'] = 'Құттықтау картасыз';
$_LANG['add_to_order'] = 'Заказды енгізу';
$_LANG['calc_order_amount'] = 'Заказ сомасын есептеу';
$_LANG['available_surplus'] = 'Теңгерім：';
$_LANG['available_integral'] = 'Бонус：';
$_LANG['available_bonus'] = 'Конверт：';
$_LANG['admin'] = 'Админ енгізу';
$_LANG['search_goods'] = 'Тауар коды,тауар аты немесе тауардың рет саны бойынша іздеңіз';
$_LANG['category'] = 'Түрлер';
$_LANG['order_category'] = 'Заказ түрі';
$_LANG['brand'] = 'Бренд';
$_LANG['user_money_not_enough'] = 'Клиент теңгерімі жеткіліксіз';
$_LANG['pay_points_not_enough'] = 'Клиент бонусы жеткіліксіз';
$_LANG['money_paid_enough'] = 'Төленген сома тауардың жалпы сомасы және басқа ақылардың жиынтығынан да көп,алдымен ақшасын қайтарыңыз';
$_LANG['price_note'] = 'Ескерту：Тауар бағасында төлсипаттың(SKU) қосалқы бағасыда қамтылған';
$_LANG['select_pack'] = 'Қорап таңдау';
$_LANG['select_card'] = 'Құтықтау картасын таңдау';
$_LANG['select_shipping'] = 'Тасымал түрін таңдаңыз';
$_LANG['want_insure'] = 'Сақтандыру аламын';
$_LANG['update_goods'] = 'Тауарды жаңарту';
$_LANG['notice_user'] = '<strong>Ескерту：</strong>Іздеу нәтижесі тек алдыңғы 20 деректі көрсетеді,егер таба алмаған болсаңыз,' .
    'тіптіде анық іздеуге тура келеді.Егерде бұл клиент порталға тіркелмеген болсада табылмайды,' .
    'алдымен порталда тіркелуі керек';
$_LANG['amount_increase'] = 'Сіз заказды өзгерткендіктен,заказдың жалпы сомасы артып кетті,тағы бір рет төлем жасау керек.';
$_LANG['amount_decrease'] = 'Сіз заказды өзгерткендіктен,заказдың жалпы сомасы кеміді,артық ақшаны қайтару керек';
$_LANG['continue_shipping'] = 'Сіз қабылдаушы мекен-жайын өзгерткендіктен,бұрынғы тасымал түрі жарамсыз,жеткізу түрін қайтадан таңдаңыз.';
$_LANG['continue_payment'] = 'Сіз тасымал түрін өзгерткендіктен,бұрынғы төлем түрі жарамсыз,төлем түрін қайтадан таңдаңыз.';
$_LANG['refund'] = 'Ақша қайтару';
$_LANG['cannot_edit_order_shipped'] = 'Жіберілген заказды өзгерте алмайсыз';
$_LANG['cannot_edit_order_payed'] = 'Төленген заказды өзгерте алмайсыз';
$_LANG['address_list'] = 'Бар мекен-жайлардан таңдаңыз：';
$_LANG['order_amount_change'] = 'Заказдың жалпы сомасы %s теңгеден %s теңгеге өзгерді';
$_LANG['shipping_note'] = 'Ескерту：Заказ жіберіліп қойғандықтан,тасымал түрін өзгерткенде тасымал ақысына және сақтандыру ақысына әсер етпейді';
$_LANG['change_use_surplus'] = 'Заказды өңдеу %s ,алғытөлем арқылы төленген соманы өзгерту';
$_LANG['change_use_integral'] = 'Заказды өңдеу %s ,бонуспен төленген санды өзгерту';
$_LANG['return_order_surplus'] = 'Бас тарту,жарамсыз немесе тауарды қайтару операциялары жасалғандықтан,%s заказын төлеген кездегіалғытөлемді қайтару';
$_LANG['return_order_integral'] = 'Бас тарту,жарамсыз немесе тауарды қайтару операциялары жасалғандықтан,%s заказын төлегендегі істетілген бонусты қайтару';
$_LANG['order_gift_integral'] = '%s заказынан берілген силық бонус';
$_LANG['return_order_gift_integral'] = 'Тауарды қайтару немесе тауар жіберілмеу операциялары жасалғандықтан,%s заказы берген силық бонусын қайтару';
$_LANG['invoice_no_mall'] = 'Көп жіберу кодының арасын үтірмен（“,”）бөліңіз';

$_LANG['js_languages']['input_price'] = 'Еркін баға қою';
$_LANG['js_languages']['pls_search_user'] = 'Клиентті іздеңіз және таңдаңыз';
$_LANG['js_languages']['confirm_drop'] = 'Бұл тауарды жоясыз ба？';
$_LANG['js_languages']['invalid_goods_number'] = 'Тауар саны дұрыс емес';
$_LANG['js_languages']['pls_search_goods'] = 'Тауарды іздеп таңдаңыз';
$_LANG['js_languages']['pls_select_area'] = 'Мекен-жайды толық таңдаңыз';
$_LANG['js_languages']['pls_select_shipping'] = 'Жеткізу түрін таңдаңыз';
$_LANG['js_languages']['pls_select_payment'] = 'Төлем түрін таңдаңыз';
$_LANG['js_languages']['pls_select_pack'] = 'Қорапты таңдаңыз';
$_LANG['js_languages']['pls_select_card'] = 'Құттықтау картасын таңдаңыз';
$_LANG['js_languages']['pls_input_note'] = 'Жазба қалтырыңыз！';
$_LANG['js_languages']['pls_input_cancel'] = 'Бас тарту себебін енгізіңіз！';
$_LANG['js_languages']['pls_select_refund'] = 'Ақша қайтару түрін таңдаңыз！';
$_LANG['js_languages']['pls_select_refund_cause'] = 'Тауарды қайтару себебін таңдаңыз！';
$_LANG['js_languages']['pls_select_agency'] = 'Кеңсені таңдаңыз！';
$_LANG['js_languages']['pls_select_other_agency'] = 'Бұл заказ қазір осы кеңсеге тиесілі,басқа кеңсені таңдаңыз！';
$_LANG['js_languages']['loading'] = 'Өңделуде...';

/* 订单操作 */
$_LANG['order_operate'] = 'Операция：';
$_LANG['label_refund_amount'] = 'Қайтару сомасы：';
$_LANG['label_handle_refund'] = 'Қайтару түрі：';
$_LANG['label_refund_note'] = 'Түсіндіру：';
$_LANG['return_user_money'] = 'Теңгерімге қайтару';
$_LANG['create_user_account'] = 'Қайтару өтінішін жасау';
$_LANG['create_user_account_notice'] = 'Таңдап және расталғаннан кейін заказ тауары жіберілмеген болса,онда клиентке ақша шешіп алу өтінімі жасалады,тауар жіберілген болса,онда ақшаны шешіп алу өтініші және тауарды қайтару қағазы бірге жасалады';
$_LANG['not_handle'] = 'ӨҢдемеу,қате атқарулар жасаған кезде осыны таңдасаңыз болады';

$_LANG['order_refund'] = 'Ақша қайтару：%s';
$_LANG['order_pay'] = 'Заказ төлемі：%s';

$_LANG['send_mail_fail'] = 'Email жіберу сәтсіз';

$_LANG['send_message'] = 'Жазбаны жіберу/көру';

/* 发货单操作 */
$_LANG['delivery_operate'] = 'Жіберу операциясы：';
$_LANG['delivery_sn_number'] = 'Жіберу коды：';
$_LANG['invoice_no_sms'] = 'Трек-нөмірін енгізіңіз！';
$_LANG['invoice_no_notice'] = 'Трек-нөмірі тек сан және латын әріптен тұрады！';

/* 发货单搜索 */
$_LANG['delivery_sn'] = 'Жіберу қағазы';

/* 发货单状态 */
$_LANG['delivery_status_dt'] = 'Жіберу күйі';
$_LANG['delivery_status'][0] = 'Жіберілді';
$_LANG['delivery_status'][1] = 'Қайтару';
$_LANG['delivery_status'][2] = 'Қалыпты';

/* 发货单标签 */
$_LANG['label_delivery_status'] = 'Күйі';
$_LANG['label_suppliers_name'] = 'Поставщик';
$_LANG['label_delivery_time'] = 'Уақыты';
$_LANG['label_delivery_sn'] = 'Жіберу коды';
$_LANG['label_add_time'] = 'Тапсырыс уақыты';
$_LANG['label_update_time'] = 'Жіберу уақыты';
$_LANG['label_send_number'] = 'Жіберу саны';
$_LANG['batch_delivery'] = 'Топпен жіберу';

/* 发货单提示 */
$_LANG['tips_delivery_del'] = 'Жіберу қағазы сәтті жойылды！';

/* 退货单操作 */
$_LANG['back_operate'] = 'Қайтару операциясы：';

/* 退货单标签 */
$_LANG['return_time'] = 'Қайтару уақыты：';
$_LANG['label_return_time'] = 'Қайтару уақыты';
$_LANG['label_apply_time'] = 'Өтініш уақыты';
$_LANG['label_back_shipping'] = 'Қайтарудың тасымал түрі';
$_LANG['label_back_invoice_no'] = 'Қайтарудың трек-нөмірі';
$_LANG['back_order_info'] = 'Қайтару инфосы';

/* 退货单提示 */
$_LANG['tips_back_del'] = 'Қайтару сәтті жойылды！';

$_LANG['goods_num_err'] = 'Қор жеткіліксіз,қайтадан таңдаңыз！';

/*大商创1.5后台新增*/
/*退换货列表*/
$_LANG['problem_desc'] = 'Мәселені сипаттау';
$_LANG['product_repair'] = 'Тауарды жөндеу';
$_LANG['product_return'] = 'Тауарды қайтару';
$_LANG['product_change'] = 'Тауарды ауыстыру';
$_LANG['product_price'] = 'Тауар бағасы';

$_LANG['return_sn'] = 'Қайтару коды';
$_LANG['return_change_sn'] = 'Қайтару коды';
$_LANG['repair'] = 'Жөндеу';
$_LANG['return_goods'] = 'Қайтару';
$_LANG['change'] = 'Ауыстыру';
$_LANG['only_return_money'] = 'Ақша қайтару';
$_LANG['already_repair'] = 'Жөнделді';
$_LANG['refunded'] = 'Ақша қайтарылды';
$_LANG['already_change'] = 'Ауыстырылды';
$_LANG['return_change_type'] = 'Типі';
$_LANG['apply_time'] = 'Өтініш уақыты';
$_LANG['y_amount'] = 'Қайтаратын сома';
$_LANG['s_amount'] = 'Қайтарған сома';
$_LANG['actual_return'] = 'Жалпы қайтарған сома';
$_LANG['return_change_num'] = 'Қайтарған саны';
$_LANG['receipt_time'] = 'Қабылдаған уақыт';
$_LANG['applicant'] = 'Өтініш беруші';
$_LANG['to_order_sn2'] = 'Негізгі заказ';
$_LANG['to_order_sn3'] = 'Негізгі заказ';
$_LANG['sub_order_sn'] = 'Қосымша заказ';
$_LANG['sub_order_sn2'] = 'Қосымша заказ';

$_LANG['return_reason'] = 'Қайтару себебі';
$_LANG['reason_cate'] = 'Себеп түрлері';
$_LANG['top_cate'] = 'Негізгі түр';

$_LANG['since_some_info'] = 'Алып кету инфосы';
$_LANG['since_some_name'] = 'Алатын жер атауы';
$_LANG['contacts'] = 'Аты-жөні';
$_LANG['tpnd_time'] = 'Алу уақыты';
$_LANG['warehouse_name'] = 'Склад аты';
$_LANG['ciscount'] = 'Жеңілдік';
$_LANG['notice_delete_order'] = '(Клиент кеңсесі：бұл заказ жойылды)';
$_LANG['notice_trash_order'] = 'Клиент жойып жіберді';
$_LANG['order_not_operable'] = '（Заказды атқара алмайсыз）';

$_LANG['region'] = 'Аймақ';
$_LANG['seller_mail'] = 'Сатушы жіберді';
$_LANG['courier_sz'] = 'Трек-нөмірі';
$_LANG['select_courier'] = 'Тасымал түрін таңдау';
$_LANG['fillin_courier_number'] = 'Трек-нөмірін енгізіңіз';
$_LANG['edit_return_reason'] = 'Қайтару себебін өңдеу';
$_LANG['buyers_return_reason'] = 'Клиенттің қайтару себебі';
$_LANG['user_file_image'] = 'Клеинттің дәлел суреттері';
$_LANG['operation_notes'] = 'Атқару жазбасы';
$_LANG['agree_apply'] = 'Мақұлдау';
$_LANG['receive_goods'] = 'Қайтаруды қабылдап алу';
$_LANG['current_executable_operation'] = 'Атқара алатын операциялар';
$_LANG['refound'] = 'Ақша қайтару';
$_LANG['swapped_out_single'] = 'Ауыстырған тауарды жіберу【бөлек】';
$_LANG['swapped_out'] = 'Ауыстырған тауарды жіберу';
$_LANG['complete'] = 'Тауарды қайтару аяқталды';
$_LANG['after_service'] = 'Сатудан кейінгі қызмет';
$_LANG['complete'] = 'Қайтаруды аяқтау';
$_LANG['wu'] = 'Жоқ';
$_LANG['not_filled'] = 'Енгізілмеді';
$_LANG['seller_message'] = 'Сатушы жазбасы';
$_LANG['buyer_message'] = 'Клиент жазбасы';
$_LANG['total_stage'] = 'Жалпы бөлу мерзімі';
$_LANG['stage'] = 'Ай';
$_LANG['by_stage'] = 'Бөлу сомасы';
$_LANG['yuan_stage'] = 'тг/ай';
$_LANG['submit_order'] = 'Жолдау';
$_LANG['payment_order'] = 'Заказды төлеу';
$_LANG['seller_shipping'] = 'Сатушы жіберді';
$_LANG['confirm_shipping'] = 'Қабылдап алу';
$_LANG['evaluate'] = 'Бағалау';
$_LANG['logistics_tracking'] = 'Ізіне түсу';
$_LANG['cashdesk'] = 'Касса';
$_LANG['wxapp'] = 'MiniApp';
$_LANG['info'] = 'Инфо';
$_LANG['general_invoice'] = 'Фактура';
$_LANG['personal_general_invoice'] = 'Жеке тұлға фактурасы';
$_LANG['enterprise_general_invoice'] = 'Заңды тұлға фактурасы';
$_LANG['VAT_invoice'] = 'Салып фактурасы';
$_LANG['id_code'] = 'ID коды';
$_LANG['has_been_issued'] = 'Жіберілді';
$_LANG['invoice_generated'] = 'Жіберу қағазы жасалды';
$_LANG['has_benn_refund'] = 'Ақша қайтарылды';
$_LANG['net_profit'] = 'Таза пайда шамамен';
$_LANG['one_key_delivery'] = 'Біржолата жіберу';
$_LANG['goods_delivery'] = 'Тауар жіберу';
$_LANG['search_logistics_info'] = 'Тасымал дерегін іздеуде,күте тұрыңыз...';
$_LANG['consignee_address'] = 'Мекен-жайы';
$_LANG['view_order'] = 'Заказды көру';
$_LANG['set_baitiao'] = 'Бөліп төлеуді қою';
$_LANG['account_details'] = 'Шот деталы';
$_LANG['goods_sku'] = 'Тауар коды';
$_LANG['all_order'] = 'Барлығы';
$_LANG['order_status_01'] = 'Растау';
$_LANG['order_status_02'] = 'Төлеу';
$_LANG['order_status_03'] = 'Жіберу';
$_LANG['order_status_04'] = 'Аяқталды';
$_LANG['order_status_05'] = 'Қабылдау';
$_LANG['order_status_06'] = 'Төленуде';
$_LANG['search_keywords_placeholder'] = 'Заказ коды/Тауар коды/Тауар кілтсөзі';
$_LANG['search_keywords_placeholder2'] = 'Тауар коды/Тауар кілтсөзі';
$_LANG['is_reality'] = 'Оригинал';
$_LANG['is_return'] = 'Қайтару';
$_LANG['is_fast'] = 'Тез жеткізу';

$_LANG['order_category'] = 'Заказ түрлері';
$_LANG['baitiao_order'] = 'Бөліп төлеу';
$_LANG['zc_order'] = 'Жинау заказы';
$_LANG['so_order'] = 'Бутик заказы';
$_LANG['fx_order'] = 'Бөлісу заказы';
$_LANG['team_order'] = 'Бірлесу';
$_LANG['bargain_order'] = 'Баға түсіру';
$_LANG['wholesale_order'] = 'Оптом';
$_LANG['package_order'] = 'Пакеттер';
$_LANG['xn_order'] = 'Виртуалды тауар';
$_LANG['pt_order'] = 'Кәдімгі заказ';
$_LANG['return_order'] = 'Қайтару заказы';
$_LANG['other_order'] = 'Акция заказы';
$_LANG['db_order'] = 'Ұтыс заказы';
$_LANG['ms_order'] = 'Таласу заказы';
$_LANG['tg_order'] = 'Топтасу заказы';
$_LANG['pm_order'] = 'Аукцион заказы';
$_LANG['jf_order'] = 'Бонус заказы';
$_LANG['ys_order'] = 'Presale заказы';

$_LANG['have_commission_bill'] = 'Есептелген комиссия шоты';
$_LANG['knot_commission_bill'] = 'Төленген комиссия шоты';
$_LANG['view_commission_bill'] = 'Шотты көру';

$_LANG['order_export_dialog'] = 'Заказ шығарған терезе';
$_LANG['operation_error'] = 'Атқару қателігі';

$_LANG['confirmation_receipt_time'] = 'Қабылдау уақыты';

$_LANG['fill_user'] = 'Клиент енгізу';
$_LANG['batch_add_order'] = 'Топпен заказ енгізу';
$_LANG['search_user_placeholder'] = 'Клиент аты/коды';
$_LANG['username'] = 'Клиент аты';
$_LANG['search_user_name_not'] = 'Алдымен клиент атын іздеңіз';
$_LANG['search_user_name_notic'] = '<strong>Ескерту：</strong>Іздеу дерегінде тек алдыңғы 20 дерек көрсетіледі,егер табылмасы тіптіде анықырақ іздеу керек. <br>Егер клиент осы порталға тіркелмеген болса табылмайды.';

$_LANG['search_number_placeholder'] = 'Реті/Аты/коды';
$_LANG['select_warehouse'] = 'Алдымен склад таңдаңыз';
$_LANG['receipt_info'] = 'Қабылдау дерегін енгізіңіз';
$_LANG['add_distribution_mode'] = 'Тасымал түрін енгізіңіз';
$_LANG['select_payment_method'] = 'Төлем түрін таңдаңыз';
$_LANG['join_order'] = 'Заказды енгізу';
$_LANG['add_invoice'] = 'Фактура енгізу';
$_LANG['search_goods_first'] = 'Алдымен тауарды іздеңіз';

$_LANG['order_step_notic_01'] = 'Аймақ ақпараты дұрыс енгізілгеніне көзіңізді жеткізіңіз';
/*大商创1.5后台新增end*/

/*众筹相关 by wu*/
$_LANG['zc_goods_info'] = 'Жинау жоба ақпараты';
$_LANG['zc_project_name'] = 'Жинау жоба атауы';
$_LANG['zc_project_raise_money'] = 'Жинау сомасы';
$_LANG['zc_goods_price'] = 'Жоба бағасы';
$_LANG['zc_shipping_fee'] = 'Тасымал ақысы';
$_LANG['zc_return_time'] = 'Болжалды өтелу уақыты';
$_LANG['zc_return_content'] = 'Өтелу мазмұны';
$_LANG['zc_return_detail'] = 'Жоба сәтті аяқталғаннан кейін %s күн ішінде';

$_LANG['set_grab_order'] = 'Таласу деп қою';
$_LANG['set_success'] = 'Сәтті бапталды';

//by wu
$_LANG['cannot_delete'] = 'Қосымша қайтару себептері бар,жойылмайды';
$_LANG['invoice_no_null'] = 'Трек-нөмірін енгізіңіз';

$_LANG['seckill'] = 'Seckill заказы';

//分单操作
$_LANG['split_action_note'] = "【Тауар коды：%s , жіберу：%s тал】";

/* 退换货原因 */
$_LANG['add_return_cause'] = "Қайтару себебін енгізу";
$_LANG['return_cause_list'] = "Себептер тізімі";
$_LANG['back_return_cause_list'] = "Себептер тізіміне қайту";
$_LANG['return_order_info'] = "Заказ инфосына қайту";
$_LANG['back_return_delivery_handle'] = "Қайтару атқаруларына қайту";

$_LANG['detection_list_notic'] = "Тек -заказды қабылдау керек уақыттан - қазіргі уақыттан кем заказды атқара аласыз";

$_LANG['refund_type_notic_one'] = "Ақша қайтару сәтсіз,қайтарған сома қайтару керек сомадан көп боп кетті";
$_LANG['refund_type_notic_two'] = "Ақша қайтару сәтсіз,сіздің шотыңыздың қарыз сомасы сенімділік лимитінен асып кетті,теңгерімді толтырыңыз немесе Oibuy-ға хабарласыңыз.";
$_LANG['refund_type_notic_three'] = "Бұл заказда қайтаратын тауар бар,тауарды қайтара алмайсыз";

$_LANG['batch_delivery_success'] = "Топпен сәтті жіберілді";
$_LANG['batch_delivery_failed'] = "Топпен жіберу сәтсіз,трек-нөмірін енгізіңіз";
$_LANG['inspect_order_type'] = "Заказдың күйін тексеріңіз";

$_LANG['order_return_prompt'] = "Ақша қайтару,заказды қайтару";
$_LANG['buy_integral'] = "сатып алған бонус";

/* js 验证提示 */
$_LANG['js_languages']['not_back_cause'] = 'Қайтару себебін енгізіңіз';
$_LANG['js_languages']['no_confirmation_delivery_info'] = 'Жіберуді растау дерегі табылмады';

/* order_step js*/
$_LANG['order_step_js_notic_01'] = 'Қабылдаушы аты-жөнін енгізіңіз';
$_LANG['order_step_js_notic_02'] = 'Мемлекетті таңдаңыз';
$_LANG['order_step_js_notic_03'] = 'Облыс/қаланы таңдаңыз';
$_LANG['order_step_js_notic_04'] = 'Қаланы таңдаңыз';
$_LANG['order_step_js_notic_05'] = 'Ауданды таңдаңыз';
$_LANG['order_step_js_notic_06'] = 'Мекен-жайды енгізіңіз';
$_LANG['order_step_js_notic_07'] = 'Тел нөмірді енгізіңіз';
$_LANG['order_step_js_notic_08'] = 'Тел нөмірі қате';
$_LANG['order_step_js_notic_09'] = 'Тауар қоры жеткіліксіз';
$_LANG['order_step_js_notic_10'] = 'Алдымен тауарды енгізіңіз';
$_LANG['order_step_js_notic_11'] = 'Сіз өзгерткен жеңілдік сомасы заказдың жалпы сомасынан көп болмауы керек';
$_LANG['order_step_js_notic_12'] = ',ақша қайтару туындауы мүмкін,егер ақша қайтару керек болса онда шотыңыздан тартылады';
$_LANG['order_step_js_notic_13'] = 'Сіз өзгерткен сомадан минус сан шықты（';
$_LANG['order_step_js_notic_14'] = ',жалғастырасыз ба?';

$_LANG['lab_bar_shop_price'] = 'Бағасы';
$_LANG['lab_bar_market_price'] = 'Базарда';

$_LANG['refund_way'] = 'Ақша қайтару жолы';
$_LANG['return_balance'] = 'Теңгерімге қайтару';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['return_cause_list'][0] = 'Порталдағы тауарды қайтару себептер тізімін баптау.';
$_LANG['operation_prompt_content']['return_cause_list'][1] = 'Қайтару себептерін жою немесе өзгерту.';

$_LANG['operation_prompt_content']['return_cause_info'][0] = 'Қазіргі бар себептің негізгі түрін таңдау.';

$_LANG['operation_prompt_content']['back_list'][0] = 'Порталдағы барлық қайтару заказдарын баптау.';
$_LANG['operation_prompt_content']['back_list'][1] = 'Заказ коды бойынша іздеуге болады,оң жақтағы жолақ бойынша кеңейтілген іздеу жасай аласыз.';

$_LANG['operation_prompt_content']['delivery_list'][0] = 'Порталдағы барлық жіберілген заказдар тізімін баптау.';
$_LANG['operation_prompt_content']['delivery_list'][1] = 'Заказ коды бойынша іздеуге болады,оң жақтағы жолақ бойынша кеңейтілген іздеу жасай аласыз.';
$_LANG['operation_prompt_content']['delivery_list'][2] = 'Жіберуді көруге және бас тартуға болады.';

$_LANG['operation_prompt_content']['detection_list'][0] = 'Порталдағы барлық жіберілген заказдарды тексере аласыз.';
$_LANG['operation_prompt_content']['detection_list'][1] = 'Заказ коды бойынша іздеуге болады,оң жақтағы жолақ бойынша кеңейтілген іздеу жасай аласыз.';
$_LANG['operation_prompt_content']['detection_list'][2] = 'Бір басып тауарды қабылдау функциясы：Бұл функция арқылы,тек қазіргі беттегі заказдарға ғана операция атқара аласыз,егерде бұдан да көп заказдарға атқару үшін,бір бетте көрінетін деректер санын өзгерту керек.';

$_LANG['operation_prompt_content']['list'][0] = 'Порталдағы бүкіл тауарлардың заказдар тізімі,порталдың өзі сатып жатқан тауарлары және сатушылардың заказдары көрсетіледі.';
$_LANG['operation_prompt_content']['list'][1] = 'Заказ кодын басу арқылы ақпарат бетіне кіріп заказға операциялар жасай аласыз.';
$_LANG['operation_prompt_content']['list'][2] = 'Tab арқылы әр күйдегі заказдарға ауыстыра аласыз,заказды іріктеп көруге ыңғайлы.';

$_LANG['operation_prompt_content']['search'][0] = 'Заказды іздеу де бірнеше шарттар қою арқылы тіптен нақты іздеуге болады.';

$_LANG['operation_prompt_content']['step'][0] = 'Заказды енгізу барысы：Порталдағы бір клиентті таңдау - тауар таңдап заказға енгізу - заказ сомасын растау - қабылдаушы ақпаратын енгізу - тасымал түрін енгізу - төлем түрін таңдау - фактура енгізу - қаражат ақпаратын көру - аяқтау';

$_LANG['operation_prompt_content']['return_list'][0] = 'Клиент тапсырған қайтару өтініштерін баптау';
$_LANG['operation_prompt_content']['return_list'][1] = 'Заказ коды бойынша іздеуге болады,оң жақтағы жолақ бойынша кеңейтілген іздеу жасай аласыз.';

$_LANG['operation_prompt_content']['templates'][0] = 'Заказ сәтті берілгеннен кейін,заказ ақпаратын басып шығаруға болады.';
$_LANG['operation_prompt_content']['templates'][1] = 'Үлгінің мәндерін албаты өзгертуге болмайды,әйтпесе деректер дұрыс көрсетілмей қалуы мүмкін.';
$_LANG['operation_prompt_content']['templates'][2] = 'Мәндер түсіндірмесі：
                    <p>$lang.***：Тілдер мәні,өзгерту жолы：Түпкөзі/languages/zh_cn/admin/order.php</p>
                    <p>$order.***：Заказ ақпараты, dsc_order дерек кестесіне қараңыз</p>
                    <p>$goods.***：Тауар ақпараты,dsc_goods дерек кестесіне қараңыз</p>';

$_LANG['stores_name'] = 'Бутик аты：';
$_LANG['stores_address'] = 'Мекен-жайы：';
$_LANG['stores_tel'] = 'Байланысы：';
$_LANG['stores_opening_hours'] = 'Жұмыс уақыты：';
$_LANG['stores_traffic_line'] = 'Қатынас жолдары：';
$_LANG['stores_img'] = 'Шынайы суреті：';
$_LANG['pick_code'] = 'Алу коды：';


// 商家后台
$_LANG['label_sure_collect_goods_time'] = 'Қабылдау уақыты：';
$_LANG['label_order_cate'] = 'Заказ түрі：';
$_LANG['label_id_code'] = 'ID коды：';
$_LANG['label_get_post_address'] = 'Қабылдау адрессі：';

$_LANG['js_languages']['jl_merge_order'] = 'Заказ біріктіру';
$_LANG['js_languages']['jl_merge'] = 'Біріктіру';
$_LANG['js_languages']['jl_sure_merge_order'] = 'Бұл екі заказды біріктіресіз бе?';
$_LANG['js_languages']['jl_order_step_js_notic_10'] = 'Өзгерткен жеңілдік ақысы тауардың жалпы сомасынан артық болмауы керек';
$_LANG['js_languages']['jl_order_step_js_notic_11'] = 'Өзгерткен жеңілдік ақысы заказдың жалпы сомасынан артық болмауы керек';
$_LANG['js_languages']['jl_order_step_js_notic_12'] = ',әйтпесе ақша қайтаруға тура келеді,ақша қайтарған кезде сіздің шоттан тартылады';
$_LANG['js_languages']['jl_order_step_js_notic_13'] = 'Сіз өзгерткен қаржының себебінен минус шот шығып тұр（';
$_LANG['js_languages']['jl_order_step_js_notic_14'] = ',жалғастырасыз ба?';
$_LANG['js_languages']['jl_order_export_dialog'] = 'Заказдан шыққан терезе';
$_LANG['js_languages']['jl_set_rob_order'] = 'Таласуға қою';
$_LANG['js_languages']['jl_vat_info'] = 'Салық фактурасы';
$_LANG['js_languages']['jl_search_logistics_info'] = 'Тасымалдың ізіне түсуде,күте тұрыңыз...';
$_LANG['js_languages']['jl_goods_delivery'] = 'Тауарды жіберу';
$_LANG['js_languages']['pls_input_should_return'] = 'қайтару керек сомадан асып кетті,максимум қайтару сомасы：';

$_LANG['add_order_step'][0] = 'Клиетті таңдаңыз';
$_LANG['add_order_step'][1] = 'Заказ тауарын таңдаңыз';
$_LANG['add_order_step'][2] = 'Мекен-жайын енгізіңіз';
$_LANG['add_order_step'][3] = 'Тасымал түрін таңдаңыз';
$_LANG['add_order_step'][4] = 'Төлем түрін таңдаңыз';
$_LANG['add_order_step'][5] = 'Фактура инфосын енгізіңіз';
$_LANG['add_order_step'][6] = 'Қаражат инфосын енгізіңіз';

$_LANG['print_shipping_form'] = 'Жіберу жапсырмасын шығару';
$_LANG['current'] = 'Қазіргі';
$_LANG['store_info'] = 'Бутик инфосы';

$_LANG['this_order_return_no_continue'] = 'Бұл заказ қайтару заказы деп растады,операцияны жалғастыра алмайсыз！';
$_LANG['no_info_fill_express_number'] = 'Қазірше ақпарат жоқ,трек-нөмірін енгізіңіз';

$_LANG['op_receive_goods'] = 'Қайтарған тауар қабылданды';
$_LANG['op_agree_apply'] = 'Өтінішті мақұлдау';
$_LANG['op_refound'] = 'Ақшаны қайтару';

$_LANG['average_coupons'] = "Ортақ купон сомасы：%s";
$_LANG['average_bonus'] = "Ортақ конверт сомасы：%s";
$_LANG['average_favourable'] = "Ортақ жеңілдік сомасы：%s";
$_LANG['average_value_card'] = "Ортақ төлем карта сомасы：%s";
$_LANG['average_card_discount'] = "Ортақ карта жеңілдік сомасы：%s";
$_LANG['average_goods_integral_money'] = "Ортақ бонус сомасы：%s";

$_LANG['label_value_card_discount'] = 'Карта жеңілдігі：';
$_LANG['label_goods_value_card'] = 'Төлем карта қамтылған сома：';

$_LANG['label_contain'] = "Тасымал қамтылған";
$_LANG['label_goods_paid_card'] = "Төлем карта сомасы";
$_LANG['label_among'] = "Ішінде";

$_LANG['label_return_val_card'] = "Төлем картаға қайтарылатын сома";
$_LANG['label_return_pay_money'] = "Қайтарылатын төлем сомасы";
$_LANG['label_return_integral_money'] = "Қайтарылатын бонус";
$_LANG['label_return_shipping_fee'] = "Қайтарылатын тасымал ақысы";

$_LANG['label_pay_actual_return'] = "Қайтарылған төлем сомасы";
$_LANG['label_return_is_shipping_fee'] = "Қайтарылған тасымал ақысы";
$_LANG['label_actual_value_card'] = "Қайтарылған карта сомасы";
$_LANG['label_integral_money'] = "Қайтарылған бонус сомасы";

$_LANG['order_team_ok'] = "【Бірлесу】Бірлесінді,бірлескен адам саны：%s";
$_LANG['order_team_false'] = "【Бірлесу】Бірлесінбеді,бірлескен адам саны：%s,әлі де керек：%s";

$_LANG['see_pay_document'] = "Аударым чегін көру";

return $_LANG;
