<?php

return [

    /*
    |--------------------------------------------------------------------------
    | app Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the app
    |
    */

    'cp_home' => 'app管理中心',

    'errcode' => '錯誤代碼：',
    'errmsg' => '錯誤信息：',
    'empty' => '不能為空',
    'handler' => '操作',
    'enabled' => '啟用',
    'disabled' => '禁用',
    'already_enabled' => '已啟用',
    'already_disabled' => '已禁用',
    'to_enabled' => '點擊啟用',
    'to_disabled' => '點擊禁用',

    'add' => '添加',
    'yes' => '是',
    'no' => '否',
    'button_submit' => '提交',
    'button_reset' => '重置',
    'button_save' => '保存',

    'edit' => '編輯',
    'delete' => '刪除',
    'drop' => '卸載',
    'view' => '查看',
    'update' => '更新',
    'sort_order' => '排序',
    'status' => '狀態',
    'select_please' => '請選擇',

    'confirm_delete' => '確定刪除嗎？',
    'no_records' => '沒有找到任何記錄',

    'menu' => 'app管理',
    'app_config' => 'APP設置',


    // 廣告管理
    'ad_position_list' => '廣告位列表',
    'ad_position_info' => '廣告位信息',
    'update_position' => '添加廣告位',
    'search_position_name' => '搜索廣告位名稱',
    'position_name' => '廣告位名稱',
    'position_name_empty' => '廣告位名稱不能為空',
    'position_type' => '廣告位類型',
    'location_type' => '廣告位位置',
    'posit_width' => '寬度',
    'posit_height' => '高度',
    'position_desc' => '廣告位描述',
    'posit_style' => '廣告位模板',
    'unit_px' => '像素',
    'loading_screen' => 'APP首屏啟動頁',
    'menu_select_ap' => '請選擇廣告位類型',
    'view_ad' => '查看廣告',
    'ad_position_tips' => [
        0 => '先添加廣告位，再點擊查看廣告，進入廣告列表，添加廣告。'
    ],
    'forbid_delete_adp' => '此廣告位下有廣告，禁止刪除！',

    'ad_list' => '廣告列表',
    'ad_info' => '廣告信息',
    'ad_info_tips' => [
        0 => '上傳廣告圖片時請注意圖片尺寸問題。 如 APP首屏廣告圖 建議：寬750*高1920'
    ],
    'update_ad' => '添加廣告',
    'search_ad_name' => '搜索廣告名稱',
    'ad_name' => '廣告名稱',
    'ad_name_empty' => '廣告名稱不能為空',
    'ad_id' => '廣告id',
    'position_id' => '廣告位id',
    'media_type' => '廣告媒介類型',
    'click_count' => '點擊數',

    'ad_img' => '圖片',
    'ad_text' => '文字',
    'ad_link' => '廣告鏈接',
    'upfile_img' => '上傳廣告圖片',
    'img_url' => '或圖片網址',
    'ad_content' => '廣告內容',
    'upfile_img_notice' => '支持圖片格式 jpg,png',

    'ad_name_notic' => '廣告名稱只是作為辨別多個廣告條目之用，並不顯示在廣告中',

    'manage' => '管理',
    'client_manage' => '客戶端管理',
    'add_product' => '添加產品',
    'client_manage_li_0' => '客戶端產品管理，支持管理客戶端版本和刪除設置。',
    'client_delete_confirm' => '刪除客戶端同時會刪除當前客戶端下所有產品，確認刪除？',
    'client_name' => '應用名稱',
    'client_name_notice' => '請輸入APP應用名稱，例：【商城APP】iphone格式',
    'appid' => 'AppID',
    'add_client' => '添加客戶端產品',
    'edit_client' => '編輯產品信息',
    'client_push_list' => '版本推送列表',
    'client_product_list_li_0' => '安卓app版本升級添加',
    'client_product_list_li_1' => '產品信息：可查看/自定義修改應用產品信息；',
    'client_product_list_li_2' => '展示當前應用產品版本信息，可在列表內根據版本號搜索篩選；',
    'client_product_list_li_3' => '只針對安卓版本使用',
    'add_product' => '添加版本',
    'edit_product' => '編輯版本',
    'client_manage_name' => '客戶端產品應用名稱',
    'version_code' => '版本號',
    'update_desc' => '更新描述',
    'update_time' => '更新日期',
    'download_url' => '下載地址',
    'is_show' => '是否顯示',
    'client_product_info_li_0' => '根據應用實際更新內容填寫版本信息；',
    'version_code_notice' => '填寫版本號，推薦採用諸如1.0命名風格的版本號',
    'update_desc_notice' => '當前版本更新內容',
    'download_url_notice' => '當前版本下載地址，請填寫真實下載地址',
    'is_show_notice' => '選擇否，添加數據暫不推送檢測',
    'update_time_notice' => '請選擇日期',
    'not_null' => '不能為空',
    'duplicate_client_name_or_appid' => '應用名稱或APPID重複',
    'miss_params' => '參數缺失',
    'duplicate_version_code_or_download_url' => '版本號或下載地址重複',

    'app_config_tips' => [
        '可设置APP首页顶部左侧广告LOGO；',
        '支持添加APP首页顶部左侧LOGO图片和链接，未添加则前端不显示；',
        '请根据实际需求添加数据；',
    ],
    'app_top_img' => 'APP首页顶部图片',
    'app_top_img_desc' => '可在APP首页头部左侧显示LOGO图片',
    'app_top_url' => 'APP首页顶部链接',
];
