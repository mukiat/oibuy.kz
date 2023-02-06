<?php

namespace App\Modules\Admin\Controllers;

use App\Models\EmailSendlist;
use App\Models\MailTemplates;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Magazine\MagazineListManageService;

/**
 * 邮件订阅
 * Class ViewSendlistController
 * @package App\Modules\Admin\Controllers
 */
class ViewSendlistController extends InitController
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
        admin_priv('view_sendlist');

        $act = e(request()->input('act', 'list'));

        if ($act == 'list') {

            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['05_view_sendlist']);
            $this->smarty->assign('full_page', 1);

            $listdb = $this->magazineListManageService->getSendlist();

            $this->smarty->assign('listdb', $listdb['listdb']);
            $this->smarty->assign('filter', $listdb['filter']);
            $this->smarty->assign('record_count', $listdb['record_count']);
            $this->smarty->assign('page_count', $listdb['page_count']);

            return $this->smarty->display('view_sendlist.dwt');
        } elseif ($act == 'query') {

            $listdb = $this->magazineListManageService->getSendlist();

            $this->smarty->assign('listdb', $listdb['listdb']);
            $this->smarty->assign('filter', $listdb['filter']);
            $this->smarty->assign('record_count', $listdb['record_count']);
            $this->smarty->assign('page_count', $listdb['page_count']);

            $sort_flag = sort_flag($listdb['filter']);
            $this->smarty->assign($sort_flag['tag'], $sort_flag['img']);

            return make_json_result($this->smarty->fetch('view_sendlist.dwt'), '', ['filter' => $listdb['filter'], 'page_count' => $listdb['page_count']]);
        } elseif ($act == 'del') {

            $id = (int)request()->input('id', 0);

            EmailSendlist::where('id', $id)->delete();

            $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['del_ok'], 0, $links);
        }

        /*------------------------------------------------------ */
        //-- 批量删除
        /*------------------------------------------------------ */

        elseif ($act == 'batch_remove') {
            /* 检查权限 */
            $ids = request()->input('checkboxes');

            if (!empty($ids)) {
                EmailSendlist::whereIn('id', $ids)->delete();

                $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['del_ok'], 0, $links);
            } else {
                $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['no_select'], 0, $links);
            }
        }

        /*------------------------------------------------------ */
        //-- 批量发送
        /*------------------------------------------------------ */

        elseif ($act == 'batch_send') {
            /* 检查权限 */
            $ids = request()->input('checkboxes');

            if (!empty($ids)) {
                $list = EmailSendlist::whereIn('id', $ids)->orderBy('pri', 'DESC')->orderBy('last_send', 'ASC');
                $list = BaseRepository::getToArrayFirst($list);

                //发送列表为空
                if (empty($list['id'])) {
                    $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
                    return sys_msg($GLOBALS['_LANG']['mailsend_null'], 0, $links);
                }

                $res = EmailSendlist::whereIn('id', $ids)->orderBy('pri', 'DESC')->orderBy('last_send', 'ASC');
                $res = BaseRepository::getToArrayGet($res);

                foreach ($res as $row) {

                    if ($row['email_content']) {
                        $url = asset('/');
                        $row['email_content'] = str_replace($url . $url, $url, $row['email_content']);
                    }

                    //发送列表不为空，邮件地址为空
                    if (!empty($row['id']) && empty($row['email'])) {
                        EmailSendlist::where('id', $row['id'])->delete();
                        continue;
                    }

                    //查询相关模板
                    $rt = MailTemplates::where('template_id', $row['template_id']);
                    $rt = BaseRepository::getToArrayFirst($rt);

                    //如果是模板，则将已存入email_sendlist的内容作为邮件内容
                    //否则即是杂志，将mail_templates调出的内容作为邮件内容
                    if ($rt['type'] == 'template') {
                        $rt['template_content'] = stripslashes($row['email_content']);
                    }
                    $rt['template_content'] = stripslashes($rt['template_content']);

                    if ($row['email'] && $rt['template_id'] && $rt['template_content']) {
                        list($name) = explode('@', $row['email']);
                        if (CommonRepository::sendEmail($name, $row['email'], $rt['template_subject'], $rt['template_content'], $rt['is_html'])) {
                            //发送成功 从列表中删除
                            EmailSendlist::where('id', $row['id'])->delete();
                        } else {
                            //发送出错
                            if ($row['error'] < 3) {
                                $time = TimeRepository::getGmTime();
                                $extra = [
                                    'pri' => 0,
                                    'last_send' => $time,
                                ];
                                EmailSendlist::query()->where('id', $row['id'])->increment('error', 1, $extra);
                            } else {
                                //将出错超次的纪录删除
                                EmailSendlist::where('id', $row['id'])->delete();
                            }
                        }
                    } else {
                        //无效的邮件队列
                        EmailSendlist::where('id', $row['id'])->delete();
                    }
                }

                $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['mailsend_finished'], 0, $links);
            } else {
                $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['no_select'], 0, $links);
            }
        }

        /*------------------------------------------------------ */
        //-- 全部发送
        /*------------------------------------------------------ */

        elseif ($act == 'all_send') {

            $count = EmailSendlist::query()->count('id');

            //发送列表为空
            if (empty($count)) {
                $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
                return sys_msg($GLOBALS['_LANG']['mailsend_null'], 0, $links);
            }

            $res = EmailSendlist::query()->orderBy('pri', 'DESC')->orderBy('last_send', 'ASC');
            $res = BaseRepository::getToArrayGet($res);

            foreach ($res as $row) {
                //发送列表不为空，邮件地址为空
                if (!empty($row['id']) && empty($row['email'])) {
                    EmailSendlist::where('id', $row['id'])->delete();
                    continue;
                }

                //查询相关模板
                $rt = MailTemplates::where('template_id', $row['template_id']);
                $rt = BaseRepository::getToArrayFirst($rt);

                //如果是模板，则将已存入email_sendlist的内容作为邮件内容
                //否则即是杂志，将mail_templates调出的内容作为邮件内容
                if ($rt['type'] == 'template') {
                    $rt['template_content'] = stripslashes($row['email_content']);
                }
                $rt['template_content'] = stripslashes($rt['template_content']);

                if ($row['email'] && $rt['template_id'] && $rt['template_content']) {
                    list($name) = explode('@', $row['email']);
                    if (CommonRepository::sendEmail($name, $row['email'], $rt['template_subject'], $rt['template_content'], $rt['is_html'])) {
                        //发送成功 从列表中删除
                        EmailSendlist::where('id', $row['id'])->delete();
                    } else {
                        //发送出错
                        if ($row['error'] < 3) {
                            $time = TimeRepository::getGmTime();
                            $extra = [
                                'pri' => 0,
                                'last_send' => $time,
                            ];
                            EmailSendlist::query()->where('id', $row['id'])->increment('error', 1, $extra);
                        } else {
                            //将出错超次的纪录删除
                            EmailSendlist::where('id', $row['id'])->delete();
                        }
                    }
                } else {
                    //无效的邮件队列
                    EmailSendlist::where('id', $row['id'])->delete();
                }
            }

            $links[] = ['text' => $GLOBALS['_LANG']['05_view_sendlist'], 'href' => 'view_sendlist.php?act=list'];
            return sys_msg($GLOBALS['_LANG']['mailsend_finished'], 0, $links);
        }
    }
}
