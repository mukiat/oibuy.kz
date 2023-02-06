<?php

namespace App\Modules\Admin\Controllers;

use App\Services\Lottery\LotteryPrizeService;
use App\Services\Lottery\LotteryRecordService;
use App\Services\Lottery\LotteryService;
use App\Services\User\UserRankService;
use Illuminate\Http\Request;

/**
 * Class LotteryController
 * @package App\Modules\Admin\Controllers
 */
class LotteryController extends InitController
{
    /**
     * @var \App\Services\Lottery\LotteryService
     */
    private $lotteryService;

    /**
     * @var \App\Services\Lottery\LotteryPrizeService
     */
    private $lotteryPrizeService;

    /**
     * @var \App\Services\Lottery\LotteryRecordService
     */
    private $lotteryRecordService;

    /**
     * @var \App\Services\User\UserRankService
     */
    private $userRankService;

    /**
     * LotteryController constructor.
     * @param \App\Services\Lottery\LotteryService $lotteryService
     * @param \App\Services\Lottery\LotteryPrizeService $lotteryPrizeService
     * @param \App\Services\Lottery\LotteryRecordService $lotteryRecordService
     * @param \App\Services\User\UserRankService $userRankService
     */
    public function __construct(LotteryService $lotteryService,
                                LotteryPrizeService $lotteryPrizeService,
                                LotteryRecordService $lotteryRecordService,
                                UserRankService $userRankService)
    {
        $this->lotteryService = $lotteryService;
        $this->lotteryPrizeService = $lotteryPrizeService;
        $this->lotteryRecordService = $lotteryRecordService;
        $this->userRankService = $userRankService;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $act = $request->get('act', 'index');
        $admin = get_admin_ru_id();

        // 获取活动配置
        $config = $this->lotteryService->get($admin['ru_id']);
        if (empty($config)) {
            $config = $this->lotteryService->init($admin['ru_id']);
        }
        $config['participant'] = explode(',', $config['participant']);

        /**
         * 展示活动配置
         */
        if ($act === 'index') {
            // 获取会员等级
            $filter = [
                'membership_card_display' => 'hide' //1.4.4 优化除分销权益卡绑定以外的等级都显示
            ];

            $ranks = $this->userRankService->getUserRank($filter);
            foreach ($ranks as $k => $v) {
                $ranks[$k] = ['name' => $v, 'checked' => in_array($k, $config['participant']) ? 'checked' : ''];
            }

            // 获取活动奖品
            $prizes = $this->lotteryPrizeService->all($config['id'], $admin['ru_id']);

            $this->smarty->assign('ur_here', '消费抽奖');
            $this->smarty->assign('config', $config);
            $this->smarty->assign('ranks', $ranks);
            $this->smarty->assign('prizes', $prizes);

            return $this->smarty->display('lottery.dwt');
        }

        /**
         * 更新活动配置
         */
        if ($act === 'update') {
            $config = [
                "id" => $request->get('id'),
                "active_state" => intval($request->get('active_state', 0)),
                "start_time" => $request->get('start_time'),
                "end_time" => $request->get('end_time'),
                "active_desc" => $request->get('active_desc'),
                "participant" => implode(',', $request->get('user_rank')),
                "single_amount" => $request->get('single_amount'),
                "participate_number" => $request->get('participate_number'),
            ];

            $this->lotteryService->update($config, $admin['ru_id']);

            return sys_msg($GLOBALS['_LANG']['edit_success'], 0);
        }

        /**
         * 展示奖品添加表单
         */
        if ($act === 'prize_create') {

        }

        /**
         * 保存活动奖品
         */
        if ($act === 'prize_store') {

        }

        /**
         * 编辑活动奖品
         */
        if ($act === 'prize_edit') {

        }

        /**
         * 更新活动奖品
         */
        if ($act === 'prize_update') {

        }

        /**
         * 移除活动奖品
         */
        if ($act === 'prize_remove') {

        }

        /**
         * 展示活动记录
         */
        if ($act === 'record') {
            $list = $this->getRecord($config['id'], $request);

            $this->smarty->assign('ur_here', '参与记录');
            $this->smarty->assign('list', $list['item']);
            $this->smarty->assign('filter', $list['filter']);
            $this->smarty->assign('record_count', $list['record_count']);
            $this->smarty->assign('page_count', $list['page_count']);
            $this->smarty->assign('full_page', 1);

            return $this->smarty->display('lottery_record.dwt');
        }
    }

    /**
     * 获取活动记录
     * @param int $lottery_id
     * @param $request
     * @return array
     */
    private function getRecord($lottery_id = 0, $request)
    {
        $filter['lottery_id'] = $lottery_id;
        $filter['type'] = $request->get('type', 0);
        $filter['keyword'] = $request->get('keyword', '');
        $filter['page_size'] = $request->get('page_size', 20);

        $result = $this->lotteryRecordService->all($filter);

        return ['item' => $result['data'], 'filter' => $filter, 'page_count' => $result['last_page'], 'record_count' => $result['total']];
    }
}
