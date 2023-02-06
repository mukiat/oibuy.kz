<?php

namespace App\Services\Seo;

use App\Models\Category;
use App\Models\Seo;
use App\Repositories\Common\BaseRepository;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;

class SeoManageService
{
    protected $commonManageService;
    protected $merchantCommonService;

    public function __construct(
        CommonManageService $commonManageService,
        MerchantCommonService $merchantCommonService
    ) {
        $this->commonManageService = $commonManageService;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * seo列表
     *
     * @return array
     */
    public function getSeo()
    {
        $res = Seo::whereRaw(1);
        $res = BaseRepository::getToArrayGet($res);

        $seo = [];
        if (is_array($res)) {
            foreach ($res as $value) {
                $seo[$value['type']] = $value;
            }
        }
        return $seo;
    }

    /*
    * 获取 seo 分类信息
    */
    public function getSeoCatInfo()
    {
        $res = Category::whereRaw('trim(cate_title) <> ?', '')
            ->orWhereRaw('trim(cate_keywords) <> ?', '')
            ->orWhereRaw('trim(cate_description) <> ?', '');
        $row = BaseRepository::getToArrayFirst($res);

        return $row ? $row : [];
    }
}
