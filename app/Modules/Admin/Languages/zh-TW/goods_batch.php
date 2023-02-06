<?php

$_LANG['select_method'] = '選擇商品的方式：';
$_LANG['by_cat'] = '根據商品分類、品牌';
$_LANG['by_sn'] = '根據商品貨號';
$_LANG['select_cat'] = '選擇商品分類';
$_LANG['select_brand'] = '選擇商品品牌';
$_LANG['goods_list'] = '商品列表';
$_LANG['src_list'] = '待選列表';
$_LANG['dest_list'] = '已選列表';
$_LANG['input_sn'] = '輸入商品貨號(每行只能輸入一個貨號)';
$_LANG['edit_method'] = '編輯方式：';
$_LANG['edit_each'] = '逐個編輯';
$_LANG['edit_all'] = '統一編輯';
$_LANG['go_edit'] = '進入編輯';

$_LANG['notice_edit'] = '會員價格為-1表示會員價格將根據會員等級折扣比例計算';

$_LANG['goods_class'] = '商品類別';
$_LANG['g_class'][G_REAL] = '實體商品';
$_LANG['g_class'][G_CARD] = '虛擬卡';

$_LANG['goods_sn'] = '貨號';
$_LANG['goods_name'] = '商品名稱';
$_LANG['market_price'] = '市場價格';
$_LANG['shop_price'] = '本店價格';
$_LANG['integral'] = '積分購買';
$_LANG['give_integral'] = '贈送積分';
$_LANG['goods_number'] = '庫存';
$_LANG['brand'] = '品牌';
$_LANG['attribute'] = '屬性';

$_LANG['batch_edit_ok'] = '批量修改成功';
$_LANG['batch_edit_null'] = '請選擇商品';

$_LANG['export_format'] = '數據格式';
$_LANG['export_dscmall'] = 'dscmall支持數據格式';
$_LANG['goods_cat'] = '所屬分類：';
$_LANG['csv_file'] = '上傳批量csv文件：';
$_LANG['notice_file'] = '（CSV文件中一次上傳商品數量最好不要超過40，CSV文件大小最好不要超過500K.）';
$_LANG['file_charset'] = '文件編碼：';
$_LANG['download_file'] = '下載批量CSV文件（%s）';
$_LANG['use_help'] = '<ul>' .
    '<li>根據使用習慣，下載相應語言的csv文件，例如中國內地用戶下載簡體中文語言的文件，港台用戶下載繁體語言的文件；</li>' .
    '<li>填寫csv文件，可以使用excel或文本編輯器打開csv文件；<br />' .
    '碰到「是否精品」之類，填寫數字0或者1，0代表「否」，1代表「是」；<br />' .
    '商品圖片和商品縮略圖請填寫帶路徑的圖片文件名，其中路徑是相對於 [根目錄]/images/ 的路徑，例如圖片路徑為[根目錄]/images/200610/abc.jpg，只要填寫 200610/abc.jpg 即可；<br />' .
    '<font style="color:#FE596A;">如果是淘寶助理格式請確保cvs編碼為在網站的編碼，如編碼不正確，可以用編輯軟體轉換編碼。</font></li>' .
    '<li>將填寫的商品圖片和商品縮略圖上傳到相應目錄，例如：[根目錄]/images/200610/；<br />' .
    '<font style="color:#FE596A;">請首先上傳商品圖片和商品縮略圖再上傳csv文件，否則圖片無法處理。</font></li>' .
    '<li>選擇所上傳商品的分類以及文件編碼，上傳csv文件</li>' .
    '</ul>';

$_LANG['js_languages']['please_select_goods'] = '請您選擇商品';
$_LANG['js_languages']['please_input_sn'] = '請您輸入商品貨號';
$_LANG['js_languages']['goods_cat_not_leaf'] = '請選擇底級分類';
$_LANG['js_languages']['please_select_cat'] = '請您選擇所屬分類';
$_LANG['js_languages']['please_upload_file'] = '請您上傳批量csv文件';

// 批量上傳商品的欄位
$_LANG['upload_goods']['goods_name'] = '商品名稱';
$_LANG['upload_goods']['goods_sn'] = '商品貨號';
$_LANG['upload_goods']['brand_name'] = '商品品牌';   // 需要轉換成brand_id
$_LANG['upload_goods']['market_price'] = '市場售價';
$_LANG['upload_goods']['shop_price'] = '本店售價';
$_LANG['upload_goods']['cost_price'] = '成本價';
$_LANG['upload_goods']['integral'] = '積分購買額度';
$_LANG['upload_goods']['original_img'] = '商品原始圖';
$_LANG['upload_goods']['goods_img'] = '商品圖片';
$_LANG['upload_goods']['goods_thumb'] = '商品縮略圖';
$_LANG['upload_goods']['keywords'] = '商品關鍵詞';
$_LANG['upload_goods']['goods_brief'] = '簡單描述';
$_LANG['upload_goods']['goods_desc'] = '詳細描述';
$_LANG['upload_goods']['goods_weight'] = '商品重量（kg）';
$_LANG['upload_goods']['goods_number'] = '庫存數量';
$_LANG['upload_goods']['warn_number'] = '庫存警告數量';
$_LANG['upload_goods']['is_best'] = '是否精品';
$_LANG['upload_goods']['is_new'] = '是否新品';
$_LANG['upload_goods']['is_hot'] = '是否熱銷';
$_LANG['upload_goods']['is_on_sale'] = '是否上架';
$_LANG['upload_goods']['is_alone_sale'] = '能否作為普通商品銷售';
$_LANG['upload_goods']['is_real'] = '是否實體商品';

$_LANG['batch_upload_ok'] = '批量上傳成功';
$_LANG['goods_upload_confirm'] = '批量上傳確認';

// 批量上傳商品庫商品的欄位
$_LANG['upload_goods_lib']['goods_name'] = '商品名稱';
$_LANG['upload_goods_lib']['goods_sn'] = '商品貨號';
$_LANG['upload_goods_lib']['brand_name'] = '商品品牌';   // 需要轉換成brand_id
$_LANG['upload_goods_lib']['market_price'] = '市場售價';
$_LANG['upload_goods_lib']['shop_price'] = '本店售價';
$_LANG['upload_goods_lib']['original_img'] = '商品原始圖';
$_LANG['upload_goods_lib']['goods_img'] = '商品圖片';
$_LANG['upload_goods_lib']['goods_thumb'] = '商品縮略圖';
$_LANG['upload_goods_lib']['keywords'] = '商品關鍵詞';
$_LANG['upload_goods_lib']['goods_brief'] = '簡單描述';
$_LANG['upload_goods_lib']['goods_desc'] = '詳細描述';
$_LANG['upload_goods_lib']['goods_weight'] = '商品重量（kg）';
$_LANG['upload_goods_lib']['is_on_sale'] = '是否上架';
$_LANG['upload_goods_lib']['is_real'] = '是否實體商品';

/* 頁面頂部操作提示 */
$_LANG['operation_prompt_content']['confirm'][0] = '首先下載csv文件，打開excel表格添加多個商品信息欄位。';
$_LANG['operation_prompt_content']['confirm'][1] = '上傳編輯好的csv文件，選擇數據格式、分類、編碼進行上傳文件。';

$_LANG['operation_prompt_content']['select'][0] = '根據分類、品牌或者貨號搜索商品，在選擇的商品列表中選中需要批量編輯的商品。';
$_LANG['operation_prompt_content']['select'][1] = '選擇逐個編輯或是統一編輯，點擊進入編輯開始編輯。';
$_LANG['operation_prompt_content']['select'][2] = '逐個編輯可在選中商品編輯列表進行編輯，如修改市場價格、本地價格、贈送積分、庫存等信息。';
$_LANG['operation_prompt_content']['select'][3] = '統一編輯是選中需要統一信息的商品進行編輯。';

$_LANG['operation_prompt_content']['edit'][0] = '可從管理平台手動添加一名新會員，並填寫相關信息。';
$_LANG['operation_prompt_content']['edit'][1] = '新增會員後可從會員列表中找到該條數據，並再次進行編輯操作，但該會員名稱不可變。';

return $_LANG;
