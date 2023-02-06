<?php

namespace App\Modules\Admin\Controllers;

use App\Libraries\Image;
use App\Models\Country;
use App\Modules\Admin\Services\Country\CountryService;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class CountryController extends InitController
{
    private $countryService;
    private $dscRepository;

    public function __construct(
        CountryService $countryService,
        DscRepository $dscRepository
    )
    {
        $this->countryService = $countryService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

        $act = isset($_REQUEST['act']) && !empty($_REQUEST['act']) ? addslashes($_REQUEST['act']) : 'list';

        /*------------------------------------------------------ */
        //-- 国家列表页面
        /*------------------------------------------------------ */
        if ($act == 'list') {

            admin_priv('country_manage');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['country_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['add_country'], 'href' => 'country.php?act=add']);
            $this->smarty->assign('full_page', 1);

            $country_list = $this->countryService->getCountryList();
            $this->smarty->assign('country_list', $country_list['list']);
            $this->smarty->assign('filter', $country_list['filter']);
            $this->smarty->assign('record_count', $country_list['record_count']);
            $this->smarty->assign('page_count', $country_list['page_count']);

            $sort_flag = sort_flag($country_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return $this->smarty->display('country_list.dwt');
        }

        /*------------------------------------------------------ */
        //-- 排序、分页、查询
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'query') {

            $country_list = $this->countryService->getCountryList();

            $this->smarty->assign('country_list', $country_list['list']);
            $this->smarty->assign('filter', $country_list['filter']);
            $this->smarty->assign('record_count', $country_list['record_count']);
            $this->smarty->assign('page_count', $country_list['page_count']);

            $sort_flag = sort_flag($country_list['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result(
                $this->smarty->fetch('country_list.dwt'),
                '',
                ['filter' => $country_list['filter'], 'page_count' => $country_list['page_count']]
            );
        }

        /*------------------------------------------------------ */
        //-- 添加新广告页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'add') {
            admin_priv('country_manage');

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['add_country']);
            $this->smarty->assign('action_link', ['href' => 'country.php?act=list', 'text' => $GLOBALS['_LANG']['country_list']]);

            $this->smarty->assign('form_act', 'insert');
            $this->smarty->assign('action', 'add');
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            return $this->smarty->display('country_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 新国家信息的处理
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'insert') {
            admin_priv('country_manage');

            $country_name = !empty($_POST['country_name']) ? trim($_POST['country_name']) : '';

            if (!isset($_FILES['country_icon'])) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['js_languages']['ad_photo_empty'], 0, $link);
            }

            /* 查看广告名称是否有重复 */
            $count = Country::where('country_name', $country_name)->count();
            if ($count > 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['country_name_exist'], 0, $link);
            }

            $country_icon = basename($image->upload_image($_FILES['country_icon'], 'countryimg'));

            $add_file = [];
            if ($country_icon) {
                if (strpos($country_icon, DATA_DIR . '/countryimg/') === false) {

                    $country_icon = DATA_DIR . '/countryimg/' . $country_icon;

                    $add_file[] = $country_icon;
                } else {
                    $add_file[] = $country_icon;
                }
            }
            $this->dscRepository->getOssAddFile($add_file);

            Country::insert([
                'country_name' => $country_name,
                'country_icon' => $country_icon,
                'add_time' => TimeRepository::getGmTime()
            ]);

            /* 记录管理员操作 */
            admin_log($country_name, 'add', 'country');

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_country_list'];
            $link[0]['href'] = 'country.php?act=list';

            $link[1]['text'] = $GLOBALS['_LANG']['add_country'];
            $link[1]['href'] = 'country.php?act=add';
            return sys_msg($GLOBALS['_LANG']['add'] . "&nbsp;" . $country_name . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 编辑新广告页面
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'edit') {
            admin_priv('country_manage');

            $id = !empty($_REQUEST['id']) ? intval($_POST['id']) : 0;

            $country = $this->countryService->country_info($id);
            $this->smarty->assign('country', $country);

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['edit_country']);
            $this->smarty->assign('action_link', ['href' => 'country.php?act=list', 'text' => $GLOBALS['_LANG']['country_list']]);

            $this->smarty->assign('form_act', 'update');
            $this->smarty->assign('action', 'edit');
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);

            return $this->smarty->display('country_info.dwt');
        }

        /*------------------------------------------------------ */
        //-- 国家信息编辑处理
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'update') {
            admin_priv('country_manage');

            $id = !empty($_REQUEST['id']) ? intval($_POST['id']) : 0;
            $country_name = !empty($_POST['country_name']) ? trim($_POST['country_name']) : '';

            if ($_FILES['country_icon'] && $_FILES['country_icon']['error'] > 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['js_languages']['ad_photo_empty'], 0, $link);
            }

            /* 查看广告名称是否有重复 */
            $count = Country::where('country_name', $country_name)->where('id', '<>', $id)->count();
            if ($count > 0) {
                $link[] = ['text' => $GLOBALS['_LANG']['go_back'], 'href' => 'javascript:history.back(-1)'];
                return sys_msg($GLOBALS['_LANG']['country_name_exist'], 0, $link);
            }

            $country = $this->countryService->country_info($id);

            $country_icon = '';
            if ($_FILES['country_icon'] && $_FILES['country_icon']['size'] > 0) {
                $country_icon = basename($image->upload_image($_FILES['country_icon'], 'countryimg'));
            }

            $other = [
                'country_name' => $country_name
            ];

            $add_file = [];
            if ($country_icon) {
                if (strpos($country_icon, DATA_DIR . '/countryimg/') === false) {

                    $country_icon = DATA_DIR . '/countryimg/' . $country_icon;
                    $add_file[] = $country_icon;

                    BaseRepository::dscUnlink(storage_public($country['icon']));
                } else {
                    $add_file[] = $country_icon;
                }

                $other['country_icon'] = $country_icon;
            }
            $this->dscRepository->getOssAddFile($add_file);

            Country::where('id', $id)->update($other);

            /* 记录管理员操作 */
            admin_log($country_name, 'edit', 'country');

            /* 提示信息 */
            $link[0]['text'] = $GLOBALS['_LANG']['back_country_list'];
            $link[0]['href'] = 'country.php?act=list';

            $link[1]['text'] = $GLOBALS['_LANG']['edit_country'];
            $link[1]['href'] = 'country.php?act=edit' . '&id=' . $id;
            return sys_msg($GLOBALS['_LANG']['edit'] . "&nbsp;" . $country_name . "&nbsp;" . $GLOBALS['_LANG']['attradd_succed'], 0, $link);
        }

        /*------------------------------------------------------ */
        //-- 删除国家信息
        /*------------------------------------------------------ */
        elseif ($_REQUEST['act'] == 'remove') {
            $check_auth = check_authz_json('country_manage');
            if ($check_auth !== true) {
                return $check_auth;
            }

            $id = intval($_GET['id'] ?? 0);

            $img = Country::where('id', $id)->value('country_icon');
            $img = $img ? $img : '';
            if ($img) {
                $this->dscRepository->getOssDelFile([$img]);
                BaseRepository::dscUnlink(storage_public($img));
            }

            Country::where('id', $id)->delete();

            admin_log('', 'remove', 'country');

            $url = 'country.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));

            return dsc_header("Location: $url\n");
        }
    }
}