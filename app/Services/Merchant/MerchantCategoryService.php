<?php

namespace App\Services\Merchant;

use App\Models\MerchantsCategory;
use App\Repositories\Common\BaseRepository;

class MerchantCategoryService
{
    public function merGoodsCatList($seller_id = 0)
    {
        $res = MerchantsCategory::select('cat_id', 'cat_id as id', 'cat_name', 'cat_name as name', 'user_id', 'user_id as ru_id', 'parent_id')
            ->where("user_id", $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        $catList = [];
        if ($res) {

            $sql = [
                'where' => [
                    [
                        'name' => 'parent_id',
                        'value' => 0
                    ]
                ]
            ];

            $catList = BaseRepository::getArraySqlGet($res, $sql, 1);

            foreach ($catList as $key => $val) {

                $sql = [
                    'where' => [
                        [
                            'name' => 'parent_id',
                            'value' => $val['id']
                        ]
                    ]
                ];

                $childList = BaseRepository::getArraySqlGet($res, $sql, 1);

                if ($childList) {
                    foreach ($childList as $idx => $row) {

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'parent_id',
                                    'value' => $row['id']
                                ]
                            ]
                        ];

                        $childTowList = BaseRepository::getArraySqlGet($res, $sql, 1);

                        $childList[$idx]['cat_id'] = $childTowList;

                        if ($childTowList) {
                            foreach ($childTowList as $k => $v) {
                                $childList[$idx]['cat_id'][$k]['cat_id'] = [];
                            }
                        }
                    }
                }

                $catList[$key]['cat_id'] = $childList;
            }
        }

        return $catList;
    }
}