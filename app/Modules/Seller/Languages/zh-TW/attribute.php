<?php

/* 列表 */
$_LANG['set_gcolor'] = '設置屬性顏色';

$_LANG['by_goods_type'] = '按商品類型顯示';
$_LANG['all_goods_type'] = '所有商品類型';

$_LANG['attr_id'] = '編號';
$_LANG['cat_id'] = '商品類型';
$_LANG['attr_name'] = '屬性名稱';
$_LANG['attr_input_type'] = '屬性值的錄入方式';
$_LANG['attr_values'] = '可選值列表';
$_LANG['attr_type'] = '購買商品時是否需要選擇該屬性的值';

$_LANG['value_attr_input_type'][ATTR_TEXT] = '手工錄入';
$_LANG['value_attr_input_type'][ATTR_OPTIONAL] = '從列表中選擇';
$_LANG['value_attr_input_type'][ATTR_TEXTAREA] = '多行文本框';

$_LANG['drop_confirm'] = '您確實要刪除該屬性嗎？';

/* 添加/編輯 */
$_LANG['label_attr_name'] = '屬性名稱：';
$_LANG['label_cat_id'] = '所屬商品類型：';
$_LANG['label_attr_index'] = '能否進行檢索：';
$_LANG['label_is_linked'] = '相同屬性值的商品是否關聯：';
$_LANG['no_index'] = '不需要檢索';
$_LANG['keywords_index'] = '關鍵字檢索';
$_LANG['range_index'] = '范圍檢索';
$_LANG['note_attr_index'] = '不需要該屬性成為檢索商品條件的情況請選擇不需要檢索，需要該屬性進行關鍵字檢索商品時選擇關鍵字檢索，如果該屬性檢索時希望是指定某個范圍時，選擇范圍檢索。';
$_LANG['label_attr_input_type'] = '該屬性值的錄入方式：';
$_LANG['text'] = '手工錄入';
$_LANG['select'] = '從下面的列表中選擇（一行代表一個可選值）';
$_LANG['text_area'] = '多行文本框';
$_LANG['label_attr_values'] = '可選值列表：';
$_LANG['label_attr_group'] = '屬性分組：';
$_LANG['label_attr_type'] = '屬性是否可選';
$_LANG['note_attr_type'] = '選擇"單選/復選屬性"時，可以對商品該屬性設置多個值，同時還能對不同屬性值指定不同的價格加價，用戶購買商品時需要選定具體的屬性值。選擇"唯一屬性"時，商品的該屬性值只能設置一個值，用戶只能查看該值。';
$_LANG['attr_type_values'][0] = '唯一屬性';
$_LANG['attr_type_values'][1] = '單選屬性';
$_LANG['attr_type_values'][2] = '復選屬性';


$_LANG['add_next'] = '添加下一個屬性';
$_LANG['back_list'] = '返回屬性列表';
$_LANG['edit_attr'] = '返回編輯屬性';

$_LANG['add_ok'] = '添加屬性 [%s] 成功。';
$_LANG['edit_ok'] = '編輯屬性 [%s] 成功。';

/* 提示信息 */
$_LANG['name_exist'] = '該屬性名稱已存在，請您換一個名稱。';
$_LANG['drop_confirm'] = '您確實要刪除該屬性嗎？';
$_LANG['notice_drop_confirm'] = '已經有%s個商品使用該屬性，您確實要刪除該屬性嗎？';
$_LANG['name_not_null'] = '屬性名稱不能為空。';
$_LANG['select_color'] = '請選擇顏色';
$_LANG['attr_color_edit_success'] = '屬性顏色修改成功';
$_LANG['edit_attr_img'] = '編輯屬性圖片';
$_LANG['back_attr'] = '返回該屬性';
$_LANG['edit_success'] = '編輯成功';

$_LANG['no_select_arrt'] = '您沒有選擇需要刪除的屬性名';
$_LANG['drop_ok'] = '成功刪除了 %d 條商品屬性';

$_LANG['js_languages']['name_not_null'] = '請您輸入屬性名稱。';
$_LANG['js_languages']['values_not_null'] = '請您輸入該屬性的可選值。';
$_LANG['js_languages']['cat_id_not_null'] = '請您選擇該屬性所屬的商品類型。';
$_LANG['js_languages']['js_name_not_null'] = '屬性名稱不能為空';

$_LANG['wai_href'] = '外鏈地址';
$_LANG['add_attribute_img'] = '添加屬性圖片';
$_LANG['add_attribute_color'] = '設置屬性顏色';

$_LANG['category_style'] = '分類篩選樣式';
$_LANG['category_style_one'] = '普通';
$_LANG['category_style_color'] = '顏色';

/* 頁面頂部操作提示 */
$_LANG['operation_prompt_content'][0] = '展示了一個商品類型下的商品屬性列表。';
$_LANG['operation_prompt_content'][1] = '可新增商品屬性。';
$_LANG['operation_prompt_content'][2] = '請按提示文案正確填寫信息。';

$_LANG['operation_prompt_content']['color'][0] = '填寫顏色信息';

// 商家後台
$_LANG['label_attr_filter_style'] = '分類篩選樣式：';

return $_LANG;
