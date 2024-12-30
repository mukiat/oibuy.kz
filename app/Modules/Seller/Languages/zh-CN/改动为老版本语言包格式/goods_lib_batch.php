<?php

$_LANG['select_method'] = 'Тауар таңдау тәсілі：';
$_LANG['by_cat'] = 'Тауар түрі,брендті бойынша';
$_LANG['by_sn'] = 'Тауар коды бойынша';
$_LANG['select_cat'] = 'Тауар түрін таңдау';
$_LANG['select_brand'] = 'Брендтін таңдау';
$_LANG['goods_list'] = 'Тауар тізімі：';
$_LANG['src_list'] = 'Таңдайтындар';
$_LANG['dest_list'] = 'Таңдалғандар';
$_LANG['input_sn'] = 'Тауар кодын енгізу：<br />(Әр қатарға біреу)';
$_LANG['edit_method'] = 'Өңдеу түрі：';
$_LANG['edit_each'] = 'Бірден өңдеу';
$_LANG['edit_all'] = 'Біріңғай өңдеу';
$_LANG['go_edit'] = 'Өңдеуге өту';

$_LANG['notice_edit'] = 'VIP бағасы -1 болса, VIP бағасы VIP деңгейінің жеңілдік мөлшерлемесіне сәйкес есептелетінін білдіреді.';

$_LANG['goods_class'] = 'Тауар санаты';
$_LANG['g_class'][G_REAL] = 'Шынайы тауар';
$_LANG['g_class'][G_CARD] = 'Виртуалды тауар';

$_LANG['goods_sn'] = 'Коды';
$_LANG['goods_name'] = 'Тауар аты';
$_LANG['market_price'] = 'Нарық бағасы';
$_LANG['shop_price'] = 'Бағасы';
$_LANG['integral'] = 'Бонуспен алу';
$_LANG['give_integral'] = 'Силық бонус';
$_LANG['goods_number'] = 'Қоры';
$_LANG['brand'] = 'Бренд';

$_LANG['batch_edit_ok'] = 'Жаппай өзгеру сәтті';
$_LANG['goods_lib_batch_add'] = 'Жергілікті тауар қорының тауарын жаппай жүктеу';

$_LANG['export_format'] = 'Дерек форматы';
$_LANG['export_dscmall'] = 'Oibuy қолдайтын деректер форматтары';
$_LANG['goods_cat'] = 'Тиесілі түр：';
$_LANG['csv_file'] = 'Жаппай csv файлын жүктеу：';
$_LANG['notice_file'] = '（CSV файлында бір реткі тауарды жүктеу саны 40-тан аспауы керек,CSV файлы 500KB аспауы керек.）';
$_LANG['file_charset'] = 'Файл коды：';
$_LANG['download_file'] = 'Топпен CSV файлын жүктеп алу（%s）';
$_LANG['use_help'] = '<ul>' .
    '<li>Ыңғайыңызға қарай сәйкесті тілдегі csv файлын жүктеңіз,мысалы ҚР азаматы болсаңыз қазақ тілінде,басқа ел азаматы ағылшын тілінде；</li>' .
    '<li>csv файлды енгізіңіз,excel немесе мәтін редакторы арқылы csv файлын аша аласыз；<br />' .
    '“Үзік па”сияқтыларға кездескенде,0 немесе 1 деп енгізуге болады,0 “Жоқ”,1 “Иа”дегенді білдіреді；<br />' .
    'Тауар суреті мен тауар нобайын енгізген кезде сілтемесі бар сурет файлының атын енгізіңіз,сілтемесі[Түпкі каталог]/images/ сілтемесіне сәйкес болады,мысалы сурет сілтемесі [түпкі каталог]/images/200610/abc.jpg болса,онда тек 200610/abc.jpg деп енгізсеңіз болды；<br />' .
    '<li>Енгізген тауар суреті мен нобайын сәйкесті каталогқа жүктеңіз,Мы：[Түпкі каталог]/images/200610/；<br />' .
    '<font style="color:#FE596A;">Алдымен тауар суреті мен тауар нобайын жүктеңіз,сосын csv файлды жүктеңіз,әйтпесе сурет өңделмейді.</font></li>' .
    '<li>Жүктеген тауарыңыздың түрімен файл кодын таңдап,csv файлын жүктеңіз.</li>' .
    '</ul>';

$_LANG['js_languages']['please_select_goods'] = 'Тауарды таңдаңыз';
$_LANG['js_languages']['please_input_sn'] = 'Тауар кодын енгізіңіз';
$_LANG['js_languages']['goods_cat_not_leaf'] = 'Төменгі түрді таңдаңыз';
$_LANG['js_languages']['please_select_cat'] = 'Тиесілі түрді таңдаңыз';
$_LANG['js_languages']['please_upload_file'] = 'Жаппай csv файлын жүктеңіз';


$_LANG['batch_upload_ok'] = 'Жаппай жүктеу сәтті';
$_LANG['goods_upload_confirm'] = 'Жаппай жүктеуді растау';

// 批量上传商品库商品的字段
$_LANG['upload_goods_lib']['goods_name'] = 'Тауар аты';
$_LANG['upload_goods_lib']['goods_sn'] = 'Тауар коды';
$_LANG['upload_goods_lib']['brand_name'] = 'Брендті';   // 需要转换成brand_id
$_LANG['upload_goods_lib']['market_price'] = 'Нарық бағасы';
$_LANG['upload_goods_lib']['shop_price'] = 'Бағасы';
$_LANG['upload_goods_lib']['original_img'] = 'Негізгі фото';
$_LANG['upload_goods_lib']['goods_img'] = 'Тауар суреті';
$_LANG['upload_goods_lib']['goods_thumb'] = 'Тауар нобайы';
$_LANG['upload_goods_lib']['keywords'] = 'Кілтсөзі';
$_LANG['upload_goods_lib']['goods_brief'] = 'Сипаттау';
$_LANG['upload_goods_lib']['goods_desc'] = 'Нақты инфо';
$_LANG['upload_goods_lib']['goods_weight'] = 'Салмағы(Кг)';
$_LANG['upload_goods_lib']['is_on_sale'] = 'Сөреге';
$_LANG['upload_goods_lib']['is_real'] = 'Шынайы тауар ма';

/* 页面顶部操作提示 */
$_LANG['operation_prompt_content']['batch'][0] = 'Алдымен csv файлын жүктеңіз,excel-ды ашып бірнеше тауар инфосын енгізіңіз.';
$_LANG['operation_prompt_content']['batch'][1] = 'Өңделген csv файлын жүктеңіз,дерек форматын,түрін,кодын таңдап файлды жүктеңіз.';

$_LANG['operation_prompt_content']['confirm'][0] = 'Алдымен csv файлын жүктеңіз,excel-ды ашып бірнеше тауар инфосын енгізіңіз.';
$_LANG['operation_prompt_content']['confirm'][1] = 'Өңделген csv файлын жүктеңіз,дерек форматын,түрін,кодын таңдап файлды жүктеңіз.';

return $_LANG;
