<?php

namespace App\Services\Presale;

use App\Models\Goods;
use App\Models\PresaleActivity;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

class PresaleService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    ) {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 返回搜索预售商品ID
     *
     * @param array $keywords
     * @param array $children
     * @return array
     */
    public function presaleActivitySearch($keywords = [], $children = [])
    {
        $time = TimeRepository::getGmTime();

        if ($keywords) {
            $res = PresaleActivity::select('goods_id');

            foreach ($keywords as $key => $val) {
                $res = $res->where('start_time', '<', $time)->where('end_time', '>', $time);
                $res = $res->where(function ($query) use ($val) {
                    $val = $this->dscRepository->mysqlLikeQuote(trim($val));
                    $query->orWhere('goods_name', 'like', '%' . $val . '%');
                });
            }

            $res = BaseRepository::getToArrayGet($res);
            $goods_id = BaseRepository::getKeyPluck($res, 'goods_id');
        }

        return $goods_id ?? [];
    }
}
