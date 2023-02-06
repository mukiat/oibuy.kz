<?php

namespace App\Services\App;

use App\Models\AppAd;
use App\Models\AppAdPosition;
use App\Models\AppClient;
use App\Models\AppClientProduct;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Common\StrRepository;

/**
 * APP 后台管理
 * Class AppManageService
 * @package App\Services\App
 */
class AppManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 添加或更新广告位
     * @param null $data
     * @return bool
     */
    public function updateAdPostion($data = null)
    {
        if (is_null($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'app_ad_position');

        $position_id = $data['position_id'] ?? 0;
        if (!empty($position_id)) {
            $res = AppAdPosition::where('position_id', $position_id)->update($data);
        } else {
            $res = AppAdPosition::create($data);
        }

        return $res;
    }

    /**
     * 广告位列表
     * @param int $position_id
     * @param string $keywords
     * @param array $offset
     * @return array
     */
    public function adPositionList($position_id = 0, $keywords = '', $offset = [])
    {
        $model = AppAdPosition::whereRaw(1);

        if (!empty($position_id)) {
            $model = $model->where('position_id', $position_id);
        }

        if (!empty($keywords)) {
            $model = $model->where('position_name', 'like', '%' . $keywords . '%');
        }

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $model = $model->orderBy('position_id', 'DESC')
            ->get();

        $list = $model ? $model->toArray() : [];

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 广告位信息
     * @param int $position_id
     * @return array
     */
    public function adPositionInfo($position_id = 0)
    {
        $res = AppAdPosition::where('position_id', $position_id)->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 检查广告位下是否有广告
     * @param int $position_id
     * @return bool
     */
    public function checkAd($position_id = 0)
    {
        $model = AppAdPosition::where('position_id', $position_id);

        $model = $model->whereHasIn('appAds');

        $count = $model->count();

        return $count > 0 ? true : false;
    }

    /**
     * 删除广告位
     * @param int $position_id
     * @return bool
     */
    public function deleteAdPosition($position_id = 0)
    {
        if (empty($position_id)) {
            return false;
        }

        $res = AppAdPosition::where('position_id', $position_id)->delete();

        return $res;
    }

    /**
     * 添加或更新广告
     * @param null $data
     * @return bool
     */
    public function updateAd($data = null)
    {
        if (is_null($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'app_ad');

        $ad_id = $data['ad_id'] ?? 0;
        if (!empty($ad_id)) {
            $res = AppAd::where('ad_id', $ad_id)->update($data);
        } else {
            $res = AppAd::create($data);
        }

        return $res;
    }

    /**
     * app广告列表
     * @param int $position_id
     * @param string $keywords
     * @param array $offset
     * @return array
     */
    public function adList($position_id = 0, $keywords = '', $offset = [])
    {
        $model = AppAd::whereRaw(1);

        if (!empty($position_id)) {
            $model = $model->where('position_id', $position_id);
        }

        if (!empty($keywords)) {
            $model = $model->where('ad_name', 'like', '%' . $keywords . '%');
        }

        $model = $model->with([
            'appAdPosition'
        ]);

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
                    // 远程图片地址
                    if (strtolower(substr($value['ad_code'], 0, 4)) == 'http') {
                        $list[$k]['url_src'] = $value['ad_code'];
                        $value['ad_code'] = '';
                    }

                    $list[$k]['ad_code'] = empty($value['ad_code']) ? '' : $this->dscRepository->getImagePath($value['ad_code']);
                }

                if (isset($value['app_ad_position']) && !empty(isset($value['app_ad_position']))) {
                    $list[$k]['position_name'] = $value['app_ad_position']['position_name'];
                }
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 广告信息
     * @param int $ad_id
     * @return array
     */
    public function adInfo($ad_id = 0)
    {
        $model = AppAd::where('ad_id', $ad_id);

        $model = $model->with([
            'appAdPosition'
        ]);

        $model = $model->first();

        $res = $model ? $model->toArray() : [];

        if (!empty($res)) {
            if ($res['media_type'] == 0) {
                // 远程图片地址
                if (strtolower(substr($res['ad_code'], 0, 4)) == 'http') {
                    $res['url_src'] = $res['ad_code'];
                    $res['ad_code'] = '';
                }

                $res['ad_code'] = empty($res['ad_code']) ? '' : $this->dscRepository->getImagePath($res['ad_code']);
            }
        }

        return $res;
    }

    /**
     * 修改广告状态
     * @param int $ad_id
     * @param int $status
     * @return bool
     */
    public function updateAdStatus($ad_id = 0, $status = 0)
    {
        if (empty($ad_id)) {
            return false;
        }

        $model = AppAd::where('ad_id', $ad_id)->first();

        $model->enabled = $status;

        $model->save();

        return true;
    }

    /**
     * 删除广告
     * @param int $ad_id
     * @return bool
     */
    public function deleteAd($ad_id = 0)
    {
        if (empty($ad_id)) {
            return false;
        }

        $res = AppAd::where('ad_id', $ad_id)->delete();

        return $res;
    }

    /*
    * app客户端
    */
    public function clientList()
    {
        $list = AppClient::whereRaw('1');
        return BaseRepository::getToArrayGet($list);
    }

    /*
    * app客户端操作
    */
    public function updateAppClient($data)
    {
        $id = $data['id'] ?? 0;
        $arr['name'] = $data['name'] ?? '';

        if ($id > 0) {
            AppClient::where('id', $id)->update($arr);
        } else {
            $arr['create_time'] = TimeRepository::getGmTime();
            $arr['appid'] = $this->autoCreateAppId($arr);
            AppClient::insert($arr);
        }
    }

    /*
    * app客户端检查是否重复
    */
    public function checkClient($data, $client_id)
    {
        return AppClient::where('name', $data['name'])->where('id', '<>', $client_id)->count();
    }

    /*
    * app客户端检查是否重复
    */
    public function clientInfo($client_id)
    {
        $row = AppClient::where('id', $client_id);

        return BaseRepository::getToArrayFirst($row);
    }

    /*
    * app客户端
    */
    public function clientProductList($client_id = 0, $offset = [])
    {
        $model = AppClientProduct::whereRaw(1);

        if (!empty($client_id)) {
            $model = $model->where('client_id', $client_id);
        }

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $model = $model->orderBy('id', 'DESC')
            ->get();

        $list = $model ? $model->toArray() : [];

        if (!empty($list)) {
            foreach ($list as $k => $val) {
                $list[$k]['update_desc'] = StrRepository::limit($val['update_desc'], 20);
                $list[$k]['update_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $val['update_time']);
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /*
    * app客户端产品检查
    */
    public function clientProductInfo($product_id)
    {
        $row = AppClientProduct::where('id', $product_id);
        $info = BaseRepository::getToArrayFirst($row);

        if (!empty($info)) {
            $info['update_time'] = $info['update_time'] ? TimeRepository::getLocalDate('Y-m-d H:i:s', $info['update_time']) : '';
        }
        return $info;
    }

    /*
    * app客户端检查是否重复
    */
    public function checkClientProduct($data, $client_id, $product_id)
    {
        return AppClientProduct::where(function ($query) use ($data) {
            $query->where('version_id', $data['version_id']);
        })->where('client_id', $client_id)->where('id', '<>', $product_id)->count();
    }

    /*
    * app客户端产品
    */
    public function updateAppClientProduct($data)
    {
        $id = $data['product_id'] ?? 0;
        $arr = [];

        if (isset($data['version_id'])) {
            $arr['version_id'] = $data['version_id'];
        }

        if (isset($data['client_id'])) {
            $arr['client_id'] = $data['client_id'];
        }

        if (isset($data['update_desc'])) {
            $arr['update_desc'] = $data['update_desc'];
        }

        if (isset($data['download_url'])) {
            $arr['download_url'] = $data['download_url'];
        }

        if (isset($data['is_show'])) {
            $arr['is_show'] = $data['is_show'];
        }

        if (isset($data['update_time'])) {
            $arr['update_time'] = TimeRepository::getLocalStrtoTime($data['update_time']);
        }
        if ($arr) {
            if ($id > 0) {
                AppClientProduct::where('id', $id)->update($arr);
            } else {
                $arr['create_time'] = TimeRepository::getGmTime();
                AppClientProduct::insert($arr);
            }
        }
    }

    /**
     * 删除客户端
     * @param int $client_id
     * @return bool
     */
    public function deleteClient($client_id = 0)
    {
        if (empty($client_id)) {
            return false;
        }

        $res = AppClient::where('id', $client_id)->delete();

        if ($res) {
            AppClientProduct::where('client_id', $client_id)->delete();
        }

        return $res;
    }

    /**
     * 删除客户端产品
     * @param int $client_id
     * @return bool
     */
    public function deleteClientProduct($product_id = 0)
    {
        if (empty($product_id)) {
            return false;
        }

        return AppClientProduct::where('id', $product_id)->delete();
    }

    /**
     * 获取客户端产品名称
     * @param int $client_id
     * @return bool
     */
    public function getClientName($client_id = 0)
    {
        if (empty($client_id)) {
            return false;
        }

        return AppClient::where('id', $client_id)->value('name');
    }

    /**
     * 当前时间
     */
    public function getNowTime()
    {
        return TimeRepository::getLocalDate('Y-m-d H:i:s');
    }

    /**
     * 自动生成appid
     * 生成规则 strtolower(md5(name . create_time)) 取中间16位
     * @return string
     */
    public function autoCreateAppId($arr)
    {
        $name = $arr['name'] ?? '';
        $create_time = $arr['create_time'] ?? '';
        $md5_str = strtolower(md5($name . $create_time));

        return substr($md5_str, 7, 16); // 取中间16位
    }
}
