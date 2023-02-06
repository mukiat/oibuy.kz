<?php

namespace App\Modules\Seller\Controllers;

use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsBookingManageService;

/**
 * 缺货处理管理程序
 */
class GoodsBookingController extends InitController
{
    protected $goodsBookingManageService;

    public function __construct(
        GoodsBookingManageService $goodsBookingManageService
    )
    {
        $this->goodsBookingManageService = $goodsBookingManageService;
    }

    public function index()
    {
        $menus = session('menus', '');
        $this->smarty->assign('menus', $menus);
        $this->smarty->assign('action_type', "order");
        admin_priv('booking');

        $adminru = get_admin_ru_id();

        $this->smarty->assign('menu_select', ['action' => '04_order', 'current' => '06_undispose_booking']);
        /*------------------------------------------------------ */
        //-- 列出所有订购信息
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'list_all') {
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['04_order']);
            $this->smarty->assign('current', '06_undispose_booking');
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['list_all']);
            $this->smarty->assign('full_page', 1);

            $list = $this->goodsBookingManageService->getBooKingList($adminru['ru_id']);

            // 分页
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('booking_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);


            return $this->smarty->display('booking_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 翻页、排序
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'query') {
            $page = isset($_REQUEST['page']) && !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

            $list = $this->goodsBookingManageService->getBooKingList($adminru['ru_id']);
            $this->smarty->assign('current', '06_undispose_booking');

            if ($adminru['ru_id'] == 0) {
                $this->smarty->assign('priv_ru', 1);
            } else {
                $this->smarty->assign('priv_ru', 0);
            }

            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('booking_list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            $sort_flag = sort_flag($list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('booking_list.dwt'),
                '',
                ['filter' => $list['filter'], 'page_count' => $list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 删除缺货登记
        /*------------------------------------------------------ */

        if ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('booking');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id']);

            $this->db->query("DELETE FROM " . $this->dsc->table('booking_goods') . " WHERE rec_id='$id'");

            $url = 'goods_booking.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }

        /*------------------------------------------------------ */
        //-- 显示详情
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'detail') {
            $id = intval($_REQUEST['id']);
            $this->smarty->assign('primary_cat', $GLOBALS['_LANG']['04_order']);
            $this->smarty->assign('send_fail', !empty($_REQUEST['send_ok']));
            $this->smarty->assign('booking', $this->get_booking_info($id));
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['detail']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['06_undispose_booking'], 'href' => 'goods_booking.php?act=list_all', 'class' => 'icon-reply']);
            return $this->smarty->display('booking_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 处理提交数据
        /*------------------------------------------------------ */
        if ($_REQUEST['act'] == 'update') {
            /* 权限判断 */
            admin_priv('booking');

            $rec_id = isset($_REQUEST['rec_id']) && !empty($_REQUEST['rec_id']) ? intval($_REQUEST['rec_id']) : 0;
            $dispose_note = !empty($_POST['dispose_note']) ? trim($_POST['dispose_note']) : '';

            $sql = "UPDATE  " . $this->dsc->table('booking_goods') .
                " SET is_dispose='1', dispose_note='$dispose_note', " .
                "dispose_time='" . gmtime() . "', dispose_user='" . session('seller_name') . "'" .
                " WHERE rec_id = '$rec_id'";
            $this->db->query($sql);

            $send_ok = 1;
            /* 邮件通知处理流程 */
            if (!empty($_POST['send_email_notice']) or isset($_POST['remail'])) {
                //获取邮件中的必要内容
                $sql = 'SELECT bg.email, bg.link_man, bg.goods_id, g.goods_name ' .
                    'FROM ' . $this->dsc->table('booking_goods') . ' AS bg, ' . $this->dsc->table('goods') . ' AS g ' .
                    "WHERE bg.goods_id = g.goods_id AND bg.rec_id = '$rec_id'";
                $booking_info = $this->db->getRow($sql);

                /* 设置缺货回复模板所需要的内容信息 */
                $template = get_mail_template('goods_booking');
                $goods_link = $this->dsc->seller_url() . 'goods.php?id=' . $booking_info['goods_id'];

                $this->smarty->assign('user_name', $booking_info['link_man']);
                $this->smarty->assign('goods_link', $goods_link);
                $this->smarty->assign('goods_name', $booking_info['goods_name']);
                $this->smarty->assign('dispose_note', $dispose_note);
                $this->smarty->assign('shop_name', "<a href='" . $this->dsc->seller_url() . "'>" . $GLOBALS['_CFG']['shop_name'] . '</a>');
                $this->smarty->assign('send_date', TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], gmtime()));

                $content = $this->smarty->fetch('str:' . $template['template_content']);

                /* 发送邮件 */
                if (CommonRepository::sendEmail($booking_info['link_man'], $booking_info['email'], $template['template_subject'], $content, $template['is_html'])) {
                    $send_ok = 0;
                } else {
                    $send_ok = 1;
                }
            }

            return dsc_header("Location: ?act=detail&id=" . $rec_id . "&send_ok=$send_ok\n");
        }
    }

    /**
     * 获得缺货登记的详细信息
     *
     * @param integer $id
     *
     * @return  array
     */
    private function get_booking_info($id)
    {
        $sql = "SELECT bg.rec_id, bg.user_id, IFNULL(u.user_name, '" . $GLOBALS['_LANG']['guest_user'] . "') AS user_name, " .
            "bg.link_man, g.goods_name, bg.goods_id, bg.goods_number, " .
            "bg.booking_time, bg.goods_desc,bg.dispose_user, bg.dispose_time, bg.email, " .
            "bg.tel, bg.dispose_note ,bg.dispose_user, bg.dispose_time,bg.is_dispose  " .
            "FROM " . $this->dsc->table('booking_goods') . " AS bg " .
            "LEFT JOIN " . $this->dsc->table('goods') . " AS g ON g.goods_id=bg.goods_id " .
            "LEFT JOIN " . $this->dsc->table('users') . " AS u ON u.user_id=bg.user_id " .
            "WHERE bg.rec_id ='$id'";

        $res = $this->db->GetRow($sql);

        /* 格式化时间 */
        $res['booking_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $res['booking_time']);
        if (!empty($res['dispose_time'])) {
            $res['dispose_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $res['dispose_time']);
        }

        return $res;
    }
}
