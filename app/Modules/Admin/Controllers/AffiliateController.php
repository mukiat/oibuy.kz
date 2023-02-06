<?php

namespace App\Modules\Admin\Controllers;

use App\Repositories\Common\BaseRepository;
use Illuminate\Support\Facades\DB;

/**
 * 推荐分成管理
 *
 * Class AffiliateController
 * @package App\Modules\Admin\Controllers
 */
class AffiliateController extends InitController
{
    public function index()
    {
        admin_priv('affiliate');

        $act = e(request()->input('act', 'list'));

        $config = $this->getAffiliateConfig();

        /*------------------------------------------------------ */
        //-- 分成管理页
        /*------------------------------------------------------ */
        if ($act == 'list') {
            if (empty($_REQUEST['is_ajax'])) {
                $this->smarty->assign('full_page', 1);
            }

            if ($config) {
                // 默认推荐说明
                $config['config']['separate_desc'] = $config['config']['separate_desc'] ?? trans('admin::affiliate.separate_desc_content');
            }

            $this->smarty->assign('ur_here', trans('admin::common.affiliate'));
            $this->smarty->assign('config', $config);
            return $this->smarty->display('affiliate.dwt');
        } elseif ($act == 'query') {
            $this->smarty->assign('ur_here', trans('admin::common.affiliate'));
            $this->smarty->assign('config', $config);
            return make_json_result($this->smarty->fetch('affiliate.dwt'), '', null);
        }
        /*------------------------------------------------------ */
        //-- 增加下线分成方案
        /*------------------------------------------------------ */
        elseif ($act == 'add') {
            if (request()->isMethod('POST') && count($config['item']) < 5) {

                //下线不能超过5层
                $_POST['level_point'] = (float)$_POST['level_point'];
                $_POST['level_money'] = (float)$_POST['level_money'];
                $maxpoint = $maxmoney = 100;

                if (isset($config['item']) && $config['item']) {
                    foreach ($config['item'] as $key => $val) {
                        $maxpoint -= intval($val['level_point']);
                        $maxmoney -= intval($val['level_money']);
                    }
                }

                $_POST['level_point'] > $maxpoint && $_POST['level_point'] = $maxpoint;
                $_POST['level_money'] > $maxmoney && $_POST['level_money'] = $maxmoney;

                if (!empty($_POST['level_point']) && strpos($_POST['level_point'], '%') === false) {
                    $_POST['level_point'] .= '%';
                }
                if (!empty($_POST['level_money']) && strpos($_POST['level_money'], '%') === false) {
                    $_POST['level_money'] .= '%';
                }
                $items = ['level_point' => $_POST['level_point'], 'level_money' => $_POST['level_money']];
                $links[] = ['text' => trans('admin::common.affiliate'), 'href' => 'affiliate.php?act=list'];
                $config['item'][] = $items;
                $config['on'] = 1;
                $config['config']['separate_by'] = 0;

                $this->put_affiliate($config);
            } else {
                return make_json_error($GLOBALS['_LANG']['level_error']);
            }

            return dsc_header("Location: affiliate.php?act=query\n");
        }
        /*------------------------------------------------------ */
        //-- 修改配置
        /*------------------------------------------------------ */
        elseif ($act == 'updata') {
            $separate_by = (intval($_POST['separate_by']) == 1) ? 1 : 0;

            $_POST['expire'] = (float)$_POST['expire'];
            $_POST['level_point_all'] = (float)$_POST['level_point_all'];
            $_POST['level_money_all'] = (float)$_POST['level_money_all'];
            $_POST['level_money_all'] > 100 && $_POST['level_money_all'] = 100;
            $_POST['level_point_all'] > 100 && $_POST['level_point_all'] = 100;

            if (!empty($_POST['level_point_all']) && strpos($_POST['level_point_all'], '%') === false) {
                $_POST['level_point_all'] .= '%';
            }
            if (!empty($_POST['level_money_all']) && strpos($_POST['level_money_all'], '%') === false) {
                $_POST['level_money_all'] .= '%';
            }
            $_POST['level_register_all'] = intval($_POST['level_register_all']);
            $_POST['level_register_up'] = intval($_POST['level_register_up']);

            $separate_desc = e(request()->input('separate_desc', ''));

            $temp = [];
            $temp['config'] = [
                'expire' => $_POST['expire'],        //COOKIE过期数字
                'expire_unit' => $_POST['expire_unit'],   //单位：小时、天、周
                'separate_by' => $separate_by,            //分成模式：0、注册 1、订单
                'level_point_all' => $_POST['level_point_all'],    //积分分成比
                'level_money_all' => $_POST['level_money_all'],    //金钱分成比
                'level_register_all' => $_POST['level_register_all'], //推荐注册奖励积分
                'level_register_up' => $_POST['level_register_up'],   //推荐注册奖励积分上限
                'separate_desc' => $separate_desc,   //推荐说明
            ];
            $temp['item'] = $config['item'];
            $temp['on'] = 1;
            $this->put_affiliate($temp);
            $links[] = ['text' => trans('admin::common.affiliate'), 'href' => 'affiliate.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
        }
        /*------------------------------------------------------ */
        //-- 推荐开启
        /*------------------------------------------------------ */
        elseif ($act == 'on') {
            $on = (intval($_POST['on']) == 1) ? 1 : 0;

            $config['on'] = $on;
            $this->put_affiliate($config);
            $links[] = ['text' => trans('admin::common.affiliate'), 'href' => 'affiliate.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
        }
        /*------------------------------------------------------ */
        //-- Ajax修改设置
        /*------------------------------------------------------ */
        elseif ($act == 'edit_point') {

            /* 取得参数 */
            $key = trim($_POST['id']) - 1;
            $val = (float)trim($_POST['val']);
            $maxpoint = 100;
            foreach ($config['item'] as $k => $v) {
                if ($k != $key) {
                    $maxpoint -= intval($v['level_point']);
                }
            }
            $val > $maxpoint && $val = $maxpoint;
            if (!empty($val) && strpos($val, '%') === false) {
                $val .= '%';
            }
            $config['item'][$key]['level_point'] = $val;
            $config['on'] = 1;
            $this->put_affiliate($config);
            return make_json_result(stripcslashes($val));
        }
        /*------------------------------------------------------ */
        //-- Ajax修改设置
        /*------------------------------------------------------ */
        elseif ($act == 'edit_money') {
            $key = trim($_POST['id']) - 1;
            $val = (float)trim($_POST['val']);
            $maxmoney = 100;
            foreach ($config['item'] as $k => $v) {
                if ($k != $key) {
                    $maxmoney -= intval($v['level_money']);
                }
            }
            $val > $maxmoney && $val = $maxmoney;
            if (!empty($val) && strpos($val, '%') === false) {
                $val .= '%';
            }
            $config['item'][$key]['level_money'] = $val;
            $config['on'] = 1;
            $this->put_affiliate($config);
            return make_json_result(stripcslashes($val));
        }
        /*------------------------------------------------------ */
        //-- 删除下线分成
        /*------------------------------------------------------ */
        elseif ($act == 'del') {
            $key = trim($_GET['id']) - 1;
            unset($config['item'][$key]);
            $temp = [];
            foreach ($config['item'] as $key => $val) {
                $temp[] = $val;
            }
            $config['item'] = $temp;
            $config['on'] = 1;
            $config['config']['separate_by'] = 0;
            $this->put_affiliate($config);
            return dsc_header("Location: affiliate.php?act=list\n");
        }
    }

    private function getAffiliateConfig()
    {
        $affiliate = config('shop.affiliate');
        return $affiliate ? unserialize($affiliate) : [];
    }

    private function put_affiliate($config)
    {
        $temp = serialize($config);
        DB::table('shop_config')->where('code', 'affiliate')->update(['value' => $temp]);

        /* 清除系统设置 */
        $list = [
            'shop_config'
        ];
        BaseRepository::getCacheForgetlist($list);
    }
}
