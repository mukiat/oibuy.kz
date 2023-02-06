<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Goods;
use App\Models\VirtualCard;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\OfficeService;
use App\Services\VirtualCard\VirtualCardManageService;
use App\Repositories\Common\FileSystemsRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 虚拟卡商品管理程序
 */
class VirtualCardController extends InitController
{
    protected $virtualCardManageService;

    public function __construct(
        VirtualCardManageService $virtualCardManageService
    )
    {
        $this->virtualCardManageService = $virtualCardManageService;
    }

    public function index()
    {
        load_helper('code');

        $admin_id = get_admin_id();
        $act = e(request()->input('act', ''));
        $goods_id = (int)request()->input('goods_id', 0);

        /*------------------------------------------------------ */
        //-- 补货处理
        /*------------------------------------------------------ */
        if ($act == 'replenish') {

            /* 检查权限 */
            admin_priv('virualcard');

            /* 验证goods_id是否合法 */
            if (empty($goods_id)) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'virtual_card.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['replenish_no_goods_id'], 1, $link);
            } else {
                $goods_name = Goods::where('goods_id', $goods_id)->where('is_real', 0)->where('extension_code', 'virtual_card')->value('goods_name');
                if (empty($goods_name)) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'virtual_card.php?act=list'];
                    return sys_msg($GLOBALS['_LANG']['replenish_no_get_goods_name'], 1, $link);
                }
            }

            /*输出日期 by wu start*/
            $year = TimeRepository::getLocalDate('Y');
            $month = TimeRepository::getLocalDate('m');
            $day = TimeRepository::getLocalDate('d');
            $data_year = [];
            $data_month = [];
            $data_day = [];
            for ($i = 0; $i < 10; $i++) {
                $data_year[] = $year + $i;
            }
            for ($i = 1; $i <= 12; $i++) {
                $data_month[] = sprintf('%02d', $i);
            }
            for ($i = 1; $i <= 31; $i++) {
                $data_day[] = sprintf('%02d', $i);
            }
            $data_time = ['year' => $year + 1, 'month' => $month, 'day' => $day];
            $this->smarty->assign('data_year', $data_year);
            $this->smarty->assign('data_month', $data_month);
            $this->smarty->assign('data_day', $data_day);
            $this->smarty->assign('data_time', $data_time);
            /*输出日期 by wu end*/

            $card = [
                'goods_id' => $goods_id,
                'goods_name' => $goods_name,
                'end_date' => TimeRepository::getLocalDate('Y-m-d', TimeRepository::getLocalStrtoTime('+1 year'))
            ];
            $this->smarty->assign('card', $card);

            $default_card_sn = time() . Str::random(6);
            $default_card_password = Str::random(6);
            $this->smarty->assign('default_card_sn', $default_card_sn);
            $this->smarty->assign('default_card_password', $default_card_password);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['replenish']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'virtual_card.php?act=card&goods_id=' . $card['goods_id']]);
            return $this->smarty->display('replenish_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 编辑补货信息
        /*------------------------------------------------------ */
        elseif ($act == 'edit_replenish') {
            /* 检查权限 */
            admin_priv('virualcard');

            /* 获取卡片信息 */
            $card_id = (int)request()->input('card_id', 0);

            $card = $this->virtualCardManageService->virtual_card_info($card_id);

            if (!empty($card)) {
                if ($card['crc32'] == 0 || $card['crc32'] == crc32(AUTH_KEY)) {
                    $card['card_password'] = dsc_decrypt($card['card_password']);
                } elseif ($card['crc32'] == crc32(OLD_AUTH_KEY)) {
                    $card['card_password'] = dsc_decrypt($card['card_password'], OLD_AUTH_KEY);
                } else {
                    $card['card_sn'] = '***';
                    $card['card_password'] = '***';
                }

                $card['goods_name'] = $card['get_goods']['goods_name'] ?? '';
            }

            /*输出日期 by wu start*/
            $year = TimeRepository::getLocalDate('Y');
            $month = TimeRepository::getLocalDate('m');
            $day = TimeRepository::getLocalDate('d');
            $data_year = [];
            $data_month = [];
            $data_day = [];
            for ($i = 0; $i < 10; $i++) {
                $data_year[] = $year + $i;
            }
            for ($i = 1; $i <= 12; $i++) {
                $data_month[] = sprintf('%02d', $i);
            }
            for ($i = 1; $i <= 31; $i++) {
                $data_day[] = sprintf('%02d', $i);
            }
            $data_time = ['year' => TimeRepository::getLocalDate('Y', $card['end_date']), 'month' => TimeRepository::getLocalDate('m', $card['end_date']), 'day' => TimeRepository::getLocalDate('d', $card['end_date'])];
            $this->smarty->assign('data_year', $data_year);
            $this->smarty->assign('data_month', $data_month);
            $this->smarty->assign('data_day', $data_day);
            $this->smarty->assign('data_time', $data_time);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['replenish']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'virtual_card.php?act=card&goods_id=' . $card['goods_id']]);
            $this->smarty->assign('card', $card);
            return $this->smarty->display('replenish_info.dwt');
        }
        /*------------------------------------------------------ */
        //-- 编辑补货信息提交
        /*------------------------------------------------------ */
        elseif ($act == 'action') {
            /* 检查权限 */
            admin_priv('virualcard');

            $card_id = (int)request()->input('card_id', 0);

            $card_sn = request()->input('card_sn', '');
            $old_card_sn = request()->input('old_card_sn', '');
            $card_password = request()->input('card_password', '');

            /* 加密后的 */
            $coded_card_sn = trim($card_sn); // 不加密卡号 可模糊搜索
            $coded_card_password = !empty($card_password) ? dsc_encrypt($card_password) : '';

            /* 在前后两次card_sn不一致时，检查是否有重复记录,一致时直接更新数据 */
            if ($card_sn != trim($old_card_sn)) {
                $card_sn_count = $this->virtualCardManageService->card_sn_count($goods_id, $coded_card_sn);
                if ($card_sn_count > 0) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'virtual_card.php?act=replenish&goods_id=' . $goods_id];
                    return sys_msg(sprintf($GLOBALS['_LANG']['card_sn_exist'], $_POST['card_sn']), 1, $link);
                }
            }

            $end_dateYear = request()->input('end_dateYear', '');
            $end_dateMonth = request()->input('end_dateMonth', '');
            $end_dateDay = request()->input('end_dateDay', '');

            /* 如果old_card_sn不存在则新加一条记录 */
            if (empty($old_card_sn)) {
                /* 插入一条新记录 */
                $end_date = TimeRepository::getLocalStrtoTime($end_dateYear . "-" . $end_dateMonth . "-" . $end_dateDay);
                $add_date = TimeRepository::getGmTime();

                $data = [
                    'goods_id' => $goods_id,
                    'card_sn' => $card_sn,
                    'card_password' => $coded_card_password,
                    'end_date' => $end_date,
                    'add_date' => $add_date,
                    'crc32' => crc32(AUTH_KEY),
                ];
                $this->virtualCardManageService->create_virtual_card($data);

                // 更新商品库存
                $this->virtualCardManageService->update_goods_number($goods_id);

                $link[] = ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'virtual_card.php?act=card&goods_id=' . $goods_id];
                $link[] = ['text' => $GLOBALS['_LANG']['continue_add'], 'href' => 'virtual_card.php?act=replenish&goods_id=' . $goods_id];
                return sys_msg($GLOBALS['_LANG']['action_success'], 0, $link);
            } else {
                /* 更新数据 */
                $end_date = TimeRepository::getLocalStrtoTime($end_dateYear . "-" . $end_dateMonth . "-" . $end_dateDay);

                $updata = [
                    'card_sn' => $coded_card_sn,
                    'card_password' => $coded_card_password,
                    'end_date' => $end_date,
                ];
                $this->virtualCardManageService->update_virtual_card($card_id, $updata);

                // 更新商品库存
                $this->virtualCardManageService->update_goods_number($goods_id);

                $link[] = ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'virtual_card.php?act=card&goods_id=' . $goods_id];
                $link[] = ['text' => $GLOBALS['_LANG']['continue_add'], 'href' => 'virtual_card.php?act=replenish&goods_id=' . $goods_id];
                return sys_msg($GLOBALS['_LANG']['action_success'], 0, $link);
            }
        }
        /*------------------------------------------------------ */
        //-- 补货列表
        /*------------------------------------------------------ */
        elseif ($act == 'card') {
            /* 检查权限 */
            admin_priv('virualcard');

            /* 验证goods_id是否合法 */
            if (empty($goods_id)) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'virtual_card.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['replenish_no_goods_id'], 1, $link);
            } else {
                $goods_name = Goods::where('goods_id', $goods_id)->where('is_real', 0)->where('extension_code', 'virtual_card')->value('goods_name');
                if (empty($goods_name)) {
                    $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'virtual_card.php?act=list'];
                    return sys_msg($GLOBALS['_LANG']['replenish_no_get_goods_name'], 1, $link);
                }
            }

            $this->smarty->assign('goods_id', $goods_id);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('ur_here', $goods_name);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['replenish'], 'href' => 'virtual_card.php?act=replenish&goods_id=' . $goods_id]);
            $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['export_excel'], 'href' => 'javascript:download_list();']);
            $this->smarty->assign('goods_id', $goods_id);

            $list = $this->virtualCardManageService->get_replenish_list();

            $this->smarty->assign('card_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return $this->smarty->display('replenish_list.dwt');
        }
        /*------------------------------------------------------ */
        //-- 虚拟卡列表，用于排序、翻页
        /*------------------------------------------------------ */
        elseif ($act == 'query_card') {

            $list = $this->virtualCardManageService->get_replenish_list();

            $this->smarty->assign('card_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('replenish_list.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]);
        }
        /*------------------------------------------------------ */
        //-- 批量删除card
        /*------------------------------------------------------ */
        elseif ($act == 'batch_drop_card') {
            /* 检查权限 */
            admin_priv('virualcard');

            $checkboxes = request()->input('checkboxes', []);
            if (empty($checkboxes)) {
                return sys_msg($GLOBALS['_LANG']['action_fail'], 1);
            }

            $num = count($checkboxes);

            $checkboxes = !is_array($checkboxes) ? explode(',', $checkboxes) : $checkboxes;

            $res = $this->virtualCardManageService->batch_delete_virtual_card($checkboxes);

            if ($res) {
                /* 更新商品库存 */
                $this->virtualCardManageService->update_goods_number($goods_id);
                $link[] = ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'virtual_card.php?act=card&goods_id=' . $goods_id];
                return sys_msg($GLOBALS['_LANG']['action_success'], 0, $link);
            }
        }
        /*------------------------------------------------------ */
        //-- 批量上传页面
        /*------------------------------------------------------ */
        elseif ($act == 'batch_card_add') {
            /* 检查权限 */
            admin_priv('virualcard');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['batch_card_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['virtual_card_list'], 'href' => 'goods.php?act=list&extension_code=virtual_card']);
            $this->smarty->assign('goods_id', $goods_id);
            return $this->smarty->display('batch_card_info.dwt');
        } elseif ($act == 'batch_confirm') {
            /* 检查上传是否成功 */
            if ($_FILES['uploadfile']['tmp_name'] == '' || $_FILES['uploadfile']['tmp_name'] == 'none') {
                return sys_msg($GLOBALS['_LANG']['uploadfile_fail'], 1);
            }

            $data = file($_FILES['uploadfile']['tmp_name']);
            $rec = []; //数据数组
            $i = 0;
            $separator = trim($_POST['separator']);
            foreach ($data as $line) {
                $row = explode($separator, $line);
                switch (count($row)) {
                    case '3':
                        $rec[$i]['end_date'] = $row[2];
                    // no break
                    case '2':
                        $rec[$i]['card_password'] = $row[1];
                    // no break
                    case '1':
                        $rec[$i]['card_sn'] = $row[0];
                        break;
                    default:
                        $rec[$i]['card_sn'] = $row[0];
                        $rec[$i]['card_password'] = $row[1];
                        $rec[$i]['end_date'] = $row[2];
                        break;
                }
                $i++;
            }

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['batch_card_add']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['batch_card_add'], 'href' => 'virtual_card.php?act=batch_card_add&goods_id=' . $goods_id]);
            $this->smarty->assign('list', $rec);
            return $this->smarty->display('batch_card_confirm.dwt');
        }
        /*------------------------------------------------------ */
        //-- 批量上传处理
        /*------------------------------------------------------ */
        elseif ($act == 'batch_insert') {
            /* 检查权限 */
            admin_priv('virualcard');

            $checked = request()->input('checked', []);
            if (empty($checked)) {
                return sys_msg($GLOBALS['_LANG']['action_fail'], 1);
            }

            $card_sn_arr = request()->input('card_sn', []);
            $card_password_arr = request()->input('card_password', []);
            $end_date_arr = request()->input('end_date', []);

            $add_time = TimeRepository::getGmTime();
            $i = 0;
            foreach ($checked as $key) {
                $rec['card_sn'] = addslashes($card_sn_arr[$key]);
                $rec['card_password'] = dsc_encrypt($card_password_arr[$key]);
                $rec['crc32'] = crc32(AUTH_KEY);
                $rec['end_date'] = empty($end_date_arr[$key]) ? 0 : TimeRepository::getLocalStrtoTime($end_date_arr[$key]);
                $rec['goods_id'] = $goods_id;
                $rec['add_date'] = $add_time;
                VirtualCard::insert($rec);
                $i++;
            }

            /* 更新商品库存 */
            $this->virtualCardManageService->update_goods_number($goods_id);
            $link[] = ['text' => $GLOBALS['_LANG']['card'], 'href' => 'virtual_card.php?act=card&goods_id=' . $goods_id];
            return sys_msg(sprintf($GLOBALS['_LANG']['batch_card_add_ok'], $i), 0, $link);
        }
        /*------------------------------------------------------ */
        //-- 切换是否已出售状态
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_sold') {
            $check_auth = check_authz_json('virualcard');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $card_id = (int)request()->input('id', 0);
            $val = (int)request()->input('val', 0);

            if (empty($card_id)) {
                return make_json_error($GLOBALS['_LANG']['action_fail']);
            }

            $data = [
                'is_saled' => $val
            ];
            $res = $this->virtualCardManageService->update_virtual_card($card_id, $data);

            if ($res) {
                // 更新商品库存
                $goods_id = VirtualCard::where('card_id', $card_id)->value('goods_id');
                $this->virtualCardManageService->update_goods_number($goods_id);

                return make_json_result($val);
            } else {
                return make_json_error($GLOBALS['_LANG']['action_fail']);
            }
        }
        /*------------------------------------------------------ */
        //-- 删除卡片
        /*------------------------------------------------------ */
        elseif ($act == 'remove_card') {
            $check_auth = check_authz_json('virualcard');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $card_id = (int)request()->input('id', 0);
            if (empty($card_id)) {
                return make_json_error($GLOBALS['_LANG']['action_fail']);
            }

            $goods_id = VirtualCard::where('card_id', $card_id)->value('goods_id');

            $res = VirtualCard::where('card_id', $card_id)->delete();
            if ($res) {
                /* 更新商品库存 */
                $this->virtualCardManageService->update_goods_number($goods_id);

                $url = 'virtual_card.php?act=query_card&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

                return dsc_header("Location: $url\n");
            } else {
                return make_json_error($this->db->error());
            }
        }
        /*------------------------------------------------------ */
        //-- 查看密码
        /*------------------------------------------------------ */
        elseif ($act == 'view_password') {
            $check_auth = check_authz_json('virualcard');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = (int)request()->input('id', 0);

            $password = $this->virtualCardManageService->getPassword($id);

            return make_json_result($password);
        }

        /* ------------------------------------------------------ */
        //--Excel文件下载数组处理
        /* ------------------------------------------------------ */
        elseif ($act == 'ajax_download') {
            $result = ['is_stop' => 0];

            $page = (int)request()->input('page_down', 0); //处理的页数
            $page_count = (int)request()->input('page_count', 0); //总页数

            $list = $this->virtualCardManageService->get_replenish_list();

            $merchants_download_content = cache("virtual_download_content_" . $page . "_" . $admin_id);
            $merchants_download_content = !is_null($merchants_download_content) ? $merchants_download_content : [];

            $merchants_download_content = $list;

            cache()->forever("virtual_download_content_" . $page . "_" . $admin_id, $merchants_download_content);

            $result['page'] = $page;
            $result['page_count'] = $page_count;
            if ($page < $page_count) {
                $result['is_stop'] = 1;
                $result['next_page'] = $page + 1;
            }
            return response()->json($result);
        }
        /* ------------------------------------------------------ */
        //--导出当前分页订单csv文件
        /* ------------------------------------------------------ */
        elseif ($act == 'download_list_csv') {

            $page = (int)request()->input('page_down', 0); //处理的页数
            $page_count = (int)request()->input('page_count', 0); //总页数

            // 获取所有商家的下载数据 按照商家分组
            $list = cache("virtual_download_content_" . $page . "_" . $admin_id);
            $list = !is_null($list) ? $list : [];

            if (!empty($list)) {
                // 卡片名称（虚拟卡名称）、卡片序号、卡片密码、截止使用日期、是否使用、订单号
                // 需要导出字段名称
                $head = [
                    ['column_name' => __('admin::virtual_card.lab_goods_name')],
                    ['column_name' => __('admin::virtual_card.lab_card_sn')],
                    ['column_name' => __('admin::virtual_card.lab_card_password')],
                    ['column_name' => __('admin::virtual_card.lab_end_date')],
                    ['column_name' => __('admin::virtual_card.lab_is_saled')],
                    ['column_name' => __('admin::virtual_card.lab_order_sn')],
                ];

                // 需要导出字段 须和查询数据里的字段名保持一致
                $fields = [
                    'goods_name',
                    'card_sn',
                    'card_password',
                    'end_date',
                    'is_saled_formated',
                    'order_sn'
                ];

                $list = $list['item'];

                // 文件名
                $title = date('YmdHis');

                $spreadsheet = new OfficeService();

                // 文件下载目录
                $dir = 'data/attached/file/';
                $file_path = storage_public($dir);
                if (!is_dir($file_path)) {
                    Storage::disk('public')->makeDirectory($dir);
                }

                $options = [
                    'savePath' => $file_path, // 指定文件下载目录
                ];

                // 默认样式
                $spreadsheet->setDefaultStyle();

                // 文件名按分页命名
                $out_title = $title . '-' . $page;

                if ($list) {
                    $spreadsheet->exportExcel($out_title, $head, $fields, $list, $options);
                }
                // 关闭
                $spreadsheet->disconnect();
            }
            /* 清除缓存 */
            cache()->forget('virtual_download_content_' . $page . "_" . $admin_id);

            if ($page < $page_count) {
                $result['is_stop'] = 1;//
            } else {
                $result['is_stop'] = 0;
            }
            $result['error'] = 1;
            $result['page'] = $page;

            return response()->json($result);
        }
        /* ------------------------------------------------------ */
        //--Excel文件下载 订单下载压缩文件
        /* ------------------------------------------------------ */
        elseif ($act == 'virtual_card_download') {

            // 文件下载目录
            $dir = 'data/attached/file/';
            $zip_name = __('admin::virtual_card.virtual_card_export_alt') . date('YmdHis') . ".zip";

            $zip_file = FileSystemsRepository::download_zip($dir, $zip_name);

            if ($zip_file) {
                return response()->download($zip_file)->deleteFileAfterSend(); // 下载完成删除zip压缩包
            }

            return back()->withInput(); // 返回
        }
    }
}
