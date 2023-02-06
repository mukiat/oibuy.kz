<?php

namespace App\Services\Gift;

use App\Models\EmailSendlist;
use App\Models\GiftGardLog;
use App\Models\GiftGardType;
use App\Models\Goods;
use App\Models\MailTemplates;
use App\Models\UserBonus;
use App\Models\UserGiftGard;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 *
 * Class GiftGardManageService
 * @package App\Services\Gift
 */
class GiftGardManageService
{
    protected $dscRepository;
    protected $merchantCommonService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 获取礼品卡类型列表
     *
     * @param int $ru_id
     * @return array
     * @throws \Exception
     */
    public function getGiftGardTypeList($ru_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getGiftGardTypeList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 获得所有礼品卡类型的发放数量 */
        $res = GiftGardType::selectRaw('gift_id,COUNT(*) AS sent_count')->groupBy('gift_id');
        $res = BaseRepository::getToArrayGet($res);

        $sent_arr = [];
        foreach ($res as $row) {
            $sent_arr[$row['gift_id']] = $row['sent_count'];
        }

        /* 过滤条件 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }

        /* 查询条件 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'gift_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);

        $res = GiftGardType::whereRaw(1);

        if ($ru_id) {
            $res = $res->where('ru_id', $ru_id);
        }

        if ((!empty($filter['keyword']))) {
            $keyword = $filter['keyword'];
            $res = $res->where(function ($query) use ($keyword) {
                $query->where('gift_name', 'LIKE', '%' . mysql_like_quote($keyword) . '%');
            });
        }

        if ($filter['review_status']) {
            $res = $res->where('review_status', $filter['review_status']);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        if ($filter['store_search'] > -1) {
            if ($ru_id == 0) {
                if ($filter['store_search'] > 0) {
                    $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                    if ($filter['store_search'] == 1) {
                        $res = $res->where('ru_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1) {
                        $res = $res->where(function ($query) use ($filter, $store_type) {
                            $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter, $store_type) {
                                if ($filter['store_search'] == 2) {
                                    $query = $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                }
                                if ($filter['store_search'] == 3) {
                                    if ($store_type) {
                                        $query = $query->where('shop_name_suffix', $store_type);
                                    }
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                }
                            });
                        });
                    }
                } else {
                    $res = $res->where('ru_id', 0);
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end
        //区分商家和自营
        if (!empty($filter['seller_list'])) {
            $res = $res->where('ru_id', '>', 0);
        } else {
            $res = $res->where('ru_id', 0);
        }

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $arr = [];

        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {
                $row['send_by'] = '';
                if (isset($row['send_type']) && !empty($row['send_type'])) {
                    $row['send_by'] = $GLOBALS['_LANG']['send_by'][$row['send_type']];
                }

                if (isset($row['type_id']) && !empty($row['type_id'])) {
                    $row['send_count'] = isset($sent_arr[$row['type_id']]) ? $sent_arr[$row['type_id']] : 0;
                } else {
                    $row['send_count'] = 0;
                }

                $row['shop_name'] = $merchantList[$row['ru_id']]['shop_name'] ?? '';

                $row['gift_count'] = UserGiftGard::where('gift_id', $row['gift_id'])->count();

                $row['effective_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['gift_start_date']) . '～' . TimeRepository::getLocalDate(config('shop.time_format'), $row['gift_end_date']);

                $arr[] = $row;
            }
        }

        $arr = ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 查询礼品卡列表
     *
     * @access  public
     * @param integer $type_id
     * @return  array
     */
    public function getGiftGardList($type_id)
    {
        $res = UserGiftGard::where('gift_gard_id', $type_id);
        $type_arr = BaseRepository::getToArrayFirst($res);

        $type_arr['config_goods_id'] = $type_arr['config_goods_id'] ?? '';
        $config_goods_id = BaseRepository::getExplode($type_arr['config_goods_id']);
        $res = Goods::select('goods_id', 'goods_name');
        $res = $res->whereIn('goods_id', $config_goods_id);
        $row = BaseRepository::getToArrayGet($res);

        return $row;
    }

    /**
     * 获取礼品卡列表
     *
     * @param int $ru_id
     * @return array
     */
    public function getUserGiftGardList($ru_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getUserGiftGardList';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识

        $res = UserGiftGard::whereRaw(1);

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
            if (!empty($filter['keywords'])) {
                $res = $res->where('gift_sn', $filter['keywords'])
                    ->orWhere('address', 'LIKE', '%' . $filter['keywords'] . '%')
                    ->orWhere('mobile', $filter['keywords'])
                    ->orWhere('consignee_name', $filter['keywords']);
            }
        }

        /* 查询条件 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'user_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['bonus_type'] = empty($_REQUEST['bonus_type']) ? 0 : intval($_REQUEST['bonus_type']);

        if (!empty($filter['bonus_type'])) {
            $res = $res->where('gift_id', $filter['bonus_type']);
        }


        if ($_REQUEST['act'] == 'bonus_list' || $_REQUEST['act'] == 'query_bonus' || $_REQUEST['act'] == 'export_gift_gard') {
            $filter['sort_by'] = 'gift_gard_id';
            $res = $res->where('is_delete', 1);
        }

        if (empty($_REQUEST['bonus_type'])) {
            $res = $res->where('status', '>', 0);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        //管理员查询的权限 -- 店铺查询 end
        $res = $res->whereHasIn('getGiftGardType');
        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        if ($_REQUEST['act'] == 'export_gift_gard' || $_REQUEST['act'] == 'take_excel') {
            $filter['sort_by'] = 'gift_gard_id';
            $filter['page_size'] = $filter['record_count'];
        }

        $res = $res->with(['getGiftGardType' => function ($query) {
            $query->select('gift_id', 'ru_id', 'gift_name', 'gift_menory');
        }]);
        $res = $res->with(['getUsers' => function ($query) {
            $query->select('user_id', 'user_name', 'email');
        }]);
        $res = $res->with(['getGoods' => function ($query) {
            $query->select('goods_id', 'goods_name');
        }]);
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])->offset($filter['start'])->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);

        if ($row) {

            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($row as $key => $val) {
                $val['ru_id'] = '';
                $val['gift_name'] = '';
                $val['gift_menory'] = '';
                $val['goods_name'] = '';

                if (isset($val['get_gift_gard_type']) && !empty($val['get_gift_gard_type'])) {
                    $val['ru_id'] = $val['get_gift_gard_type']['ru_id'];
                    $val['gift_name'] = $val['get_gift_gard_type']['gift_name'];
                    $val['gift_menory'] = $val['get_gift_gard_type']['gift_menory'];
                }

                $val['user_name'] = $val['get_users']['user_name'] ?? '';
                $val['email'] = $val['get_users']['email'] ?? '';

                if (config('shop.show_mobile') == 0) {
                    $val['user_name'] = $this->dscRepository->stringToStar($val['user_name']);
                    $val['mobile'] = $this->dscRepository->stringToStar($val['mobile']);
                }

                if (isset($val['get_goods']) && !empty($val['get_goods'])) {
                    $val['goods_name'] = $val['get_goods']['goods_name'];
                }

                $val['emailed'] = isset($val['emailed']) ? $GLOBALS['_LANG']['mail_status'][$val['emailed']] : '';

                if (!empty($val['user_time'])) {
                    $val['user_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['user_time']);
                } else {
                    $val['user_time'] = '';
                }

                $val['shop_name'] = $merchantList[$val['ru_id']]['shop_name'] ?? '';
                $row[$key] = $val;
            }
        }

        $arr = ['item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /**
     * 取得礼品卡类型信息
     * @param int $bonus_type_id 礼品卡类型id
     * @return  array
     */
    public function GiftGardTypeInfo($bonus_type_id)
    {
        $res = GiftGardType::where('gift_id', $bonus_type_id);
        $res = BaseRepository::getToArrayFirst($res);

        return $res;
    }

    /**
     * 发送礼品卡邮件
     * @param int $bonus_type_id 礼品卡类型id
     * @param array $bonus_id_list 礼品卡id数组
     * @return  int     成功发送数量
     */
    public function sendBonusMail($bonus_type_id, $bonus_id_list)
    {
        /* 取得礼品卡类型信息 */
        $bonus_type = $this->bonusTypeInfo($bonus_type_id);

        if ($bonus_type['send_type'] != SEND_BY_USER) {
            return 0;
        }

        /* 取得属于该类型的礼品卡信息 */
        $bonus_id_list = BaseRepository::getExplode($bonus_id_list);

        $res = UserBonus::select('bonus_id', 'user_id');
        $res = $res->whereIn('bonus_id', $bonus_id_list)
            ->where('order_id', 0);
        $res = $res->with(['getUsers' => function ($query) {
            $query = $query->select('user_id', 'user_name', 'email');
            $query->where('email', '<>', '');
        }]);
        $bonus_list = BaseRepository::getToArrayGet($res);

        if (empty($bonus_list)) {
            return 0;
        }

        /* 初始化成功发送数量 */
        $send_count = 0;

        /* 发送邮件 */
        $tpl = get_mail_template('send_bonus');
        $today = TimeRepository::getLocalDate(config('shop.time_format'), TimeRepository::getGmTime());
        foreach ($bonus_list as $bonus) {
            $bonus['user_name'] = '';
            $bonus['email'] = '';
            if (isset($bonus['get_users']) && !empty($bonus['get_users'])) {
                $bonus['user_name'] = $bonus['get_users']['user_name'];
                $bonus['email'] = $bonus['get_users']['email'];
            }

            $GLOBALS['smarty']->assign('user_name', $bonus['user_name']);
            $GLOBALS['smarty']->assign('shop_name', config('shop.shop_name'));
            $GLOBALS['smarty']->assign('send_date', $today);
            $GLOBALS['smarty']->assign('sent_date', $today);
            $GLOBALS['smarty']->assign('count', 1);
            $GLOBALS['smarty']->assign('money', $this->dscRepository->getPriceFormat($bonus_type['type_money']));

            $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
            if ($this->addToMailList($bonus['user_name'], $bonus['email'], $tpl['template_subject'], $content, $tpl['is_html'], false)) {
                $data = ['emailed' => BONUS_MAIL_SUCCEED];
                UserBonus::where('bonus_id', $bonus['bonus_id'])->update($data);

                $send_count++;
            } else {
                $data = ['emailed' => BONUS_MAIL_FAIL];
                UserBonus::where('bonus_id', $bonus['bonus_id'])->update($data);
            }
        }

        return $send_count;
    }

    public function addToMailList($username, $email, $subject, $content, $is_html)
    {
        $time = TimeRepository::getGmTime();
        $content = addslashes($content);

        $template_id = MailTemplates::where('template_code', 'send_bonus')->value('template_id');
        $template_id = $template_id ?? 0;

        $data = [
            'email' => $email,
            'template_id' => $template_id,
            'email_content' => $content,
            'pri' => 1,
            'last_send' => $time,
        ];
        EmailSendlist::insert($data);
        return true;
    }

    /**
     * 生成礼品卡密码
     * @param int $length
     * @return string
     *
     * @author guan
     */
    public function generatePassword($length = 6)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        $res = UserGiftGard::where('gift_password', $password)->count();
        if ($res > 0) {
            $password = $this->generatePassword(6);
        } else {
            return $password;
        }
    }

    /*操作日志  分页  by kong */
    public function getAdminGiftGardLog($id = 0)
    {
        $filter['id'] = 0;
        if ($id > 0) {
            $filter['id'] = $id;
        }

        $res = GiftGardLog::select('admin_id', 'id', 'addtime', 'delivery_status', 'gift_gard_id');
        $res = $res->where('gift_gard_id', $filter['id'])->where('handle_type', 'gift_gard');
        $filter['record_count'] = $res->count();

        $filter = page_and_size($filter);

        $res = $res->with(['getAdminUser' => function ($query) {
            $query = $query->select('user_id', 'user_name');
        }]);
        $res = $res->orderBy('addtime', 'DESC')->offset($filter['start'])->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);


        foreach ($row as $k => $v) {
            $v['user_name'] = '';
            if (isset($v['get_admin_user']) && !empty($v['get_admin_user'])) {
                $v['user_name'] = $v['get_admin_user']['user_name'];
            }
            if ($v['addtime'] > 0) {
                $v['add_time'] = TimeRepository::getLocalDate("Y-m-d  H:i:s", $v['addtime']);
            }
            if ($v['delivery_status'] == 1) {
                $v['delivery_status'] = $GLOBALS['_LANG']['status_ship_no'];
            } elseif ($v['delivery_status'] == 2) {
                $v['delivery_status'] = $GLOBALS['_LANG']['status_ship_ok'];
            }
            if ($v['gift_gard_id']) {
                $v['gift_sn'] = UserGiftGard::where('gift_gard_id', $v['gift_gard_id'])->value('gift_sn');
            }
            $row[$k] = $v;
        }

        $arr = ['pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }
}
