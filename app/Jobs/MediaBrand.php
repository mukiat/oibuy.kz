<?php

namespace App\Jobs;

use App\Modules\WxMedia\Services\WxappShopBrandService;
use App\Repositories\Common\TimeRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Modules\WxMedia\Libraries\Media;
use App\Modules\Wxapp\Services\WxappConfigService;

class MediaBrand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * MediaBrand constructor.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     * @param WxappConfigService $wxappConfigService
     * @param WxappShopBrandService $wxappShopBrandService
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(WxappConfigService $wxappConfigService, WxappShopBrandService $wxappShopBrandService)
    {
        $all_list = $wxappShopBrandService->get_all_audit_brand();
        if (empty($all_list)) {
            return false;
        }

        $wxapp = $wxappConfigService->get_config(0);
        if (empty($wxapp) || (isset($wxapp['status']) && $wxapp['status'] == 0)) {
            return false;
        }

        // 小程序实例
        $config = [
            'appid' => $wxapp['wx_appid'] ?? '',
            'secret' => $wxapp['wx_appsecret'] ?? '',
        ];
        $this->media = new Media($config);

        foreach ($all_list as $key => $val) {
            $shop_data = [];
            $result = $this->media->shop_audit_result($val['audit_id']);
            if ($result) {
                $data = $result['data'] ?? [];
                if (!empty($data['status'])) {
                    if ($data['status'] == 1) {//审核成功
                        $shop_data['audit_status'] = 1;
                        $shop_data['audit_time'] = TimeRepository::getGmTime();
                        $shop_data['wxapp_brand_id'] = $data['brand_id'];//微信品牌id
                        $wxappShopBrandService->update_shop_brand($val['id'], $shop_data);
                    } else if ($data['status'] == 9) {//审核失败
                        $shop_data['audit_status'] = 9;
                        $shop_data['reasons_for_refusal'] = $data['reject_reason'];
                        $wxappShopBrandService->update_shop_brand($val['id'], $shop_data);
                    }
                }
            }
        }
    }
}
