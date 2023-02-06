<?php

namespace App\Modules\Admin\Controllers;

use App\Jobs\Export\PayCardExport;
use App\Models\PayCard;
use App\Models\PayCardType;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\PayCard\PayCardManageService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 充值卡的处理
 */
class PayCardController extends InitController
{
    protected $payCardManageService;

    public function __construct(
        PayCardManageService $payCardManageService
    )
    {
        $this->payCardManageService = $payCardManageService;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $act = e(request()->input('act', 'list'));

        $adminru = get_admin_ru_id();

        /*------------------------------------------------------ */
        //-- 充值卡列表页面
        /*------------------------------------------------------ */
        if ($act == 'list') {
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['pc_type_list']);


            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['pc_type_add'], 'href' => 'pay_card.php?act=add']);
            $this->smarty->assign('full_page', 1);
            $list = $this->payCardManageService->getTypeList();

            $this->smarty->assign('type_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);

            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('pc_type_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加/编辑充值卡类型页面
        /*------------------------------------------------------ */
        elseif ($act == 'add' || $act == 'edit') {
            if ($act == 'add') {
                $this->smarty->assign('form_act', 'insert');
                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['pc_type_add']);
                $next_month = TimeRepository::getLocalStrtoTime('+1 months');
                $bonus_arr['use_end_date'] = TimeRepository::getLocalDate('Y-m-d', $next_month);
                $this->smarty->assign('bonus_arr', $bonus_arr);
            } else {
                /* 获取充值卡类型数据 */
                $type_id = !empty($_GET['type_id']) ? intval($_GET['type_id']) : 0;

                $res = PayCardType::where('type_id', $type_id);
                $bonus_arr = BaseRepository::getToArrayFirst($res);

                $bonus_arr['use_end_date'] = TimeRepository::getLocalDate('Y-m-d', $bonus_arr['use_end_date']);

                $this->smarty->assign('ur_here', $GLOBALS['_LANG']['pc_type_edit']);
                $this->smarty->assign('form_act', 'update');
                $this->smarty->assign('bonus_arr', $bonus_arr);
            }

            $this->smarty->assign('lang', $GLOBALS['_LANG']);
            $this->smarty->assign('action_link', ['href' => 'value_card.php?act=list', 'text' => $GLOBALS['_LANG']['vc_type_list']]);
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            return $this->smarty->display('pc_type_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 添加/编辑充值卡类型处理
        /*------------------------------------------------------ */
        elseif ($act == 'insert' || $act == 'update') {
            $type_name = !empty($_POST['type_name']) ? trim($_POST['type_name']) : '';
            $type_money = !empty($_POST['type_money']) ? $_POST['type_money'] : 0;
            $type_id = !empty($_POST['type_id']) ? intval($_POST['type_id']) : 0;
            $type_prefix = !empty($_POST['type_prefix']) ? trim($_POST['type_prefix']) : 0;
            $use_enddate = TimeRepository::getLocalStrtoTime($_POST['use_end_date']);

            /* 检查类型是否有重复 */
            $res = PayCardType::where('type_name', $type_name)
                ->where('type_id', '<>', $type_id)
                ->count();

            if ($res > 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['type_name_exist'], 0, $link);
            }
            if ($type_id > 0) {
                $data = [
                    'type_name' => $type_name,
                    'type_money' => $type_money,
                    'type_prefix' => $type_prefix,
                    'use_end_date' => $use_enddate
                ];
                PayCardType::where('type_id', $type_id)->update($data);
                /* 提示信息 */
                $link[] = ['text' => $GLOBALS['_LANG']['back_list'], 'href' => 'pay_card.php?act=list&' . list_link_postfix()];
                return sys_msg($GLOBALS['_LANG']['edit'] . ' ' . $_POST['type_name'] . ' ' . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
            } else {
                /* 插入数据库。 */
                $data = [
                    'type_name' => $type_name,
                    'type_money' => $type_money,
                    'type_prefix' => $type_prefix,
                    'use_end_date' => $use_enddate
                ];
                PayCardType::insert($data);

                /* 提示信息 */
                $link[0]['text'] = $GLOBALS['_LANG']['continus_add'];
                $link[0]['href'] = 'pay_card.php?act=add';

                $link[1]['text'] = $GLOBALS['_LANG']['back_list'];
                $link[1]['href'] = 'pay_card.php?act=list';
                return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $_POST['type_name'] . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
            }
            /* 清除缓存 */
            clear_cache_files();
        }

        /*------------------------------------------------------ */
        //-- 删除充值卡类型
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $id = intval($_GET['id']);

            /* 删除充值卡类型 */
            PayCardType::where('type_id', $id)->delete();

            $url = 'pay_card.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 翻页、排序
        /*------------------------------------------------------ */
        if ($act == 'query') {
            $list = $this->payCardManageService->getTypeList();

            $this->smarty->assign('type_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('pc_type_list.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 充值卡发送页面
        /*------------------------------------------------------ */
        elseif ($act == 'send') {
            /* 取得参数 */
            $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['send_bonus']);
            $this->smarty->assign('action_link', ['href' => 'shoppingcard.php?act=list', 'text' => $GLOBALS['_LANG']['bonus_type']]);
            $this->smarty->assign('type_id', $id);
            $this->smarty->assign('type_list', get_pay_card_type($id));

            return $this->smarty->display('pay_card_send.dwt');
        }

        /*------------------------------------------------------ */
        //-- 按印刷品发放充值卡
        /*------------------------------------------------------ */
        elseif ($act == 'send_pay_card') {
            @set_time_limit(0);

            /* 储值卡类型和生成的数量的处理 */
            $tid = $_POST['type_id'] ? intval($_POST['type_id']) : 0;
            $send_sum = !empty($_POST['send_num']) ? intval($_POST['send_num']) : 1;
            $card_type = intval($_POST['card_type']);
            $password_type = intval($_POST['password_type']);

            $type_prefix = PayCardType::where('type_id', $tid)->value('type_prefix');
            $type_prefix = $type_prefix ? $type_prefix : '';

            $prefix_len = strlen($type_prefix);
            $length = $prefix_len + $card_type;

            /* 生成充值卡序列号 */
            $num = PayCard::selectRaw("MAX(SUBSTRING(card_number," . intval($prefix_len + 1) . ")) as card_number")
                ->whereRaw("c_id = '$tid' AND LENGTH(card_number) = '$length'")
                ->value('card_number');

            $num = $num ? intval($num) : 1;

            for ($i = 0, $j = 0; $i < $send_sum; $i++) {
                $card_number = $type_prefix . str_pad(mt_rand(0, 9999) + $num, $card_type, '0', STR_PAD_LEFT);
                $card_psd = strtoupper(mc_random($password_type));

                $data = [
                    'card_number' => $card_number,
                    'card_psd' => $card_psd,
                    'c_id' => $tid
                ];
                PayCard::insert($data);

                $j++;
            }

            /* 记录管理员操作 */
            admin_log($card_number, 'add', 'pay_card');

            /* 清除缓存 */
            clear_cache_files();

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_list'];
            $link[0]['href'] = 'pay_card.php?act=list';
            return sys_msg($GLOBALS['_LANG']['creat_value_card'] . $j . $GLOBALS['_LANG']['pay_card_num'], 0, $link);
        }


        /*------------------------------------------------------ */
        //--  充值卡列表
        /*------------------------------------------------------ */
        elseif ($act == 'pc_list') {
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['bonus_list']);
            $id = isset($_REQUEST['tid']) && !empty($_REQUEST['tid']) ? intval($_REQUEST['tid']) : 0;
            $this->smarty->assign('action_link', ['href' => 'pay_card.php?act=export_pc_list&id=' . $id, 'text' => $GLOBALS['_LANG']['export_pc_list']]);

            $list = $this->payCardManageService->getPayCardList();

            /* 赋值是否显示充值卡序列号 */
            $bonus_type = $this->payCardManageService->bonusTypeInfo(intval($id));

            $this->smarty->assign('show_bonus_sn', 1);

            $this->smarty->assign('bonus_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('bonus_type', $bonus_type);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);
            $this->smarty->assign('current_url', urlencode(request()->getRequestUri()));

            return $this->smarty->display('pay_card_view.dwt');
        }

        /* ------------------------------------------------------ */
        //-- 导出充值卡
        /* ------------------------------------------------------ */
        elseif ($act == 'export_pc_list') {
            $filter['id'] = request()->get('id', 0);

            $filter['ru_id'] = $adminru['ru_id'];
            $filter['type'] = 'pay_card';
            $filter['page_size'] = 100;
            $filter['file_name'] = date('YmdHis') . rand(1000, 9999);

            // 插入导出记录表
            $filter['request_id'] = DB::table('export_history')->insertGetId([
                'ru_id' => $filter['ru_id'],
                'type' => $filter['type'],
                'file_name' => $filter['file_name'],
                'file_type' => 'xls',
                'download_params' => json_encode($filter),
                'created_at' => Carbon::now(),
            ]);

            PayCardExport::dispatch($filter);

            return redirect()->route('admin/export_history', [
                'type' => $filter['type'],
                'callback' => urlencode(request()->header('referer'))
            ]);
        }

        /*------------------------------------------------------ */
        //-- 充值卡列表翻页、排序
        /*------------------------------------------------------ */
        elseif ($act == 'pc_query') {
            $list = $this->payCardManageService->getPayCardList();

            $this->smarty->assign('show_bonus_sn', 1);

            $this->smarty->assign('bonus_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('pay_card_view.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 删除充值卡
        /*------------------------------------------------------ */
        elseif ($act == 'remove_pc') {
            $id = intval($_GET['id']);

            PayCard::where('id', $id)->delete();

            $url = 'pay_card.php?act=pc_query&' . str_replace('act=remove_pc', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }
}
