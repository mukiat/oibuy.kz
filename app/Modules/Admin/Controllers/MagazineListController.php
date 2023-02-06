<?php

namespace App\Modules\Admin\Controllers;

use App\Models\EmailList;
use App\Models\EmailSendlist;
use App\Models\MailTemplates;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Magazine\MagazineListManageService;

/**
 * 邮件订阅
 * Class MagazineListController
 * @package App\Modules\Admin\Controllers
 */
class MagazineListController extends InitController
{
    protected $dscRepository;
    protected $magazineListManageService;

    public function __construct(
        DscRepository $dscRepository,
        MagazineListManageService $magazineListManageService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->magazineListManageService = $magazineListManageService;
    }

    public function index()
    {
        admin_priv('magazine_list');

        $act = e(request()->input('act', 'list'));

        if ($act == 'list') {

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['04_magazine_list']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['add_new'], 'href' => 'magazine_list.php?act=add']);
            $this->smarty->assign('full_page', 1);

            $magazinedb = $this->magazineListManageService->getMagazine();

            $this->smarty->assign('magazinedb', $magazinedb['magazinedb']);
            $this->smarty->assign('filter', $magazinedb['filter']);
            $this->smarty->assign('record_count', $magazinedb['record_count']);
            $this->smarty->assign('page_count', $magazinedb['page_count']);

            $special_ranks = get_rank_list();
            $send_rank[SEND_LIST . '_0'] = $GLOBALS['_LANG']['email_user'];
            $send_rank[SEND_USER . '_0'] = $GLOBALS['_LANG']['user_list'];
            foreach ($special_ranks as $rank_key => $rank_value) {
                $send_rank[SEND_RANK . '_' . $rank_key] = $rank_value;
            }
            $this->smarty->assign('send_rank', $send_rank);

            return $this->smarty->display('magazine_list.dwt');
        } elseif ($act == 'query') {

            $magazinedb = $this->magazineListManageService->getMagazine();

            $this->smarty->assign('magazinedb', $magazinedb['magazinedb']);
            $this->smarty->assign('filter', $magazinedb['filter']);
            $this->smarty->assign('record_count', $magazinedb['record_count']);
            $this->smarty->assign('page_count', $magazinedb['page_count']);

            $sort_flag = sort_flag($magazinedb['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('magazine_list.dwt'), '', ['filter' => $magazinedb['filter'], 'page_count' => $magazinedb['page_count']]);
        } elseif ($act == 'add') {

            $step = request()->input('step', '');

            if (empty($step)) {

                $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'magazine_list.php?act=list']);
                $this->smarty->assign(['ur_here' => $GLOBALS['_LANG']['04_magazine_list'], 'act' => 'add']);

                create_html_editor('magazine_content');

                return $this->smarty->display('magazine_list_add.dwt');
            } elseif ($step == 2) {

                $magazine_name = e(request()->input('magazine_name'));
                $magazine_content = trim(request()->input('magazine_content'));

                $time = TimeRepository::getGmTime();
                $data = [
                    'template_code' => md5($magazine_name . $time),
                    'is_html' => 1,
                    'template_subject' => $magazine_name,
                    'template_content' => $magazine_content,
                    'last_modify' => $time,
                    'type' => 'magazine'
                ];
                MailTemplates::insert($data);

                $links[] = ['text' => $GLOBALS['_LANG']['04_magazine_list'], 'href' => 'magazine_list.php?act=list'];
                $links[] = ['text' => $GLOBALS['_LANG']['add_new'], 'href' => 'magazine_list.php?act=add'];
                return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
            }
        } elseif ($act == 'edit') {

            $id = intval(request()->input('id'));

            $step = request()->input('step', '');

            if (empty($step)) {
                $res = MailTemplates::where('type', 'magazine')->where('template_id', $id);
                $rt = BaseRepository::getToArrayFirst($res);

                $this->smarty->assign(['id' => $id, 'act' => 'edit', 'magazine_name' => $rt['template_subject'], 'magazine_content' => $rt['template_content']]);
                $this->smarty->assign(['ur_here' => $GLOBALS['_LANG']['04_magazine_list'], 'act' => 'edit']);
                $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['go_list'], 'href' => 'magazine_list.php?act=list']);

                if ($rt['template_content']) {
                    if (config('shop.open_oss', 0) == 1) {
                        $bucket_info = $this->dscRepository->getBucketInfo();
                        $endpoint = $bucket_info['endpoint'];
                    } else {
                        $endpoint = url('/');
                    }

                    $desc_preg = get_goods_desc_images_preg($endpoint, $rt['template_content']);
                    $rt['template_content'] = $desc_preg['goods_desc'];
                }

                create_html_editor('magazine_content', $rt['template_content']);

                return $this->smarty->display('magazine_list_add.dwt');
            } elseif ($step == 2) {

                $magazine_name = e(request()->input('magazine_name'));
                $magazine_content = trim(request()->input('magazine_content'));

                $data = [
                    'is_html' => 1,
                    'template_subject' => $magazine_name,
                    'template_content' => $magazine_content,
                    'last_modify' => TimeRepository::getGmTime()
                ];
                MailTemplates::where('type', 'magazine')->where('template_id', $id)->update($data);

                $links[] = ['text' => $GLOBALS['_LANG']['04_magazine_list'], 'href' => 'magazine_list.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
            }
        } elseif ($act == 'del') {

            $id = intval(request()->input('id'));

            MailTemplates::where('type', 'magazine')->where('template_id', $id)->delete();

            $links[] = ['text' => $GLOBALS['_LANG']['04_magazine_list'], 'href' => 'magazine_list.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
        } elseif ($act == 'addtolist') {

            $id = intval(request()->input('id'));

            $pri = (int)request()->input('pri', 0);
            $pri = !empty($pri) ? 1 : 0;
            $start = (int)request()->input('start', 0);
            $send_rank = request()->input('send_rank');

            $rank_array = explode('_', $send_rank);

            $template_id = MailTemplates::where('type', 'magazine')->where('template_id', $id)->value('template_id');
            $template_id = $template_id ? $template_id : 0;

            if (!empty($template_id)) {
                if (SEND_LIST == $rank_array['0']) {
                    $count = EmailList::where('stat', 1)->count();
                    if ($count > $start) {
                        $res = EmailList::where('stat', 1)->select('email')->offset($start)->limit(100);
                        $query = BaseRepository::getToArrayGet($res);

                        $i = 0;
                        foreach ($query as $rt) {
                            $data = [
                                'email' => $rt['email'] ?? '',
                                'template_id' => $id,
                                'pri' => $pri,
                                'last_send' => TimeRepository::getGmTime()
                            ];
                            EmailSendlist::insert($data);
                            $i++;
                        }

                        if ($i == 100) {
                            $start = $start + 100;
                        } else {
                            $start = $start + $i;
                        }

                        $links[] = ['text' => sprintf($GLOBALS['_LANG']['finish_list'], $start), 'href' => "magazine_list.php?act=addtolist&id=$id&pri=$pri&start=$start&send_rank=$send_rank"];
                        return sys_msg($GLOBALS['_LANG']['finishing'], 0, $links);
                    } else {

                        MailTemplates::where('type', 'magazine')->where('template_id', $id)->update(['last_send' => TimeRepository::getGmTime()]);

                        $links[] = ['text' => $GLOBALS['_LANG']['04_magazine_list'], 'href' => 'magazine_list.php?act=list'];
                        return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
                    }
                } else {

                    if (SEND_USER == $rank_array['0']) {
                        $count = Users::where('is_validated', 1)->count('user_id');
                        $query = Users::where('is_validated', 1)->select('email')->skip($start)->take(100);
                    } else {
                        $count = Users::where('is_validated', 1)->where('user_rank', $rank_array['1'])->count('user_id');
                        $query = Users::where('is_validated', 1)->select('email')->where('user_rank', $rank_array['1'])->skip($start)->take(100);
                    }

                    if ($count > $start) {
                        $query = BaseRepository::getToArrayGet($query);
                        $i = 0;
                        foreach ($query as $rt) {
                            $data = [
                                'email' => $rt['email'] ?? '',
                                'template_id' => $id,
                                'pri' => $pri,
                                'last_send' => TimeRepository::getGmTime()
                            ];
                            EmailSendlist::insert($data);

                            $i++;
                        }

                        if ($i == 100) {
                            $start = $start + 100;
                        } else {
                            $start = $start + $i;
                        }

                        $links[] = ['text' => sprintf($GLOBALS['_LANG']['finish_list'], $start), 'href' => "magazine_list.php?act=addtolist&id=$id&pri=$pri&start=$start&send_rank=$send_rank"];
                        return sys_msg($GLOBALS['_LANG']['finishing'], 0, $links);
                    } else {

                        MailTemplates::where('type', 'magazine')->where('template_id', $id)->update(['last_send' => TimeRepository::getGmTime()]);

                        $links[] = ['text' => $GLOBALS['_LANG']['04_magazine_list'], 'href' => 'magazine_list.php?act=list'];
                        return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
                    }
                }
            } else {
                $links[] = ['text' => $GLOBALS['_LANG']['04_magazine_list'], 'href' => 'magazine_list.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['edit_ok'], 0, $links);
            }
        }
    }
}
