<?php

namespace App\Services\App;

use App\Models\AppAd;
use App\Models\AppAdPosition;
use App\Models\AppClient;
use App\Models\AppClientProduct;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * Class AppService
 * @package App\Services\App
 */
class AppService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 通过 type 获取广告位信息
     * @param string $position_type
     * @return int
     */
    public function adPositionInfoByType($position_type = '')
    {
        $position_id = AppAdPosition::where('position_type', $position_type)->value('position_id');

        return $position_id ?? 0;
    }

    /**
     * app广告列表
     * @param int $position_id
     * @param array $offset
     * @return array
     */
    public function adList($position_id = 0, $offset = [])
    {
        $model = AppAd::where('position_id', $position_id)
            ->where('enabled', 1);

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $model = $model->orderBy('sort_order', 'ASC')
            ->orderBy('ad_id', 'DESC')
            ->get();

        $list = $model ? $model->toArray() : [];

        if (!empty($list)) {
            foreach ($list as $k => $value) {
                if ($value['media_type'] == 0) {
                    $list[$k]['ad_code'] = empty($value['ad_code']) ? '' : $this->dscRepository->getImagePath($value['ad_code']);
                }
            }

            $list = collect($list)->values()->all();
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * app自动更新
     * @param string $appid
     * @return array
     */
    public function autoUpdate($appid = '')
    {
        $info = ['status' => 200, 'content' => [], 'message' => ''];

        if (!empty($appid)) {
            $client_id = AppClient::where('appid', $appid)->value('id');

            if (!empty($client_id)) {
                $now = TimeRepository::getGmTime();
                $product = AppClientProduct::where('client_id', $client_id)->where('is_show', 1)->where('update_time', '<=', $now)->orderBy('id', 'DESC');
                $product = BaseRepository::getToArrayFirst($product);
                if (!empty($product)) {
                    $info['content'] = $product;
                    $info['message'] = 'success';
                } else {
                    $info['message'] = 'no update';
                }
            } else {
                $info['status'] = 403;
                $info['message'] = 'null client_id';
            }
        } else {
            $info['status'] = 403;
            $info['message'] = 'null appid';
        }

        return $info;
    }
}
