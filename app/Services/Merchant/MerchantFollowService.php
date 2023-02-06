<?php

namespace App\Services\Merchant;


use App\Models\SellerFollowList;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class MerchantFollowService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 店铺关注二维码列表
     *
     * @param int $seller_id
     * @return mixed
     */
    public function getFollowList($seller_id = 0)
    {
        $res = SellerFollowList::where('seller_id', $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $row) {
                $res[$key]['qr_code'] = $this->dscRepository->getImagePath($row['qr_code']);
                $res[$key]['cover_pic'] = $this->dscRepository->getImagePath($row['cover_pic']);
            }
        }

        return $res;
    }
}