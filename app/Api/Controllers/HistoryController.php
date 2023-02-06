<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Repositories\Common\SessionRepository;
use App\Services\History\HistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class HistoryController
 * @package App\Api\Controllers
 */
class HistoryController extends Controller
{
    /**
     * 生成缓存------浏览记录
     *
     * @param Request $request
     * @param HistoryService $historyService
     * @param SessionRepository $sessionRepository
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, HistoryService $historyService, SessionRepository $sessionRepository)
    {
        //数据验证
        $this->validate($request, [
            'id' => 'required|integer',
            'name' => 'required|string',
            'img' => 'required|string',
        ]);

        $user_id = $this->authorization();
        if ($user_id == 0) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $info = $request->all();
        if (!empty($info)) {

            $goods_id = (int)$info['id'];
            $historyService->goodsHistoryList($user_id, $goods_id);
        }

        return $this->succeed(['code' => '200']);
    }

    /**
     * 获得浏览记录
     *
     * @param Request $request
     * @param HistoryService $historyService
     * @return array|JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, HistoryService $historyService)
    {
        $user_id = $this->authorization();

        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        return $historyService->getHistoryListMobile($user_id, $this->warehouse_id, $this->area_id, $this->area_city);
    }

    /**
     * 获得浏览记录
     *
     * @param Request $request
     * @param HistoryService $historyService
     * @return array|JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, HistoryService $historyService)
    {
        $info = $request->all();
        $key = $info['id'] ?? 0;
        $user_id = $this->authorization();
        if (empty($user_id)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $historyService->historyDel($user_id, $key);
        if ($key > 0) {
            return $code = ['code' => 300, 'msg' => lang('common.delete_success')];
        } else {
            return $code = ['code' => 200, 'msg' => lang('common.delete_success')];
        }
    }
}
