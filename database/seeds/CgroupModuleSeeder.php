<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 社区团购模块数据填充
 * Class CgroupModuleSeeder
 */
class CgroupModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->shopConfig();
        $this->groupbuyCategory();

        $this->adminAction();

        $this->returnCause();
        $this->reportType();
    }

    private function shopConfig()
    {
        // 增加社区团购模块分组
        $parent_id = DB::table('shop_config')->where('code', 'cgroup')->value('id');
        $parent_id = $parent_id ?? 0;
        if (empty($parent_id)) {
            // 默认数据
            $rows = [
                'parent_id' => 0,
                'code' => 'cgroup',
                'value' => 0,
                'type' => 'hidden',
                'store_range' => '',
                'sort_order' => 50,
                'shop_group' => ''
            ];
            $parent_id = DB::table('shop_config')->insertGetId($rows);
        }

        if ($parent_id > 0) {
            $recruit_leader = DB::table('shop_config')->where('code', 'recruit_leader')->count(); // 招募团长 dsc_shop_config 配置
            if (empty($recruit_leader)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'recruit_leader',
                    'type' => 'hidden',
                    'value' => 'a:4:{s:5:"title";s:15:"团长招募令";s:4:"link";s:43:"http://www.hbf.dscmall.zhuo/admin/index.php";s:7:"content";s:1095:"<ol class=" list-paddingleft-2" style="list-style-type: decimal;"><li><p>什么是团长？想成为团长需要具备什么条件？</p><p>商家通过线下或者线上推广的方式在各个社区招募团长，利用社交软件组成团长群，之后可定时发布团购活动，团长再将活动分享转发到自己维护的社区群中，活动结束之后商家发货给团长，团长安排社区的买家自提或团长配送，过程中团不会接触到买家支付的钱，也不需要囤货，利润会在团购结束后自动结算给团长。</p></li><li><p>什么是团长？想成为团长需要具备什么条件？</p><p>商家通过线下或者线上推广的方式在各个社区招募团长，利用社交软件组成团长群，之后可定时发布团购活动，团长再将活动分享转发到自己维护的社区群中，活动结束之后商家发货给团长，团长安排社区的买家自提或团长配送，过程中团不会接触到买家支付的钱，也不需要囤货，利润会在团购结束后自动结算给团长。</p><p><br/></p></li></ol>";s:4:"file";s:70:"data/groupbuy_recruit_img/w9RsEysI8x3zJ47Iy4KcA34PY17GsSI4Fxudeex6.png";}',
                    'store_range' => '',
                    'shop_group' => 'leader',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'recruit_leader')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
            }

            $leader_timeliness = DB::table('shop_config')->where('code', 'leader_timeliness')->count(); // 社区团购举报时效 dsc_shop_config 配置
            if (empty($leader_timeliness)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'leader_timeliness',
                    'type' => 'text',
                    'value' => '15',
                    'store_range' => '',
                    'shop_group' => 'leader_report',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'leader_timeliness')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
            }

            $need_agress_refound = DB::table('shop_config')->where('code', 'need_agress_refound')->count(); // 社区团购商家退款是否审核 dsc_shop_config 配置
            if (empty($need_agress_refound)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'need_agress_refound',
                    'type' => 'select',
                    'value' => '0',
                    'store_range' => '0,1',
                    'shop_group' => 'return_setting',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'need_agress_refound')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
            }

            $refound_money_type = DB::table('shop_config')->where('code', 'refound_money_type')->count(); // 社区团购优先退款方式 dsc_shop_config 配置
            if (empty($refound_money_type)) {
                $rows = [
                    'parent_id' => $parent_id,
                    'code' => 'refound_money_type',
                    'type' => 'options',
                    'value' => '1',
                    'store_range' => '1,2,6',
                    'shop_group' => 'return_setting',
                ];
                DB::table('shop_config')->insert($rows);
            } else {
                DB::table('shop_config')->where('code', 'refound_money_type')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'store_range' => '1,2,6', 'value' => '1']);
            }
        }


        $parent_id = DB::table('shop_config')->where('code', 'recruit_leader')->value('parent_id');
        $parent_id = $parent_id ?? 0;

        // 开启社区驿站
        $post = DB::table('shop_config')->where('code', 'open_community_post')->value('id'); // 开启社区驿站
        if (empty($post)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'open_community_post',
                    'type' => 'hidden',
                    'value' => '0',
                    'shop_group' => 'community',
                ]
            ];
            DB::table('shop_config')->insert($rows);
        } else {
            DB::table('shop_config')->where('id', $post)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
        }

        // 开启社区朋友圈
        $circles = DB::table('shop_config')->where('code', 'open_community_circles')->value('id');
        if (empty($circles)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'code' => 'open_community_circles',
                    'type' => 'hidden',
                    'value' => '0',
                    'shop_group' => 'community',
                ]
            ];
            DB::table('shop_config')->insert($rows);
        } else {
            DB::table('shop_config')->where('id', $circles)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id]);
        }

        DB::table('shop_config')->where('code', 'cgroup')->where('type', 'group')->update(['type' => 'hidden']);
    }

    private function groupbuyCategory()
    {
        $result = DB::table('groupbuy_category')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'cat_name' => '美食',
                    'parent_id' => '0',
                    'cat_img' => 'data/groupbuy_category_img/oMsNFHN6WRlYp42DnKPmllrFj2vhk2Ms9MbWgTJP.jpeg',
                ],
                [
                    'cat_name' => '水果',
                    'parent_id' => '0',
                    'cat_img' => 'data/groupbuy_category_img/msbWa2j1bube4ZBk4hZgTGMMnRelhE4iOaXLcIPJ.jpeg',
                ],
                [
                    'cat_name' => '服饰',
                    'parent_id' => '0',
                    'cat_img' => 'data/groupbuy_category_img/WLaAbtMDN1qpINreqmRjk4OUM0Lxhw7qFQ8q5wGh.jpeg',
                ],
                [
                    'cat_name' => '母婴',
                    'parent_id' => '0',
                    'cat_img' => 'data/groupbuy_category_img/zvVI6eAJm3bU1dWfLejDBz0J684MbA08zehOxe6K.jpeg',
                ],
                [
                    'cat_name' => '百货',
                    'parent_id' => '0',
                    'cat_img' => 'data/groupbuy_category_img/xKEBo4xU15iahyDC3EX0ODbvI9HHjZHhDrbONuhw.jpeg',
                ]
            ];
            DB::table('groupbuy_category')->insert($rows);
        }
    }

    /**
     * 增加权限
     */
    private function adminAction()
    {
        $parent_id = DB::table('admin_action')->where('action_code', 'groupbuy')->value('action_id');
        $parent_id = $parent_id ?? 0;

        if (empty($parent_id)) {
            // 默认数据
            $row = [
                'parent_id' => 0,
                'action_code' => 'groupbuy',
                'seller_show' => 1
            ];
            $parent_id = DB::table('admin_action')->insertGetId($row);
            $parent_id = $parent_id ?? 0;

            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'action_code' => 'groupbuy_goods_manage',
                    'seller_show' => 1
                ],
                [
                    'parent_id' => $parent_id,
                    'action_code' => 'groupbuy_order_manage',
                    'seller_show' => 1
                ],
                [
                    'parent_id' => $parent_id,
                    'action_code' => 'leader_settlement_manage',
                    'seller_show' => 1
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }

        // 驿站设置权限
        $result = DB::table('admin_action')->where('action_code', 'post_setting_manage')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'action_code' => 'post_setting_manage',
                    'seller_show' => 1
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }

        // 社团设置权限
        $result = DB::table('admin_action')->where('action_code', 'groupbuy_setting_manage')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'action_code' => 'groupbuy_setting_manage',
                    'seller_show' => 0
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }

        // 举报管理权限
        $result = DB::table('admin_action')->where('action_code', 'groupbuy_report_manage')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'parent_id' => $parent_id,
                    'action_code' => 'groupbuy_report_manage',
                    'seller_show' => 0
                ]
            ];
            DB::table('admin_action')->insert($rows);
        }

    }

    private function returnCause()
    {
        $result = DB::table('groupbuy_return_cause')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'cause_name' => '质量问题',
                    'parent_id' => '0',
                ],
                [
                    'cause_name' => '颜色问题',
                    'parent_id' => '0',
                ],
                [
                    'cause_name' => '服务问题',
                    'parent_id' => '0',
                ],
                [
                    'cause_name' => '意外损坏',
                    'parent_id' => '0',
                ]
            ];
            DB::table('groupbuy_return_cause')->insert($rows);
        }
    }

    private function reportType()
    {
        $result = DB::table('groupbuy_report_type')->count();
        if (empty($result)) {
            // 默认数据
            $rows = [
                [
                    'type_name' => '出售禁售品',
                    'status' => 1,
                ],
                [
                    'type_name' => '产品质量问题',
                    'status' => 1,
                ]
            ];
            DB::table('groupbuy_report_type')->insert($rows);
        }
    }
}
