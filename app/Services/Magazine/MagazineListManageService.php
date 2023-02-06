<?php

namespace App\Services\Magazine;

use App\Entities\EmailSendlist;
use App\Models\MailTemplates;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * Class MagazineListManageService
 * @package App\Services\Magazine
 */
class MagazineListManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 邮件订阅
     * @return array
     */
    public function getMagazine()
    {
        $sort_by = e(request()->input('sort_by', 'template_id'));
        $sort_order = e(request()->input('sort_order', 'DESC'));

        $filter['sort_by'] = trim($sort_by);
        $filter['sort_order'] = trim($sort_order);

        $filter['record_count'] = MailTemplates::where('type', 'magazine')->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */
        $res = MailTemplates::where('type', 'magazine')
            ->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $magazinedb = BaseRepository::getToArrayGet($res);

        if ($magazinedb) {
            foreach ($magazinedb as $k => $v) {
                $magazinedb[$k]['last_modify'] = TimeRepository::getLocalDate('Y-m-d', $v['last_modify']);
                $magazinedb[$k]['last_send'] = TimeRepository::getLocalDate('Y-m-d', $v['last_send']);
            }
        }

        return ['magazinedb' => $magazinedb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 邮件队列
     * @return array
     */
    public function getSendlist()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_sendlist';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $request = request()->all();
        $request = !empty($get_filter) ? BaseRepository::getArrayMerge($request, $get_filter) : $request;

        $filter['sort_by'] = empty($request['sort_by']) ? 'id' : trim($request['sort_by']);
        $filter['sort_order'] = empty($request['sort_order']) ? 'DESC' : trim($request['sort_order']);

        $model = EmailSendlist::query()->from('email_sendlist as e');
        $model = $model->leftJoin('mail_templates as m', 'e.template_id', '=', 'm.template_id');

        $filter['record_count'] = $model->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        /* 查询 */
        $list = $model->select('e.id', 'e.email', 'e.pri', 'e.error', 'e.last_send', 'm.template_subject', 'm.type')->offset($filter['start'])
            ->limit($filter['page_size'])
            ->orderBy($filter['sort_by'], $filter['sort_order'])
            ->get();
        $listdb = $list ? $list->toArray() : [];

        if ($listdb) {
            foreach ($listdb as $key => $val) {
                $listdb[$key]['last_send'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['last_send']);

                if (config('shop.show_mobile') == 0) {
                    $listdb[$key]['email'] = $this->dscRepository->stringToStar($val['email']);
                }
            }
        }

        return ['listdb' => $listdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }
}
