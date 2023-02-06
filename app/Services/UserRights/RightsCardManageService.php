<?php

namespace App\Services\UserRights;

use App\Modules\Drp\Models\DrpShop;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Models\UserMembershipCard;
use App\Models\UserMembershipCardRights;
use App\Models\UserRank;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\User\UserRankService;

class RightsCardManageService
{
    protected $dscRepository;
    protected $discountService;
    protected $rightsCardCommonService;
    protected $userRightsCommonService;
    protected $userRankService;

    public function __construct(
        DscRepository $dscRepository,
        DiscountService $discountService,
        RightsCardCommonService $rightsCardCommonService,
        UserRightsCommonService $userRightsCommonService,
        UserRankService $userRankService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->discountService = $discountService;
        $this->rightsCardCommonService = $rightsCardCommonService;
        $this->userRightsCommonService = $userRightsCommonService;
        $this->userRankService = $userRankService;
    }

    /**
     * 会员权益卡列表
     * @param int $type
     * @param int $enable
     * @param array $offset
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function rightsCardList($type = 0, $enable = 0, $offset = [])
    {
        if (empty($type)) {
            return [];
        }

        $model = UserMembershipCard::query()->where('type', $type)->where('enable', $enable);

        $model = $model->with([
            'userMembershipCardRightsList' => function ($query) {
                $query->with([
                    'userMembershipRights' => function ($query) {
                        $query->select('id', 'name', 'code', 'icon', 'trigger_point', 'rights_configure')->where('enable', 1);
                    }
                ]);
            }
        ]);

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $model = $model->orderBy('sort', 'ASC')->get();

        $list = $model ? $model->toArray() : [];

        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $val = $this->rightsCardCommonService->transFormRightsCardInfo($val);

                // 权益列表
                $val['bind_rights_name_format'] = '';
                if (isset($val['user_membership_card_rights_list']) && !empty($val['user_membership_card_rights_list'])) {
                    foreach ($val['user_membership_card_rights_list'] as $k => $v) {
                        $val['user_membership_card_rights_list'][$k] = collect($v)->merge($v['user_membership_rights'])->except('user_membership_rights')->all();
                        // 权益名称
                        if (!empty($val['user_membership_card_rights_list'][$k]['name'])) {
                            $val['bind_rights_name_format'] .= $val['user_membership_card_rights_list'][$k]['name'] . '；<br/>';
                        }
                    }
                }

                $list[$key] = $val;
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 权益卡数量
     * @param int $type
     * @param int $enable
     * @return int
     */
    public function rightsCardTotal($type = 0, $enable = 0)
    {
        if (empty($type)) {
            return 0;
        }

        $model = UserMembershipCard::query()->where('type', $type)->where('enable', $enable);

        $total = $model->count();

        return $total;
    }

    /**
     * 权益卡列表
     * @param int $type
     * @param int $enable 权益卡状态
     * @param int $limit
     * @return array
     */
    public function cardList($type = 0, $enable = null, $limit = 100)
    {
        $model = UserMembershipCard::query()->where('type', $type);

        if (!is_null($enable)) {
            $model = $model->where('enable', $enable);
        }

        $model = $model->select('id', 'name', 'enable')->orderBy('sort', 'ASC')->limit($limit)->get();

        $list = $model ? $model->toArray() : [];

        return $list;
    }

    /**
     * @param int $type
     * @return int
     */
    public function rightsCardCount($type = 0)
    {
        if (empty($type)) {
            return 0;
        }

        $model = UserMembershipCard::query()->where('type', $type);

        return $model->count();
    }

    /**
     * 添加会员权益卡
     * @param array $data
     * @return bool
     */
    public function createRightsCard($data = [])
    {
        if (empty($data)) {
            return false;
        }

        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'user_membership_card');

        $data['add_time'] = TimeRepository::getGmTime();

        return UserMembershipCard::insertGetId($data);
    }

    /**
     * 检查名称是否重复
     * @param string $name
     * @param int $id
     * @return mixed
     */
    public function checkName($name = '', $id = 0)
    {
        if (empty($name)) {
            return false;
        }

        $model = UserMembershipCard::where('name', $name);

        if (!empty($id)) {
            $model = $model->where('id', '<>', $id);
        }

        $count = $model->count();

        return $count;
    }

    /**
     * 编辑会员权益卡
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateRightsCard($id = 0, $data = [])
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'user_membership_card');

        $data['update_time'] = TimeRepository::getGmTime();

        return UserMembershipCard::where('id', $id)->update($data);
    }

    /**
     * 删除会员权益卡
     * @param int $id
     * @return bool
     */
    public function deleteRightsCard($id = 0)
    {
        if (empty($id)) {
            return false;
        }

        Goods::where('membership_card_id', $id)->update(['membership_card_id' => 0]);

        return UserMembershipCard::where('id', $id)->delete();
    }

    /**
     * 查询权益卡信息
     * @param int $id
     * @return array
     */
    public function membershipCardInfo($id = 0)
    {
        if (empty($id)) {
            return [];
        }

        return $this->rightsCardCommonService->membershipCardInfo($id);
    }

    /**
     * 查询权益卡信息 处理
     * @param array $val
     * @return array
     */
    public function transFormRightsCardInfo($val = [])
    {
        if (empty($val)) {
            return [];
        }

        $val = $this->rightsCardCommonService->transFormRightsCardInfo($val);

        return $val;
    }

    /**
     * 会员权益卡下绑定的权益列表
     * @param array $item
     * @return bool|mixed
     */
    public function transFormCardRightsList($item = [])
    {
        if (empty($item)) {
            return false;
        }

        $list = $this->rightsCardCommonService->transFormCardRightsList($item);

        return $list;
    }

    /**
     * 绑定权益
     * @param int $membership_card_id
     * @param array $rights_data
     * @return bool
     */
    public function bindCardRights($membership_card_id = 0, $rights_data = [])
    {
        if (empty($membership_card_id) || empty($rights_data)) {
            return false;
        }

        $data = [];

        $now = TimeRepository::getGmTime();

        foreach ($rights_data as $k => $item) {
            $data[$k] = BaseRepository::getArrayfilterTable($item, 'user_membership_card_rights');

            $data[$k]['membership_card_id'] = $membership_card_id;
            $data[$k]['rights_id'] = $item;
            $data[$k]['add_time'] = $now;
        }

        $discount = $this->userRightsCommonService->userRightsInfo('discount');
        if (in_array($discount['id'], $rights_data)) {
            $rank_discount = $discount['rights_configure'][0]['value'] ?? 100;
            $user_rank_id = UserMembershipCard::query()->where('id', $membership_card_id)->value('user_rank_id');
            $this->userRankService->updateUserRank($user_rank_id, ['discount' => $rank_discount]);
        }

        $data = BaseRepository::recursiveNullVal($data);

        return UserMembershipCardRights::insert($data);
    }

    /**
     * 编辑会员卡权益
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCardRights($id = 0, $data = [])
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'user_membership_card_rights');

        $data['update_time'] = TimeRepository::getGmTime();

        return UserMembershipCardRights::where('id', $id)->update($data);
    }

    /**
     * 绑定权益列表
     * @param int $membership_card_id
     * @return array
     */
    public function bindCardRightsList($membership_card_id = 0)
    {
        if (empty($membership_card_id)) {
            return [];
        }

        $model = UserMembershipCardRights::query()->where('membership_card_id', $membership_card_id)
            ->get();

        $list = $model ? $model->toArray() : [];

        return $list;
    }

    /**
     * 查询信息
     * @param int $id
     * @return array|mixed
     */
    public function bindCardRightsInfo($id = 0)
    {
        if (empty($id)) {
            return [];
        }

        $model = UserMembershipCardRights::query()->where('id', $id);

        $model = $model->with([
            'userMembershipRights' => function ($query) {
                $query->where('enable', 1);
            }
        ]);

        $model = $model->first();

        $info = $model ? $model->toArray() : [];

        return $info;
    }

    /**
     * 解除绑定权益
     * @param int $id
     * @return bool
     */
    public function unbindCardRights($id = 0)
    {
        if (empty($id)) {
            return false;
        }

        return UserMembershipCardRights::where('id', $id)->delete();
    }

    /**
     * 解除会员权益卡下绑定的权益
     * @param int $membership_card_id
     * @return bool
     */
    public function deleteCardRightsByCardId($membership_card_id = 0)
    {
        if (empty($membership_card_id)) {
            return false;
        }

        return UserMembershipCardRights::where('membership_card_id', $membership_card_id)->delete();
    }

    /**
     * 搜索商品
     * @param string $keywords
     * @param int $cat_id
     * @param int $brand_id
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public function goodsListSearch($keywords = '', $cat_id = 0, $brand_id = 0, $offset = [], $filter = [])
    {
        $model = Goods::query()->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_real', 1)
            ->where('is_show', 1);

        if (config('shop.review_goods') == 1) {
            $model = $model->whereIn('review_status', [3, 4, 5]);
        }

        if ($cat_id > 0) {

            /**
             * 当前分类下的所有子分类
             * 返回一维数组
             */
            $cat_keys = app(CategoryService::class)->getCatListChildren($cat_id);
            $model = $model->whereIn('cat_id', $cat_keys);
        }
        if ($brand_id > 0) {
            $model = $model->where('brand_id', $brand_id);
        }

        // 已选择商品id
        $select_goods_id = [];
        if (!empty($filter)) {
            $filter['select_goods_id'] = $filter['select_goods_id'] ?? '';
            $select_goods_id = empty($filter['select_goods_id']) ? '' : explode(',', $filter['select_goods_id']);

            // 当前会员权益卡id
            $select_membership_card_id = $filter['membership_card_id'] ?? 0;

            if ($select_membership_card_id > 0) {
                // 编辑权益卡商品  显示当前权益卡已选择的 + 其他权益卡未选择的
                $model = $model->where(function ($query) use ($select_membership_card_id) {
                    $query->where('membership_card_id', $select_membership_card_id)->orWhere('membership_card_id', 0);
                });
            } else {
                // 添加权益卡商品 显示其他权益卡未选择的
                $model = $model->where('membership_card_id', 0)->where('is_on_sale', 1);
            }
        }

        if ($keywords) {
            $model = $model->where('goods_name', 'like', '%' . $keywords . '%')
                ->orWhere('goods_sn', 'like', '%' . $keywords . '%')
                ->orWhere('keywords', 'like', '%' . $keywords . '%');
        }

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])
                ->limit($offset['limit']);
        }

        $list = $model->select('goods_id', 'goods_name', 'goods_thumb', 'is_distribution', 'membership_card_id')
            ->orderBy('membership_card_id', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('goods_id', 'DESC')
            ->get();
        $list = $list ? $list->toArray() : [];

        if ($list) {
            foreach ($list as $k => $value) {
                $list[$k]['goods_thumb'] = empty($value['goods_thumb']) ? '' : $this->dscRepository->getImagePath($value['goods_thumb']);

                $list[$k]['checked'] = 0;
                if (!empty($select_goods_id) && in_array($value['goods_id'], $select_goods_id)) {
                    $list[$k]['checked'] = 1;
                }

                if (!empty($select_membership_card_id) && $select_membership_card_id == $value['membership_card_id']) {
                    $list[$k]['checked'] = 1;
                }
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 指定购买商品绑定(取消)会员权益卡
     * @param int $membership_card_id
     * @param array $receive_value
     * @param array $input_value_old
     * @return bool
     */
    public function updateGoodsMembershipCard($membership_card_id = 0, $receive_value = [], $input_value_old = [])
    {
        if (empty($membership_card_id) || empty($receive_value)) {
            return false;
        }

        // 获取新goods_id
        $goods_arr = [];
        foreach ($receive_value as $k => $value) {
            if ($value['type'] == 'goods' && !empty($value['value'])) {
                $goods_arr = is_string($value['value']) ? explode(',', $value['value']) : $value['value'];
            }
        }

        // 获取原goods_id
        $goods_arr_diff = [];
        $goods_id_old = $input_value_old['goods'] ?? '';
        if (!empty($goods_id_old)) {
            $goods_arr_old = is_string($goods_id_old) ? explode(',', $goods_id_old) : $goods_id_old;
        }
        if (!empty($goods_arr_old)) {
            // 比较原goods_id 数组与新goods_id数组 得出差集 结果为 取消权益卡的goods_id数组
            $diff = collect($goods_arr_old)->diff($goods_arr);
            $goods_arr_diff = $diff->values()->all();
        }

        if (!empty($goods_arr)) {
            // 绑定权益卡
            $data = [
                'is_on_sale' => 0, // 下架商品
                'membership_card_id' => $membership_card_id
            ];
            Goods::whereIn('goods_id', $goods_arr)->update($data);
        }

        if (!empty($goods_arr_diff)) {
            // 取消权益卡
            $data = [
                'is_on_sale' => 1, // 上架商品
                'membership_card_id' => 0
            ];
            Goods::whereIn('goods_id', $goods_arr_diff)->update($data);
        }

        return true;
    }

    /**
     * 指定购买商品绑定(删除)会员权益卡
     * @param int $membership_card_id
     * @return bool
     */
    public function removeGoodsMembershipCard($membership_card_id = 0)
    {
        if (empty($membership_card_id)) {
            return false;
        }

        $data = [
            'is_on_sale' => 1, // 上架商品
            'membership_card_id' => 0
        ];
        return Goods::where('membership_card_id', $membership_card_id)->update($data);
    }

    /**
     * 商品信息
     * @param string $goods_id_string
     * @return array
     */
    public function getGoods($goods_id_string = '')
    {
        if (empty($goods_id_string)) {
            return [];
        }

        $goods_id_arr = is_string($goods_id_string) ? explode(',', $goods_id_string) : $goods_id_string;

        $model = Goods::query()
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0);

        if (config('shop.review_goods') == 1) {
            $model = $model->whereIn('review_status', [3, 4, 5]);
        }

        $model = $model->whereIn('goods_id', $goods_id_arr);

        $model = $model->select('goods_id', 'goods_name', 'goods_thumb', 'shop_price')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('goods_id', 'DESC');

        $list = BaseRepository::getToArrayGet($model);

        if ($list) {
            foreach ($list as $k => $value) {
                $list[$k]['goods_thumb'] = empty($value['goods_thumb']) ? '' : $this->dscRepository->getImagePath($value['goods_thumb']);
                $list[$k]['shop_price_format'] = empty($value['shop_price']) ? '' : $this->dscRepository->getPriceFormat($value['shop_price'], true);
            }
        }

        return $list;
    }

    /**
     * 添加特殊会员等级
     * @param string $name
     * @return bool
     */
    public function addUserRank($name = '')
    {
        if (empty($name)) {
            return false;
        }

        //检查等级名称是否重复
        $count = $this->checkRankName($name);
        if ($count > 0) {
            return false;
        }

        $data = [
            'rank_name' => $name,
            'discount' => 100,
            'show_price' => 1,
            'special_rank' => 1
        ];

        $rank_id = UserRank::insertGetId($data);

        return $rank_id;
    }

    /**
     * 会员权益卡对应的会员等级
     * @param int $id
     * @return int
     */
    public function getCardRankId($id = 0)
    {
        if (empty($id)) {
            return 0;
        }

        return UserMembershipCard::where('id', $id)->value('user_rank_id');
    }

    /**
     * 删除特殊会员等级
     * @param int $rank_id
     * @return bool
     */
    public function deleteUserRank($rank_id = 0)
    {
        if (empty($rank_id)) {
            return false;
        }

        $res = UserRank::where('rank_id', $rank_id)->delete();

        $this->updateUsersRank($rank_id);

        return $res;
    }

    /**
     * 移除所有会员对应的特殊等级
     * @param int $rank_id
     * @return bool
     */
    public function updateUsersRank($rank_id = 0)
    {
        if (empty($rank_id)) {
            return false;
        }

        return Users::where('user_rank', $rank_id)->update(['user_rank' => 0]);
    }

    public function updateUserRankByRightId($rights_id, $data)
    {
        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'user_rank');

        $card_id = UserMembershipCardRights::where('id', $rights_id)->value('membership_card_id');
        $rank_id = $this->getCardRankId($card_id);
        return UserRank::where('rank_id', $rank_id)->update($data);
    }

    public function updateUserRankByCardId($card_id, $data)
    {
        // 过滤表字段
        $data = BaseRepository::getArrayfilterTable($data, 'user_rank');

        $rank_id = UserMembershipCard::where('id', $card_id)->value('user_rank_id');
        return UserRank::where('rank_id', $rank_id)->update($data);
    }

    /**
     * 检查名称是否重复
     * @param string $name
     * @return mixed
     */
    public function checkRankName($name = '')
    {
        $model = UserRank::where('rank_name', $name);

        $count = $model->count();

        return $count;
    }

    /**
     * 检查是否能 删除会员权益卡  已关联会员的卡不可删除  （含未付款）
     * @param int $id
     * @return bool
     */
    public function checkRightsCard($id = 0)
    {
        if (empty($id)) {
            return false;
        }

        // 分销商
        $count = DrpShop::where('membership_card_id', $id)->count();

        // 购买权益卡指定商品 未付款订单
        $model = OrderGoods::where('membership_card_id', $id);
        $model = CommonRepository::constantMaxId($model, 'user_id');
        $model = $model->whereHasIn('getOrder', function ($query) {
            $query->where('pay_status', PS_UNPAYED);
        });
        $order_count = $model->count('goods_id');

        if (empty($count) && empty($order_count)) {
            return true;
        }

        return false;
    }

    /**
     * 检查会员等级是否绑定关联权益卡
     * @param int $rank_id
     * @return bool
     */
    public function checkCard($rank_id = 0)
    {
        if (empty($rank_id)) {
            return false;
        }

        // 关联权益卡是否绑定等级 且能否删除
        $card = UserMembershipCard::where('user_rank_id', $rank_id)->first();

        if (empty($card)) {
            return true;
        }

        if (!empty($card)) {
            // 检查是否能 删除会员权益卡
            $can_delete = $this->checkRightsCard($card->id);
            if ($can_delete == false) {
                return false;
            }

            // 删除权益卡
            $res = $this->deleteRightsCard($card->id);
            if ($res) {
                //解除会员权益绑定
                $this->deleteCardRightsByCardId($card->id);
            }

            return true;
        }

        return false;
    }


    /**
     * 修改关联会员特价的权益卡（未设置价格 使用默认配置）的折扣价
     * @param int $rights_id
     * @param int $discount
     * @return bool
     */
    public function updateDefaultRightDiscount($rights_id = 0, $discount = 100)
    {
        if (empty($rights_id)) {
            return false;
        }

        $card_rights = UserMembershipCardRights::where('rights_id', $rights_id)->get();
        $card_rights = $card_rights ? $card_rights->toArray() : [];

        if ($card_rights) {
            $card_ids = [];
            foreach ($card_rights as $row) {
                if (empty($row['rights_configure'])) {
                    $card_ids[] = $row['membership_card_id'];
                }
            }

            if ($card_ids) {
                $user_rank_ids = UserMembershipCard::whereIn('id', $card_ids)->select('user_rank_id')->get()->keyBy('user_rank_id')->keys()->all();
                if ($user_rank_ids) {
                    return UserRank::whereIn('rank_id', $user_rank_ids)->update(['discount' => $discount]);
                }
            }
        }
        return true;
    }
}
