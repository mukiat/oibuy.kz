<?php

namespace App\Modules\Seller\Controllers;

use App\Models\Goods;
use App\Models\VirtualCard;
use App\Repositories\Common\TimeRepository;
use App\Services\VirtualCard\VirtualCardManageService;
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

        $act = e(request()->input('act', ''));

        $goods_id = (int)request()->input('goods_id', 0);

        $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['02_cat_and_goods']);
        $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '50_virtual_card_list']);

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
                'end_date' => TimeRepository::getLocalDate('Y-m-d', TimeRepository::getLocalStrtoTime('+1 year')),
            ];
            $this->smarty->assign('card', $card);

            $default_card_sn = time() . Str::random(6);
            $default_card_password = Str::random(6);
            $this->smarty->assign('default_card_sn', $default_card_sn);
            $this->smarty->assign('default_card_password', $default_card_password);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['replenish']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'virtual_card.php?act=card&goods_id=' . $card['goods_id'], 'class' => 'icon-reply']);
            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '01_goods_list']);
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
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'virtual_card.php?act=card&goods_id=' . $card['goods_id'], 'class' => 'icon-reply']);
            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '01_goods_list']);
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
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['virtual_card'] . "--" . $goods_name);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['replenish'], 'href' => 'virtual_card.php?act=replenish&goods_id=' . $goods_id, 'class' => 'icon-plus']);
            $this->smarty->assign('action_link2', ['text' => $GLOBALS['_LANG']['export'], 'href' => 'javascript:download_list();', 'class' => 'icon-download-alt']);
            $this->smarty->assign('goods_id', $goods_id);

            $list = $this->virtualCardManageService->get_replenish_list();

            $this->smarty->assign('card_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '01_goods_list']);
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
            $this->smarty->assign('menu_select', ['action' => '02_cat_and_goods', 'current' => '01_goods_list']);
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
        //-- 更改加密串
        /*------------------------------------------------------ */
        elseif ($act == 'change') {
            /* 检查权限 */
            admin_priv('virualcard');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['virtual_card_change']);

            return $this->smarty->display('virtual_card_change.dwt');
        }
        /*------------------------------------------------------ */
        //-- 提交更改
        /*------------------------------------------------------ */
        elseif ($act == 'submit_change') {
            /* 检查权限 */
            admin_priv('virualcard');

            $old_string = request()->input('old_string');
            $new_string = request()->input('new_string');

            if (isset($old_string) && isset($new_string)) {
                // 检查原加密串是否正确
                if ($old_string != OLD_AUTH_KEY) {
                    return sys_msg($GLOBALS['_LANG']['invalid_old_string'], 1);
                }

                // 检查新加密串是否正确
                if ($new_string != AUTH_KEY) {
                    return sys_msg($GLOBALS['_LANG']['invalid_new_string'], 1);
                }

                // 检查原加密串和新加密串是否相同
                if ($old_string == $new_string || crc32($old_string) == crc32($new_string)) {
                    return sys_msg($GLOBALS['_LANG']['same_string'], 1);
                }

                // 重新加密卡号和密码
                $old_crc32 = crc32($old_string);
                $new_crc32 = crc32($new_string);
                $res = $this->virtualCardManageService->get_virtual_card_by_crc32($old_crc32);

                if (!empty($res)) {
                    foreach ($res as $row) {
                        $row['card_password'] = dsc_encrypt(dsc_decrypt($row['card_password'], $old_string), $new_string);
                        $row['crc32'] = $new_crc32;
                        VirtualCard::where('card_id', $row['card_id'])->update($row);
                    }
                }

                // 记录日志
                //admin_log();

                // 返回
                return sys_msg($GLOBALS['_LANG']['change_key_ok'], 0, [['href' => 'virtual_card.php?act=list', 'text' => $GLOBALS['_LANG']['virtual_card_list']]]);
            }
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
        //-- 开始更改加密串：检查原串和新串
        /*------------------------------------------------------ */
        elseif ($act == 'start_change') {
            $old_key = request()->input('old_key', '');
            $new_key = request()->input('new_key', '');

            $old_key = json_str_iconv(trim($old_key));
            $new_key = json_str_iconv(trim($new_key));
            // 检查原加密串和新加密串是否相同
            if ($old_key == $new_key || crc32($old_key) == crc32($new_key)) {
                return make_json_error($GLOBALS['_LANG']['same_string']);
            }
            if ($old_key != AUTH_KEY) {
                return make_json_error($GLOBALS['_LANG']['invalid_old_string']);
            } else {
                $f = storage_public(DATA_DIR . '/config.php');
                file_put_contents($f, str_replace("'AUTH_KEY', '" . AUTH_KEY . "'", "'AUTH_KEY', '" . $new_key . "'", file_get_contents($f)));
                file_put_contents($f, str_replace("'OLD_AUTH_KEY', '" . OLD_AUTH_KEY . "'", "'OLD_AUTH_KEY', '" . $old_key . "'", file_get_contents($f)));
                @fclose($f);
            }

            // 查询统计信息：总记录，使用原串的记录，使用新串的记录，使用未知串的记录
            $stat = ['all' => 0, 'new' => 0, 'old' => 0, 'unknown' => 0];

            $res = VirtualCard::query()->groupby('crc32')->selectRaw('count(*) AS cnt')->addSelect('crc32')->get();
            $res = $res ? $res->toArray() : [];

            if (!empty($res)) {
                foreach ($res as $row) {
                    $stat['all'] += $row['cnt'];
                    if (crc32($new_key) == $row['crc32']) {
                        $stat['new'] += $row['cnt'];
                    } elseif (crc32($old_key) == $row['crc32']) {
                        $stat['old'] += $row['cnt'];
                    } else {
                        $stat['unknown'] += $row['cnt'];
                    }
                }

                return make_json_result(sprintf($GLOBALS['_LANG']['old_stat'], $stat['all'], $stat['new'], $stat['old'], $stat['unknown']));
            }

            return make_json_error($GLOBALS['_LANG']['action_fail']);
        }
        /*------------------------------------------------------ */
        //-- 更新加密串
        /*------------------------------------------------------ */
        elseif ($act == 'on_change') {
            // 重新加密卡号和密码
            $updated = (int)request()->input('updated', 0);

            $each_num = 1;
            $old_crc32 = crc32(OLD_AUTH_KEY);
            $new_crc32 = crc32(AUTH_KEY);

            $res = $this->virtualCardManageService->get_virtual_card_by_crc32($old_crc32, $each_num);

            foreach ($res as $row) {
                $row['card_password'] = dsc_encrypt(dsc_decrypt($row['card_password'], OLD_AUTH_KEY));
                $row['crc32'] = $new_crc32;

                $r = $this->virtualCardManageService->update_virtual_card($row['card_id'], $row);
                if (!$r) {
                    return make_json_error($updated, 0, $GLOBALS['_LANG']['update_error']);
                }

                $updated++;
            }

            // 查询是否还有未更新的
            $left_num = VirtualCard::where('crc32', $old_crc32)->count();
            if ($left_num > 0) {
                return make_json_result($updated);
            } else {
                // 查询统计信息
                $stat = ['new' => 0, 'unknown' => 0];
                $res = VirtualCard::query()->groupby('crc32')->selectRaw('count(*) AS cnt')->addSelect('crc32')->get();
                $res = $res ? $res->toArray() : [];

                foreach ($res as $row) {
                    if ($new_crc32 == $row['crc32']) {
                        $stat['new'] += $row['cnt'];
                    } else {
                        $stat['unknown'] += $row['cnt'];
                    }
                }

                return make_json_result($updated, sprintf($GLOBALS['_LANG']['new_stat'], $stat['new'], $stat['unknown']));
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

        /*------------------------------------------------------ */
        //-- 下载EXCEL报表
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'download') {
            $list = $this->virtualCardManageService->get_replenish_list();
            $data_list = $list['item'];

            /* 文件名 */
            $filename = __('admin::virtual_card.virtual_card_export_alt') . date('YmdHis');

            header("Content-type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=$filename.xls");

            /* 订单数量, 销售出商品数量, 销售金额 */
            echo dsc_iconv(EC_CHARSET, 'GB2312', __('admin::virtual_card.lab_goods_name')) . "\t";
            echo dsc_iconv(EC_CHARSET, 'GB2312', __('admin::virtual_card.lab_card_sn')) . "\t";
            echo dsc_iconv(EC_CHARSET, 'GB2312', __('admin::virtual_card.lab_card_password')) . "\t";
            echo dsc_iconv(EC_CHARSET, 'GB2312', __('admin::virtual_card.lab_end_date')) . "\t";
            echo dsc_iconv(EC_CHARSET, 'GB2312', __('admin::virtual_card.lab_is_saled')) . "\t";
            echo dsc_iconv(EC_CHARSET, 'GB2312', __('admin::virtual_card.lab_order_sn')) . "\t\n";

            foreach ($data_list as $data) {
                echo dsc_iconv(EC_CHARSET, 'GB2312', $data['goods_name']) . "\t";
                echo dsc_iconv(EC_CHARSET, 'GB2312', $data['card_sn']) . "\t";
                echo dsc_iconv(EC_CHARSET, 'GB2312', $data['card_password']) . "\t";
                echo dsc_iconv(EC_CHARSET, 'GB2312', $data['end_date']) . "\t";
                echo dsc_iconv(EC_CHARSET, 'GB2312', $data['is_saled_formated']) . "\t";
                echo dsc_iconv(EC_CHARSET, 'GB2312', !empty($data['order_sn']) ? '&' . $data['order_sn'] : '') . "\t";
                echo "\n";
            }
        }
    }


}
