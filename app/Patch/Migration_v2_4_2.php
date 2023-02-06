<?php

namespace App\Patch;

use App\Models\ShopConfig;
use Exception;

class Migration_v2_4_2
{
    public function run()
    {
        try {
            $this->migration();
            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {

    }

    private function seed()
    {
        $this->shop_config();

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.4.2'
        ]);
    }

    private function shop_config()
    {
        $parent_id = ShopConfig::where('code', 'shopping_flow')->where('type', 'group')->value('id');
        if ($parent_id > 0) {
            $result = ShopConfig::where('code', 'invoice_type')->count();
            if (empty($result)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'invoice_type',
                    'value' => '0',
                    'type' => 'manual',
                    'store_range' => '',
                    'sort_order' => 100,
                    'shop_group' => ''
                ];
                $id = ShopConfig::insertGetId($rows);

                if ($id > 0) {
                    ShopConfig::where('code', 'invoice_content')->update([
                        'sort_order' => 100
                    ]);
                }
            }
        }

        ShopConfig::where('code', 'invoice_type')->update([
            'type' => 'hidden'
        ]);

        // 增加客服管理模块
        $customer_service = ShopConfig::where('code', 'system_customer_service')->where('type', 'group')->count();
        if (empty($customer_service)) {
            $rows = [
                'parent_id' => 0,
                'code' => 'system_customer_service',
                'value' => '0',
                'type' => 'group',
                'store_range' => '',
                'sort_order' => 50,
                'shop_group' => ''
            ];
            $customer_service_id = ShopConfig::insertGetId($rows);

            if ($customer_service_id > 0) {
                // 增加客服类型
                $typeRows = [
                    'parent_id' => $customer_service_id,
                    'code' => 'customer_service_type',
                    'value' => '1',
                    'type' => 'select',
                    'store_range' => '1,2,3',
                    'sort_order' => 1,
                    'shop_group' => ''
                ];
                ShopConfig::insertGetId($typeRows);

                // 更新QQ配置
                $qqName = $qqNumber = '';

                $kf_qq = ShopConfig::where('code', 'qq')->value('value');
                if (!empty($kf_qq)) {
                    $kf_qq = explode("\n", $kf_qq);
                    foreach ($kf_qq as $k => $v) {
                        list($qqName, $qqNumber) = explode("|", $v);
                        break;
                    }
                }

                ShopConfig::where('code', 'ym')->update([
                    'parent_id' => $customer_service_id,
                    'code' => 'qq_name',
                    'type' => 'text',
                    'value' => $qqName,
                    'sort_order' => 2,
                ]);

                ShopConfig::where('code', 'qq')->update([
                    'parent_id' => $customer_service_id,
                    'type' => 'text',
                    'value' => $qqNumber,
                    'sort_order' => 3,
                ]);

                // 更新自定义客服链接
                ShopConfig::where('code', 'service_url')->update([
                    'parent_id' => $customer_service_id,
                    'sort_order' => 4,
                ]);
            }

        }

    }

    /**
     * @throws Exception
     */
    private function clean()
    {
        cache()->flush();
    }
}
