<?php

namespace App\Modules\Admin\Controllers;

use App\Models\AdminUser;
use App\Models\Goods;
use App\Models\GoodsReport;
use App\Models\GoodsReportImg;
use App\Models\GoodsReportTitle;
use App\Models\GoodsReportType;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Common\ConfigManageService;
use App\Services\Goods\GoodsReportManageService;
use App\Services\Merchant\MerchantCommonService;

/**
 * 举报管理
 */
class GoodsReportController extends InitController
{
    protected $merchantCommonService;
    protected $configManageService;
    protected $dscRepository;
    protected $goodsReportManageService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        ConfigManageService $configManageService,
        DscRepository $dscRepository,
        GoodsReportManageService $goodsReportManageService
    ) {
        $this->merchantCommonService = $merchantCommonService;
        $this->configManageService = $configManageService;
        $this->dscRepository = $dscRepository;
        $this->goodsReportManageService = $goodsReportManageService;
    }

    public function index()
    {

        /* act操作项的初始化 */
        $act = request()->get('act', 'list');

        /*------------------------------------------------------ */
        //-- 投诉内        容
        /*------------------------------------------------------ */
        if ($act == 'list') {
            admin_priv('goods_report');
            //页面赋值
            $this->smarty->assign("ur_here", __('admin::goods_report.goods_report_list'));
            $this->smarty->assign('action_link', ['text' => __('admin::goods_report.goods_report_list'), 'href' => 'goods_report.php?act=list']);
            $this->smarty->assign('action_link1', ['text' => __('admin::goods_report.goods_report_type'), 'href' => 'goods_report.php?act=type']);
            $this->smarty->assign('action_link2', ['text' => __('admin::goods_report.goods_report_title'), 'href' => 'goods_report.php?act=title']);
            $this->smarty->assign('action_link3', ['text' => __('admin::common.report_conf'), 'href' => 'goods_report.php?act=report_conf']);
            $goods_report = $this->goodsReportManageService->getGoodsReport();
            $this->smarty->assign('goods_report', $goods_report['list']);
            $this->smarty->assign('filter', $goods_report['filter']);
            $this->smarty->assign('record_count', $goods_report['record_count']);
            $this->smarty->assign('page_count', $goods_report['page_count']);

            $this->smarty->assign('full_page', 1);
            $this->smarty->assign("act_type", $act);


            return $this->smarty->display("goods_report_list.dwt");
        }
        /*------------------------------------------------------ */
        //-- Ajax投诉内        容
        /*------------------------------------------------------ */
        elseif ($act == 'query') {
            $check_auth = check_authz_json('goods_report');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $goods_report = $this->goodsReportManageService->getGoodsReport();
            $this->smarty->assign('goods_report', $goods_report['list']);
            $this->smarty->assign('filter', $goods_report['filter']);
            $this->smarty->assign('record_count', $goods_report['record_count']);
            $this->smarty->assign('page_count', $goods_report['page_count']);

            return make_json_result(
                $this->smarty->fetch('goods_report_list.dwt'),
                '',
                ['filter' => $goods_report['filter'], 'page_count' => $goods_report['page_count']]
            );
        }
        /*------------------------------------------------------ */
        //-- 查看投诉
        /*------------------------------------------------------ */
        elseif ($act == 'check_state') {
            admin_priv('goods_report');
            $this->smarty->assign("ur_here", __('admin::goods_report.handle_report'));
            $this->smarty->assign('action_link', ['text' => __('admin::goods_report.goods_report_list'), 'href' => 'goods_report.php?act=list']);

            $report_id = request()->get('report_id', 0);

            $res = GoodsReport::where('report_id', $report_id);
            $rows = BaseRepository::getToArrayFirst($res);
            if (!empty($rows)) {
                $rows['goods_image'] = $this->dscRepository->getImagePath($rows['goods_image']);

                $rows['admin_name'] = AdminUser::where('user_id', $rows['admin_id'])->value('user_name');
                $rows['admin_name'] = $rows['admin_name'] ? $rows['admin_name'] : '';
                if ($rows['title_id'] > 0) {
                    $rows['title_name'] = GoodsReportTitle::where('title_id', $rows['title_id'])->value('title_name');
                    $rows['title_name'] = $rows['title_name'] ? $rows['title_name'] : '';
                }
                if ($rows['type_id'] > 0) {
                    $rows['type_name'] = GoodsReportType::where('type_id', $rows['type_id'])->value('type_name');
                    $rows['type_name'] = $rows['type_name'] ? $rows['type_name'] : '';
                }
                if ($rows['add_time'] > 0) {
                    $rows['add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $rows['add_time']);
                }
                $rows['url'] = $this->dscRepository->buildUri('goods', ['gid' => $rows['goods_id']], $rows['goods_name']);
                $user_id = Goods::where('goods_id', $rows['goods_id'])->value('user_id');
                $user_id = $user_id ? $user_id : 0;
                $rows['shop_name'] = $this->merchantCommonService->getShopName($user_id, 1);

                $rows['user_name'] = Users::where('user_id', $rows['user_id'])->value('user_name');
                $rows['user_name'] = $rows['user_name'] ? $rows['user_name'] : '';

                if (config('shop.show_mobile') == 0) {
                    $rows['user_name'] = $this->dscRepository->stringToStar($rows['user_name']);
                }

                //获取举报图片列表
                $res = GoodsReportImg::where('report_id', $rows['report_id'])->orderBy('img_id');
                $img_list = BaseRepository::getToArrayGet($res);
                if (!empty($img_list)) {
                    foreach ($img_list as $k => $v) {
                        $img_list[$k]['img_file'] = $this->dscRepository->getImagePath($v['img_file']);
                    }
                }
                $rows['img_list'] = $img_list;
            }
            $this->smarty->assign("handle_type", __('admin::goods_report.handle_type_desc'));
            $this->smarty->assign('goods_report', $rows);
            return $this->smarty->display('goods_report_info.dwt');
        }
        /*------------------------------------------------------ */
        //-- 处理投诉
        /*------------------------------------------------------ */
        elseif ($act == 'submit_handle') {
            admin_priv('goods_report');
            $report_id = request()->get('report_id', 0);
            $handle_type = request()->get('handle_type', 0);
            $handle_message = request()->get('handle_message', 0);
            //重新判断举报状态  防止二次操作
            $res = GoodsReport::where('report_id', $report_id);
            $goods_report_info = BaseRepository::getToArrayFirst($res);

            if ($goods_report_info['report_state'] == 0) {
                //投诉处理开始 start
                $time = TimeRepository::getGmTime();

                $data = [
                    'report_state' => 1,
                    'handle_type' => $handle_type,
                    'handle_message' => $handle_message,
                    'handle_time' => $time,
                    'admin_id' => session('admin_id')
                ];
                GoodsReport::where('report_id', $report_id)->update($data);

                //$handle_type == 1 为无效举报-商品会正常销售  ，只改变投诉状态，不做处理
                //$handle_type == 2 恶意举报--该用户的所有未处理举报将被无效处理，用户将被禁止举报
                if ($handle_type == 2) {
                    //判断是否开启处罚措施
                    if (config('shop.report_handle') == 1) {
                        //更新会员处罚到期时间，从当前时间开始
                        $report_handle_time = (config('shop.report_handle_time') > 0) ? config('shop.report_handle_time') : 30; //设置默认处罚时间为30
                        $report_time = time() - date('Z') + $report_handle_time * 86400;//获得当前格林威治时间的时间戳 加 处罚时间  得到处罚到期时间
                        $data = ['report_time' => $report_time];
                        Users::where('user_id', $goods_report_info['user_id'])->update($data);

                        //设置举报会员的所有未处理举报为无效举报
                        $data = [
                            'report_state' => 1,
                            'handle_type' => 1,
                            'handle_message' => __('admin::goods_report.handle_message_def'),
                            'handle_time' => $time,
                            'admin_id' => session('admin_id')
                        ];
                        GoodsReport::where('report_id', 0)->where('user_id', $goods_report_info['user_id'])->update($data);
                    }
                } //有效举报--商品将被违规下架,审核不通过
                elseif ($handle_type == 3) {
                    $title_name = __('admin::goods_report.irregularities');
                    //获取举报类型和举报主题
                    if ($goods_report_info['title_id'] > 0) {
                        $title_name = GoodsReportTitle::where('title_id', $goods_report_info['title_id'])->value('title_name');
                        $title_name = $title_name ? $title_name : 0;
                    }
                    //举报商品下架
                    $handle_message_goods = sprintf(__('admin::goods_report.handle_message_goods'), $title_name);
                    $data = [
                        'is_on_sale' => 0,
                        'review_status' => 2,
                        'review_content' => $handle_message_goods
                    ];
                    Goods::where('goods_id', $goods_report_info['goods_id'])->update($data);
                }
                $link[0]['text'] = __('admin::goods_report.back_list');
                $link[0]['href'] = 'goods_report.php?act=list';

                return sys_msg(__('admin::goods_report.edit_succeed'), 0, $link);
            } else {
                $link[0]['text'] = __('admin::goods_report.back_list');
                $link[0]['href'] = 'goods_report.php?act=list';

                return sys_msg(__('admin::goods_report.handle_report_repeat'), 0, $link);
            }
        }

        /*------------------------------------------------------ */
        //-- 处理举报设置
        /*------------------------------------------------------ */
        elseif ($act == 'report_conf') {
            admin_priv('goods_report');

            $this->dscRepository->helpersLang('shop_config', 'admin');

            $this->smarty->assign('ur_here', __('admin::common.report_conf'));
            $this->smarty->assign('action_link', ['text' => __('admin::goods_report.goods_report_list'), 'href' => 'goods_report.php?act=list']);
            $this->smarty->assign('action_link1', ['text' => __('admin::goods_report.goods_report_type'), 'href' => 'goods_report.php?act=type']);
            $this->smarty->assign('action_link2', ['text' => __('admin::goods_report.goods_report_title'), 'href' => 'goods_report.php?act=title']);
            $this->smarty->assign('action_link3', ['text' => __('admin::common.report_conf'), 'href' => 'goods_report.php?act=report_conf']);

            $report_conf = $this->configManageService->getUpSettings('report_conf');
            $this->smarty->assign('report_conf', $report_conf);

            $this->smarty->assign("act_type", $act);


            return $this->smarty->display('goods_report_conf.dwt');
        }

        /*------------------------------------------------------ */
        //-- 删除举报
        /*------------------------------------------------------ */
        elseif ($act == 'remove') {
            $check_auth = check_authz_json('goods_report');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $id = request()->get('id', 0);
            $res = GoodsReportImg::where('report_id', $id);
            $img_list = BaseRepository::getToArrayGet($res);

            if (!empty($img_list)) {
                foreach ($img_list as $key => $val) {
                    $this->dscRepository->getOssDelFile([$val['img_file']]);

                    @unlink(storage_public($val['img_file']));
                }
            }
            GoodsReportImg::where('report_id', $id)->delete();
            GoodsReport::where('report_id', $id)->delete();
            $url = 'goods_report.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }
        /*------------------------------------------------------ */
        //-- 投诉类型
        /*------------------------------------------------------ */
        elseif ($act == 'type') {
            admin_priv('goods_report');
            $this->smarty->assign("ur_here", __('admin::goods_report.goods_report_type'));
            $this->smarty->assign('action_link', ['text' => __('admin::goods_report.goods_report_list'), 'href' => 'goods_report.php?act=list']);
            $this->smarty->assign('action_link1', ['text' => __('admin::goods_report.goods_report_type'), 'href' => 'goods_report.php?act=type']);
            $this->smarty->assign('action_link2', ['text' => __('admin::goods_report.goods_report_title'), 'href' => 'goods_report.php?act=title']);
            $this->smarty->assign('action_link3', ['text' => __('admin::goods_report.type_add'), 'href' => 'goods_report.php?act=type_add']);
            $this->smarty->assign('action_link4', ['text' => __('admin::common.report_conf'), 'href' => 'goods_report.php?act=report_conf']);

            $type_info = $this->goodsReportManageService->getGoodsReportTypeList();
            $this->smarty->assign('type_info', $type_info['list']);
            $this->smarty->assign('filter', $type_info['filter']);
            $this->smarty->assign('record_count', $type_info['record_count']);
            $this->smarty->assign('page_count', $type_info['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign("act_type", $act);


            return $this->smarty->display("goods_report_type.dwt");
        }
        /*------------------------------------------------------ */
        //-- AJAX返回
        /*------------------------------------------------------ */
        elseif ($act == 'type_query') {
            $check_auth = check_authz_json('goods_report');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $type_info = $this->goodsReportManageService->getGoodsReportTypeList();

            $this->smarty->assign('type_info', $type_info['list']);
            $this->smarty->assign('filter', $type_info['filter']);
            $this->smarty->assign('record_count', $type_info['record_count']);
            $this->smarty->assign('page_count', $type_info['page_count']);

            return make_json_result(
                $this->smarty->fetch('goods_report_type.dwt'),
                '',
                ['filter' => $type_info['filter'], 'page_count' => $type_info['page_count']]
            );
        }
        /*------------------------------------------------------ */
        //-- 切换是否显示
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_show') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            $data = ['is_show' => $val];
            GoodsReportType::where('type_id', $id)->update($data);
            clear_cache_files();

            return make_json_result($val);
        }
        /*------------------------------------------------------ */
        //-- 切换是否显示
        /*------------------------------------------------------ */
        elseif ($act == 'toggle_show_title') {
            $check_auth = check_authz_json('complaint');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = request()->get('id', 0);
            $val = request()->get('val', 0);

            $data = ['is_show' => $val];
            GoodsReportTitle::where('title_id', $id)->update($data);
            clear_cache_files();

            return make_json_result($val);
        }

        /*------------------------------------------------------ */
        //-- 添加/编辑类型
        /*------------------------------------------------------ */
        elseif ($act == 'type_add' || $act == 'type_edit') {
            admin_priv('goods_report');
            $this->smarty->assign("ur_here", __('admin::goods_report.goods_report_type'));
            $this->smarty->assign('action_link', ['text' => __('admin::goods_report.goods_report_type'), 'href' => 'goods_report.php?act=type']);
            //处理接收数据
            $type_id = request()->get('type_id', 0);

            //初始化处理入口
            if ($act == 'type_add') {
                $form_action = "type_insert";
            } else {
                $form_action = "type_update";
                $res = GoodsReportType::where('type_id', $type_id);
                $report_type_info = BaseRepository::getToArrayFirst($res);
                $this->smarty->assign('report_type_info', $report_type_info);
            }
            $this->smarty->assign("form_action", $form_action);
            return $this->smarty->display("goods_report_type_info.dwt");
        }
        /*------------------------------------------------------ */
        //-- 添加/编辑类型 �        �库处理
        /*------------------------------------------------------ */
        elseif ($act == 'type_insert' || $act == 'type_update') {
            admin_priv('goods_report');
            $type_name = request()->get('type_name', '');
            $type_id = request()->get('type_id', 0);
            $is_show = request()->get('is_show', 0);
            $type_desc = request()->get('type_desc', '');
            if (empty($type_name)) {
                return sys_msg(__('admin::goods_report.type_name_null'), 1);
            }
            if (empty($type_desc)) {
                return sys_msg(__('admin::goods_report.type_name_null'), 1);
            }

            if ($act == 'type_insert') {
                /*检查是否重复*/
                $res = GoodsReportType::where('type_name', $type_name)->count();
                if ($res > 0) {
                    return sys_msg(sprintf(__('admin::goods_report.title_exist'), stripslashes($type_name)), 1);
                }

                $data = [
                    'type_name' => $type_name,
                    'type_desc' => $type_desc,
                    'is_show' => $is_show
                ];
                GoodsReportType::insert($data);
                $link[0]['text'] = __('admin::goods_report.continue_add');
                $link[0]['href'] = 'goods_report.php?act=type_add';

                $link[1]['text'] = __('admin::goods_report.back_list');
                $link[1]['href'] = 'goods_report.php?act=type';

                return sys_msg(__('admin::goods_report.add_succeed'), 0, $link);
            } else {
                /*检查是否重复*/
                $res = GoodsReportType::where('type_name', $type_name)->where('type_id', '<>', $type_id)->count();
                if ($res > 0) {
                    return sys_msg(sprintf(__('admin::goods_report.title_exist'), stripslashes($type_name)), 1);
                }
                $data = [
                    'type_name' => $type_name,
                    'type_desc' => $type_desc,
                    'is_show' => $is_show
                ];
                GoodsReportType::where('type_id', $type_id)->update($data);

                $link[0]['text'] = __('admin::goods_report.back_list');
                $link[0]['href'] = 'goods_report.php?act=type';

                return sys_msg(__('admin::goods_report.edit_succeed'), 0, $link);
            }
        }
        /*------------------------------------------------------ */
        //-- 删除类型
        /*------------------------------------------------------ */
        elseif ($act == 'remove_type') {
            $check_auth = check_authz_json('goods_report');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $id = request()->get('id', 0);

            GoodsReportType::where('type_id', $id)->delete();
            $url = 'goods_report.php?act=type_query&' . str_replace('act=remove_type', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }
        /*------------------------------------------------------ */
        //-- 投诉主题
        /*------------------------------------------------------ */
        elseif ($act == 'title') {
            admin_priv('goods_report');
            $this->smarty->assign("ur_here", __('admin::goods_report.goods_report_title'));
            $this->smarty->assign('action_link', ['text' => __('admin::goods_report.goods_report_list'), 'href' => 'goods_report.php?act=list']);
            $this->smarty->assign('action_link1', ['text' => __('admin::goods_report.goods_report_type'), 'href' => 'goods_report.php?act=type']);
            $this->smarty->assign('action_link2', ['text' => __('admin::goods_report.goods_report_title'), 'href' => 'goods_report.php?act=title']);
            $this->smarty->assign('action_link3', ['text' => __('admin::goods_report.title_add'), 'href' => 'goods_report.php?act=title_add']);
            $this->smarty->assign('action_link4', ['text' => __('admin::common.report_conf'), 'href' => 'goods_report.php?act=report_conf']);

            $title = $this->goodsReportManageService->getGoodsReportTitleList();
            $this->smarty->assign('title_info', $title['list']);
            $this->smarty->assign('filter', $title['filter']);
            $this->smarty->assign('record_count', $title['record_count']);
            $this->smarty->assign('page_count', $title['page_count']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign("act_type", $act);


            return $this->smarty->display("goods_report_title.dwt");
        }
        /*------------------------------------------------------ */
        //-- AJAX返回
        /*------------------------------------------------------ */
        elseif ($act == 'title_query') {
            $check_auth = check_authz_json('goods_report');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $title = $this->goodsReportManageService->getGoodsReportTitleList();

            $this->smarty->assign('title_info', $title['list']);
            $this->smarty->assign('filter', $title['filter']);
            $this->smarty->assign('record_count', $title['record_count']);
            $this->smarty->assign('page_count', $title['page_count']);

            return make_json_result(
                $this->smarty->fetch('goods_report_title.dwt'),
                '',
                ['filter' => $title['filter'], 'page_count' => $title['page_count']]
            );
        }
        /*------------------------------------------------------ */
        //-- 添加/编辑主题
        /*------------------------------------------------------ */
        elseif ($act == 'title_add' || $act == 'title_edit') {
            admin_priv('goods_report');
            $this->smarty->assign("ur_here", __('admin::goods_report.goods_report_title'));
            $this->smarty->assign('action_link', ['text' => __('admin::goods_report.goods_report_title'), 'href' => 'goods_report.php?act=title']);
            //处理接收数据
            $title_id = request()->get('title_id', 0);

            //获取举报主题
            $goods_report_type = get_goods_report_type();

            //初始化处理入口
            if ($act == 'title_add') {
                $form_action = "title_insert";
            } else {
                $form_action = "title_update";
                $res = GoodsReportTitle::where('title_id', $title_id);
                $report_title_info = BaseRepository::getToArrayFirst($res);
                $this->smarty->assign('report_title_info', $report_title_info);
            }

            $this->smarty->assign("goods_report_type", $goods_report_type);
            $this->smarty->assign("form_action", $form_action);
            return $this->smarty->display("goods_report_title_info.dwt");
        }
        /*------------------------------------------------------ */
        //-- 添加/编辑类型 �        �库处理
        /*------------------------------------------------------ */
        elseif ($act == 'title_insert' || $act == 'title_update') {
            admin_priv('goods_report');
            $title_name = request()->get('title_name', '');
            $type_id = request()->get('type_id', 0);
            $title_id = request()->get('title_id', 0);
            $is_show = request()->get('is_show', 0);
            if (empty($title_name)) {
                return sys_msg(__('admin::goods_report.title_name_null'), 1);
            }

            if ($act == 'title_insert') {
                /*检查是否重复*/
                $res = GoodsReportTitle::where('title_name', $title_name)->count();
                if ($res > 0) {
                    return sys_msg(sprintf(__('admin::goods_report.exist_title'), stripslashes($title_name)), 1);
                }
                $data = [
                    'type_id' => $type_id,
                    'title_name' => $title_name,
                    'is_show' => $is_show
                ];
                GoodsReportTitle::insert($data);
                $link[0]['text'] = __('admin::goods_report.continue_add');
                $link[0]['href'] = 'goods_report.php?act=title_add';

                $link[1]['text'] = __('admin::goods_report.back_list');
                $link[1]['href'] = 'goods_report.php?act=title';

                return sys_msg(__('admin::goods_report.add_succeed'), 0, $link);
            } else {
                /*检查是否重复*/
                $res = GoodsReportTitle::where('title_name', $title_name)->where('title_id', '<>', $title_id)->count();
                if ($res > 0) {
                    return sys_msg(sprintf(__('admin::goods_report.exist_title'), stripslashes($title_name)), 1);
                }
                $data = [
                    'type_id' => $type_id,
                    'title_name' => $title_name,
                    'is_show' => $is_show
                ];
                GoodsReportTitle::where('title_id', $title_id)->update($data);

                $link[0]['text'] = __('admin::goods_report.back_list');
                $link[0]['href'] = 'goods_report.php?act=title';

                return sys_msg(__('admin::goods_report.edit_succeed'), 0, $link);
            }
        }
        /*------------------------------------------------------ */
        //-- 删除主题
        /*------------------------------------------------------ */
        elseif ($act == 'remove_title') {
            $check_auth = check_authz_json('goods_report');
            if ($check_auth !== true) {
                return $check_auth;
            }
            $id = request()->get('id', 0);

            GoodsReportTitle::where('title_id', $id)->delete();
            $url = 'goods_report.php?act=title_query&' . str_replace('act=remove_title', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        }
    }
}
