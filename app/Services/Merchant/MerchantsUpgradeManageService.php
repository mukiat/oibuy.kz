<?php

namespace App\Services\Merchant;

use App\Libraries\Image;
use App\Models\EntryCriteria;
use App\Models\SellerGrade;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Services\Commission\CommissionService;
use App\Services\Order\OrderService;

class MerchantsUpgradeManageService
{
    protected $merchantCommonService;
    protected $orderService;
    protected $commissionService;
    protected $dscRepository;

    public function __construct(
        OrderService $orderService,
        MerchantCommonService $merchantCommonService,
        CommissionService $commissionService,
        DscRepository $dscRepository
    ) {
        $this->merchantCommonService = $merchantCommonService;
        $this->orderService = $orderService;
        $this->commissionService = $commissionService;
        $this->dscRepository = $dscRepository;
    }

    /*分页*/
    public function getPzdList()
    {
        $filter['record_count'] = SellerGrade::where('is_open', 1);
        $filter = page_and_size($filter);
        /* 获活动数据 */
        $res = SellerGrade::where('is_open', 1)
            ->orderBy('id', 'ASC')
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);

        if ($row) {
            foreach ($row as $k => $v) {
                if ($v['entry_criteria']) {
                    $entry_criteria = unserialize($v['entry_criteria']);
                    foreach ($entry_criteria as $key => $val) {
                        $criteria_name = EntryCriteria::where('id', $val)->value('criteria_name');
                        $criteria_name = $criteria_name ? $criteria_name : '';
                        if ($criteria_name) {
                            $entry_criteria[$key] = $criteria_name;
                        }
                    }

                    $row[$k]['entry_criteria'] = implode(" , ", $entry_criteria);
                    $row[$k]['grade_img'] = $this->dscRepository->getImagePath($v['grade_img']);
                }
            }
        }

        return ['pzd_list' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 保存申请时的上传图片
     *
     * @param array $image_files 上传图片数组
     * @param array $file_id 图片对应的id数组
     * @param array $url
     * @return bool|mixed|void
     * @throws \Exception
     */
    public function uploadApplyFile($image_files = [], $file_id = [], $url = [])
    {
        $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

        /* 是否成功上传 */
        foreach ($file_id as $v) {
            $flag = false;
            if (isset($image_files['error'])) {
                if ($image_files['error'][$v] == 0) {
                    $flag = true;
                }
            } else {
                if ($image_files['tmp_name'][$v] != 'none' && $image_files['tmp_name'][$v]) {
                    $flag = true;
                }
            }
            if ($flag) {
                /*生成上传信息的数组*/
                $upload = [
                    'name' => $image_files['name'][$v],
                    'type' => $image_files['type'][$v],
                    'tmp_name' => $image_files['tmp_name'][$v],
                    'size' => $image_files['size'][$v],
                ];
                if (isset($image_files['error'])) {
                    $upload['error'] = $image_files['error'][$v];
                }

                $img_original = $image->upload_image($upload);
                if ($img_original === false) {
                    $error_msg = $image->error_msg();

                    return sys_msg($error_msg, 1, [], false);
                }
                $img_url[$v] = $img_original;
                /*删除原文件*/
                if (!empty($url[$v])) {
                    dsc_unlink(storage_public($url[$v]));
                }
            }
        }
        if (!empty($img_url)) {
            return $img_url;
        } else {
            return false;
        }
    }
}
