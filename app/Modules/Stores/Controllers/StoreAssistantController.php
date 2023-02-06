<?php

namespace App\Modules\Stores\Controllers;

use App\Libraries\Exchange;
use App\Libraries\Image;
use App\Models\StoreUser;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Rules\PasswordRule;
use App\Rules\UserName;
use Illuminate\Support\Facades\Validator;

/**
 * 商品管理程序
 */
class StoreAssistantController extends InitController
{
    protected $image;
    protected $dscRepository;

    public function __construct(
        Image $image,
        DscRepository $dscRepository
    ) {
        $this->image = $image;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        $exc = new Exchange($this->dsc->table("store_user"), $this->db, 'id', 'stores_user', 'store_id', 'email');
        $sto = new Exchange($this->dsc->table("offline_store"), $this->db, 'id', 'stores_user', 'stores_name', 'is_confirm', 'stores_tel', 'stores_opening_hours');
        $store_id = session('stores_id');

        $ru_id = $this->db->getOne(" SELECT ru_id FROM " . $this->dsc->table('offline_store') . " WHERE id = '$store_id' ");
        $this->smarty->assign("app", "assistant");
        $allow_file_types = '|GIF|JPG|PNG|';

        if ($_REQUEST['act'] == 'list') {
            store_priv('user_manage'); //检查权限
            $this->smarty->assign('action_link', ['href' => 'store_assistant.php?act=add', 'text' => $GLOBALS['_LANG']['store_assistant_add']]);
            $this->smarty->assign('action_link2', ['href' => 'store_assistant.php?act=message_edit', 'text' => $GLOBALS['_LANG']['store_message_edit']]);
            $list = $this->get_store_user($ru_id, $store_id);

            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('list', $list['pzd_list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('full_page', 1);

            $this->smarty->assign('page_title', $GLOBALS['_LANG']['store_user']);
            return $this->smarty->display('store_assistant.dwt');
        } elseif ($_REQUEST['act'] == 'query') {
            $list = $this->get_store_user($ru_id, $store_id);
            $page = isset($_REQUEST['page']) && !empty(intval($_REQUEST['page'])) ? intval($_REQUEST['page']) : 1;
            $page_count_arr = seller_page($list, $page);
            $this->smarty->assign('page_count_arr', $page_count_arr);
            $this->smarty->assign('list', $list['pzd_list']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);

            //跳转页面
            return make_json_result($this->smarty->fetch('store_assistant.dwt'), '', ['filter' => $list['filter'], 'page_count' => $list['page_count']]);
        } elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit') {
            store_priv('user_manage'); //检查权限
            $this->smarty->assign('action_link', ['href' => 'store_assistant.php?act=list', 'text' => $GLOBALS['_LANG']['store_assistant_list']]);

            $act = ($_REQUEST['act'] == 'add') ? 'insert' : 'update';
            $this->smarty->assign("act", $act);

            $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $this->smarty->assign("store_action", $this->get_store_action($id));

            if ($_REQUEST['act'] == 'edit') {
                $sql = "SELECT * FROM" . $this->dsc->table('store_user') . " WHERE id = '$id'";
                $store_user = $this->db->getRow($sql);
                $this->smarty->assign('store_user', $store_user);
            }

            $this->smarty->assign('page_title', ($_REQUEST['act'] == 'add') ? $GLOBALS['_LANG']['add_assistant'] : $GLOBALS['_LANG']['edit_assistant']);
            return $this->smarty->display('store_assistant_info.dwt');
        } elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update') {
            store_priv('user_manage'); //检查权限

            $store_user = !empty($_REQUEST['store_user']) ? $_REQUEST['store_user'] : '';
            $password = !empty($_REQUEST['password']) ? trim($_REQUEST['password']) : '';
            $confirm_pwd = !empty($_REQUEST['confirm_pwd']) ? trim($_REQUEST['confirm_pwd']) : '';
            $email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : '';
            $tel = !empty($_REQUEST['tel']) ? $_REQUEST['tel'] : '';
            $store_action = !empty($_REQUEST['store_action']) ? implode(',', $_REQUEST['store_action']) : '';

            // 添加
            if ($_REQUEST['act'] == 'insert') {
                $is_only_user = $exc->is_only('stores_user', $store_user, 0);
                if (!$is_only_user) {
                    return make_json_response('', 3, sprintf($GLOBALS['_LANG']['user_exist'], stripslashes($store_user)));
                }

                /*检查邮箱是否重复*/
                $is_only_email = $exc->is_only('email', $email, 0, "email != ''");
                if (!$is_only_email) {
                    return make_json_response('', 3, $GLOBALS['_LANG']['email_exist']);
                }

                /* 判断两次密码是否一样 */
                if ($password !== $confirm_pwd) {
                    return make_json_response('', 3, $GLOBALS['_LANG']['is_different']);
                }

                $time = gmtime();

                // 数据验证
                $validator = Validator::make(request()->all(), [
                    'store_user' => ['required', 'string', new UserName()],
                    'password' => ['required', 'different:store_user', new PasswordRule()], // 密码
                ], [
                    'password.required' => lang('user.user_pass_empty'),
                    'password.different' => lang('user.user_pass_same')
                ]);

                // 返回错误
                if ($validator->fails()) {
                    $error = $validator->errors()->first();
                    return make_json_response('', 3, $error);
                }

                $ec_salt = rand(1000, 9999);

                // 生成hash密码
                $GLOBALS['user'] = init_users();
                $new_stores_pwd = $GLOBALS['user']->hash_password($password);

                $parent_id = StoreUser::where('store_id', $store_id)->where('ru_id', $ru_id)->where('parent_id', 0)->value('id');

                $data = [
                    'ru_id' => $ru_id,
                    'store_id' => $store_id,
                    'parent_id' => $parent_id,
                    'stores_user' => $store_user,
                    'stores_pwd' => $new_stores_pwd,
                    'ec_salt' => $ec_salt,
                    'add_time' => $time,
                    'tel' => $tel,
                    'email' => $email,
                    'store_action' => $store_action
                ];
                $store = StoreUser::insertGetId($data);

                if ($store) {
                    $link[0]['text'] = $GLOBALS['_LANG']['GO_add'];
                    $link[0]['href'] = 'store_assistant.php?act=add';

                    $link[1]['text'] = $GLOBALS['_LANG']['bank_list'];
                    $link[1]['href'] = 'store_assistant.php?act=list';

                    return make_json_response('', 1, $GLOBALS['_LANG']['add_succeed'], ['url' => 'store_assistant.php?act=list']);
                }
            } else {
                // 编辑
                $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
                $newpassword = !empty($_REQUEST['newpassword']) ? trim($_REQUEST['newpassword']) : '';

                /* 检查是否重复 */
                $is_only_user = $exc->is_only('stores_user', $store_user, 0, "id != $id");
                if (!$is_only_user) {
                    return make_json_response('', 0, sprintf($GLOBALS['_LANG']['user_exist'], stripslashes($store_user)));
                }

                /*检查邮箱是否重复*/
                $is_only_email = $exc->is_only('email', $email, 0, "store_id != '$id' AND email != ''");
                if (!$is_only_email) {
                    return make_json_response('', 0, $GLOBALS['_LANG']['email_exist']);
                }

                /* 判断两次密码是否一样 */
                if ($newpassword !== $confirm_pwd) {
                    return make_json_response('', 0, $GLOBALS['_LANG']['is_different']);
                }

                // 门店会员
                $data = [
                    'stores_user' => $store_user,
                    'tel' => $tel,
                    'email' => $email
                ];

                if (!empty($newpassword)) {

                    // 数据验证
                    $validator = Validator::make(request()->all(), [
                        'newpassword' => ['filled', 'different:store_user', new PasswordRule()], // 密码
                    ], [
                        'newpassword.filled' => lang('user.user_pass_empty'),
                        'newpassword.different' => lang('user.user_pass_same')
                    ]);

                    // 返回错误
                    if ($validator->fails()) {
                        $error = $validator->errors()->first();
                        return make_json_response('', 0, $error);
                    }

                    // 生成hash密码
                    $GLOBALS['user'] = init_users();
                    $data['stores_pwd'] = $GLOBALS['user']->hash_password($newpassword);

                    $data['login_status'] = '';
                }

                /* 权限 */
                $user_action = StoreUser::where('id', $id)->value('store_action');
                if ($user_action != 'all') {
                    $data['store_action'] = $store_action;
                }

                StoreUser::where('id', $id)->update($data);

                $link[0]['text'] = $GLOBALS['_LANG']['bank_list'];
                $link[0]['href'] = 'store_assistant.php?act=list';
                return make_json_response('', 1, $GLOBALS['_LANG']['edit_succeed'], ['url' => 'store_assistant.php?act=list']);
            }
        } elseif ($_REQUEST['act'] == 'remove') {
            $id = intval($_GET['id']);
            $exc->drop($id);
            $url = 'store_assistant.php?act=query&' . str_replace('act=remove', '', request()->server('QUERY_STRING'));
            return dsc_header("Location: $url\n");
        } elseif ($_REQUEST['act'] == 'message_edit') {
            store_priv('user_manage'); //检查权限

            $this->smarty->assign('action_link', ['href' => 'store_assistant.php?act=list', 'text' => $GLOBALS['_LANG']['store_assistant_list']]);
            $this->smarty->assign('page_title', $GLOBALS['_LANG']['store_message_edit']);

            $sql = "SELECT stores_name,country,province,city,district,stores_address,stores_tel,stores_opening_hours,stores_traffic_line,stores_img,longitude,latitude FROM" . $this->dsc->table('offline_store') . " WHERE id='$store_id'";
            $store_info = $this->db->getRow($sql);

            if (isset($store_info['stores_img']) && !empty($store_info['stores_img'])) {
                $store_info['stores_img'] = $this->dscRepository->getImagePath($store_info['stores_img']);
            }

            $this->smarty->assign('offline_store', $store_info);
            $this->smarty->assign('countries', get_regions());
            $this->smarty->assign('provinces', get_regions(1, 1));
            $this->smarty->assign('cities', get_regions(2, $store_info['province']));
            $this->smarty->assign('districts', get_regions(3, $store_info['city']));
            return $this->smarty->display('store_message_info.dwt');
        } elseif ($_REQUEST['act'] == 'message_update') {
            store_priv('user_manage'); //检查权限

            $stores_name = isset($_REQUEST['stores_name']) ? $_REQUEST['stores_name'] : '';
            $country = isset($_REQUEST['country']) ? $_REQUEST['country'] : '';
            $province = isset($_REQUEST['province']) ? $_REQUEST['province'] : '';
            $city = isset($_REQUEST['city']) ? $_REQUEST['city'] : '';
            $district = isset($_REQUEST['district']) ? $_REQUEST['district'] : '';
            $stores_address = isset($_REQUEST['stores_address']) ? $_REQUEST['stores_address'] : '';
            $stores_tel = isset($_REQUEST['stores_tel']) ? $_REQUEST['stores_tel'] : '';
            $stores_opening_hours = isset($_REQUEST['stores_opening_hours']) ? $_REQUEST['stores_opening_hours'] : '';
            $stores_traffic_line = isset($_REQUEST['stores_traffic_line']) ? $_REQUEST['stores_traffic_line'] : '';
            $longitude = empty($_REQUEST['longitude']) ? '' : addslashes(trim($_REQUEST['longitude']));
            $latitude = empty($_REQUEST['latitude']) ? '' : addslashes(trim($_REQUEST['latitude']));

            /* 检查是否重复 */
            $is_only = $sto->is_only('stores_name', $stores_name, 0, "id != $store_id");
            if (!$is_only) {
                return make_json_response('', 0, $GLOBALS['_LANG']['title_exist'], ['url' => 'store_assistant.php?act=message_edit']);
            }

            $sql = "UPDATE" . $this->dsc->table('offline_store') . " SET stores_name='$stores_name',country='$country'"
                . ",province='$province',city='$city',longitude='$longitude',latitude='$latitude',district='$district',stores_address='$stores_address',stores_tel='$stores_tel',"
                . "stores_opening_hours='$stores_opening_hours',stores_traffic_line='$stores_traffic_line' WHERE id = '$store_id'";

            $this->db->query($sql);

            $link[0]['text'] = $GLOBALS['_LANG']['bank_list'];
            $link[0]['href'] = 'store_assistant.php?act=list';
            return make_json_response('', 1, $GLOBALS['_LANG']['edit_succeed'], ['url' => 'store_assistant.php?act=message_edit']);
        }
    }

    private function get_store_user($ru_id = 0, $store_id = 0)
    {
        $result = get_filter();
        if ($result === false) {
            $filter['keywords'] = isset($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '';

            $where = "WHERE 1";
            /* 商家 */
            $where .= " and ru_id = '$ru_id' ";
            $where .= " and store_id = '$store_id' ";

            $sql = "SELECT COUNT(*) FROM " . $this->dsc->table('store_user') . $where;
            $filter['record_count'] = $this->db->getOne($sql);
            $filter = page_and_size($filter);
            /* 获活动数据 */
            $sql = "SELECT id,stores_user,tel,email,add_time FROM " . $this->dsc->table('store_user') . " $where ORDER BY id ASC LIMIT " . $filter['start'] . "," . $filter['page_size'];
            $filter['keywords'] = stripslashes($filter['keywords']);
            set_filter($filter, $sql);
        } else {
            $sql = $result['sql'];
            $filter = $result['filter'];
        }
        $row = $this->db->getAll($sql);
        foreach ($row as $k => $v) {
            $row[$k]['add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['add_time']);
        }
        $arr = ['pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }

    private function get_store_action($store_user_id = 0)
    {
        //获取权限列表
        $sql = " SELECT * FROM " . $this->dsc->table('store_action');
        $store_action = $this->db->getAll($sql);

        //获取店员权限
        $sql = " SELECT store_action FROM " . $this->dsc->table('store_user') . " WHERE id = '$store_user_id' ";
        $user_action = $this->db->getOne($sql);

        foreach ($store_action as $key => $val) {
            if (in_array($val['action_code'], explode(',', $user_action)) || $user_action == 'all') {
                $store_action[$key]['is_check'] = 1;
            } else {
                $store_action[$key]['is_check'] = 0;
            }
        }

        return $store_action;
    }

    /* 上传文件 */

    private function upload_article_file($upload)
    {
        $file_dir = storage_public(DATA_DIR . "/offline_store");
        if (!file_exists($file_dir)) {
            if (!make_dir($file_dir)) {
                /* 创建目录失败 */
                return false;
            }
        }

        $filename = $this->image->random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
        $path = storage_public(DATA_DIR . "/offline_store/" . $filename);

        if (move_upload_file($upload['tmp_name'], $path)) {
            return DATA_DIR . "/offline_store/" . $filename;
        } else {
            return false;
        }
    }
}
