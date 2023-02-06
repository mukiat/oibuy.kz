<?php

namespace App\Services\Comment;

use App\Exceptions\HttpException;
use App\Models\OrderGoods;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderCommentRepository;
use App\Repositories\Seller\SellerShopinfoRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * 订单商品评价
 *
 * Class OrderCommentService
 * @package App\Services\Comment
 */
class OrderCommentService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 用户订单商品评价列表
     *
     * @param int $user_id
     * @param int $is_comment 0:待评论 1:已评论/待追评
     * @param int $order_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getOrderComment($user_id = 0, $is_comment = 0, $order_id = 0, $page = 1, $size = 10)
    {
        $list = OrderCommentRepository::getOrderComment($user_id, $is_comment, $order_id, $page, $size);

        if (!empty($list)) {
            $goods_ids = collect($list)->pluck('goods_id')->toArray();
            $goodsInfo = OrderCommentRepository::getGoodsInfo($goods_ids, ['goods_id', 'goods_thumb']);

            foreach ($list as $key => $item) {
                $list[$key]['shop_name'] = SellerShopinfoRepository::getShopName($item['ru_id']);

                $item['goods_thumb'] = $goodsInfo[$item['goods_id']]['goods_thumb'] ?? '';
                $list[$key]['goods_thumb'] = $this->dscRepository->getImagePath($item['goods_thumb']);
                $list[$key]['goods_price_formated'] = $this->dscRepository->getPriceFormat($item['goods_price']);
                $list[$key]['goods_attr'] = $item['goods_attr'] ?? '';
                $list[$key]['goods_attr'] = str_replace(["\r\n", "\r", "\n"], '', $list[$key]['goods_attr']);

                if ($is_comment == 0) {
                    // 是否可评价
                    $list[$key]['can_evaluate'] = (int)config('shop.shop_can_comment', 0);
                } elseif ($is_comment == 1 || $is_comment == 2) {
                    // 已评价、追评内容与图片
                    $comment = $this->commentList($item['goods_id'], $item['rec_id'], $item['user_id']);

                    $list[$key]['comment'] = $comment;
                    $list[$key]['comment_rank'] = $comment['0']['comment_rank'] ?? 5; // 商品星级评分

                    /**
                     * 是否可追评条件：开启追评，且在订单收货完成后可追评时间内
                     */
                    $can_add_evaluate = 0;
                    if (count($comment) == 1 && $item['is_comment'] == 1) {
                        $add_evaluate = (int)config('shop.add_evaluate', 0);
                        if ($add_evaluate == 1) {
                            $add_evaluate_time = (int)config('shop.add_evaluate_time', 0);
                            $now = TimeRepository::getGmTime();
                            $confirm_take_time = DB::table('order_info')->where('order_id', $item['order_id'])->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('pay_status', PS_PAYED)->where('shipping_status', SS_RECEIVED)->value('confirm_take_time');

                            // 当前时间小于等于订单确认收货时间+可追评时间 now <= confirm_take_time + add_evaluate_time;
                            if ($confirm_take_time > 0 && $confirm_take_time >= ($now - $add_evaluate_time * 24 * 60 * 60)) {
                                $can_add_evaluate = 1;
                            }
                        }
                    }

                    $list[$key]['can_add_evaluate'] = $can_add_evaluate;
                }
            }
        }

        return $list;
    }

    /**
     * 订单商品评价数量
     * @param int $user_id
     * @param int $is_comment 0 待评价 1 已评价
     * @param int $order_id
     * @return int
     */
    public function getOrderCommentCount($user_id = 0, $is_comment = 0, $order_id = 0)
    {
        return OrderCommentRepository::getOrderCommentCount($user_id, $is_comment, $order_id);
    }

    /**
     * 处理返回格式化评价内容与图片列表
     *
     * @param int $goods_id
     * @param int $rec_id
     * @param int $user_id
     * @return array
     */
    public function commentList($goods_id = 0, $rec_id = 0, $user_id = 0)
    {
        $comment = OrderCommentRepository::commentList($goods_id, $rec_id, $user_id, ['comment_id', 'user_name', 'content', 'comment_rank', 'add_comment_id']);

        if (empty($comment)) {
            return [];
        }

        return collect($comment)->map(function ($item, $key) use ($goods_id) {
            $item['content'] = html_out($item['content']);
            $item['comment_img_list'] = $this->getCommentImgList($goods_id, $item['comment_id']);
            // 是否追评 is_add_evaluate 0 否 1 是
            $item['is_add_evaluate'] = $item['add_comment_id'] > 0 ? 1 : 0;
            return $item;
        })->toArray();
    }

    /**
     * 评价图片列表
     *
     * @param int $goods_id
     * @param int $comment_id
     * @return array
     */
    public function getCommentImgList($goods_id = 0, $comment_id = 0)
    {
        $list = OrderCommentRepository::commentImgList($goods_id, $comment_id, ['comment_img', 'img_thumb']);

        if (empty($list)) {
            return [];
        }

        return collect($list)->map(function ($item) {
            $item['comment_img'] = $this->dscRepository->getImagePath($item['comment_img']);
            $item['img_thumb'] = $this->dscRepository->getImagePath($item['img_thumb']);
            return $item;
        })->toArray();
    }

    /**
     * 首次评价
     * @param int $user_id
     * @param array $cmt
     * @param array $cmt_img_list
     * @return mixed
     * @throws HttpException
     */
    public function evaluateOrderGoods($user_id = 0, $cmt = [], $cmt_img_list = [])
    {
        if (empty($user_id) || empty($cmt)) {
            throw new HttpException(trans('user.js_languages.parameter_error'), 1);
        }

        // 是否开启评价
        $can_evaluate = (int)config('shop.shop_can_comment', 0);
        if (empty($can_evaluate)) {
            throw new HttpException(trans('comment.shop_can_comment_closed'), 1);
        }

        if (count($cmt_img_list) > 9) {
            throw new HttpException(trans('comment.comment_img_number'), 1);
        }

        $rec_id = $cmt['rec_id'] ?? 0;
        if (empty($rec_id)) {
            throw new HttpException(trans('user.js_languages.parameter_error'), 1);
        }

        $goods_id = $cmt['id'] ?? 0;
        $order_id = $cmt['order_id'] ?? 0;
        $goods_tag = $cmt['tag'] ?? ''; // 商品评价标签
        $goods_tag = is_array($goods_tag) ? implode(',', $goods_tag) : $goods_tag;
        $cmt_content = e($cmt['content'] ?? '');
        if (empty($cmt_content)) {
            throw new HttpException(trans('user.comm_content'), 1); // 评论内容不能为空
        }

        $count = DB::table('comment')->where('rec_id', $rec_id)->where('user_id', $user_id)->count();
        if ($count >= 1) {
            throw new HttpException(trans('user.comm_error') . ',' . trans('comment.been_evaluated'), 1);// 同一用户、同一订单商品重复评论（已首次评价）
        }

        /* 评论是否需要审核 */
        $status = 1 - (int)config('shop.comment_check', 0);

        $model = OrderGoods::where('rec_id', $rec_id)->where('goods_id', $goods_id)->where('main_count', 0)
            ->where('is_received', 0);

        $model = $model->where('is_comment', 0)->first();

        $order_goods = $model ? $model->toArray() : [];

        if (empty($order_goods)) {
            throw new HttpException(trans('comment.order_goods_not_exist'), 1);
        }

        if ($user_id > 0 && $order_goods['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        $ru_id = $order_goods['ru_id'] ?? 0;

        $user = DB::table('users')->where('user_id', $user_id)->select('user_name', 'email')->first();
        $user_name = $user->user_name ?? '';
        $email = $user->email ?? '';

        $now = TimeRepository::getGmTime();

        /* 保存评论内容 */
        $values = [
            'comment_type' => $cmt['type'],
            'id_value' => $goods_id,
            'email' => $email,
            'user_name' => $user_name,
            'content' => $cmt_content,
            'comment_rank' => $cmt['rank'],
            'comment_server' => $cmt['server'] ?? 5,
            'comment_delivery' => $cmt['delivery'] ?? 5,
            'add_time' => $now,
            'ip_address' => request()->getClientIp(),
            'status' => $status,
            'parent_id' => 0,
            'user_id' => $user_id,
            'ru_id' => $ru_id,
            'order_id' => $order_id,
            'rec_id' => $rec_id,
            'goods_tag' => $goods_tag,
        ];

        $comment_id = DB::table('comment')->insertGetId($values);

        if ($comment_id > 0) {
            DB::table('order_goods')->where('rec_id', $rec_id)->where('user_id', $user_id)->update(['is_comment' => 1]);

            // 首次评价

            /* 保存评论图片 */
            if ($cmt_img_list) {
                // oss图片处理
                $cmt_img_list = $this->dscRepository->transformOssFile($cmt_img_list);
                foreach ($cmt_img_list as $k => $v) {
                    $other = [
                        'user_id' => $user_id,
                        'order_id' => $order_id,
                        'rec_id' => $rec_id,
                        'goods_id' => $goods_id,
                        'comment_id' => $comment_id,
                        'comment_img' => $v,
                        'img_thumb' => $v,
                        'cont_desc' => ''
                    ];
                    DB::table('comment_img')->insert($other);
                }
            }

            // 商家满意度评价
            $desc_rank = $cmt['desc_rank'] ?? '';
            $service_rank = $cmt['service_rank'] ?? '';
            $delivery_rank = $cmt['delivery_rank'] ?? '';
            $sender_rank = $cmt['sender_rank'] ?? '';
            if ($ru_id > 0 && $desc_rank && $service_rank && $delivery_rank && $sender_rank) {
                $other = [
                    'user_id' => $user_id,
                    'ru_id' => $ru_id,
                    'order_id' => $order_id,
                    'desc_rank' => $desc_rank,
                    'service_rank' => $service_rank,
                    'delivery_rank' => $delivery_rank,
                    'sender_rank' => $sender_rank,
                    'add_time' => $now
                ];
                $comment_seller_id = DB::table('comment_seller')->insertGetId($other);
                if ($comment_seller_id) {
                    //插入店铺评分
                    $store_score = sprintf("%.2f", ($desc_rank + $service_rank + $delivery_rank) / 3);
                    DB::table('merchants_shop_information')->where('user_id', $ru_id)->increment('store_score', $store_score);

                    // 更新商家商品评价权重
                    $goods_ids = DB::table('order_goods')->where('order_id', $order_id)->pluck('goods_id')->toArray();
                    if ($goods_ids) {
                        // 获取对商家评论的数量
                        $comment_seller_num = DB::table('comment_seller')->where('order_id', $order_id)->count('sid');
                        foreach ($goods_ids as $goods_id_value) {
                            update_comment_seller($goods_id_value, ['goods_id' => $goods_id_value, 'merchants_comment_number' => $comment_seller_num]);
                        }
                    }
                }
            }

            /**
             * 追评不计权重  一个评价仅计算一次权重
             */
            // 更新商品评价权重
            $weight = DB::table('intelligent_weight')->where('goods_id', $goods_id)->count();
            if ($weight) {
                DB::table('intelligent_weight')->where('goods_id', $goods_id)->increment('goods_comment_number', 1);
            } else {
                DB::table('intelligent_weight')->insertGetId(['goods_id' => $goods_id, 'goods_comment_number' => 1]);
            }
            update_goods_weights($goods_id); // 更新权重值

            // 更新订单统计
            Artisan::call('app:user:order', ['user_id' => $user_id]);

            // 发布成功或等待审核
            $msg = $status ? trans('comment.Add_success') : trans('comment.add_success_wait');
            return ['error' => 0, 'comment_id' => $comment_id, 'msg' => $msg];
        }

        throw new HttpException(trans('user.comm_error'), 1);
    }

    /**
     * 追加评价
     * @param int $user_id
     * @param array $cmt
     * @param array $cmt_img_list
     * @return array
     * @throws HttpException
     */
    public function addEvaluateOrderGoods($user_id = 0, $cmt = [], $cmt_img_list = [])
    {
        if (empty($user_id) || empty($cmt)) {
            throw new HttpException(trans('user.js_languages.parameter_error'), 1);
        }

        // 是否开启追评
        $add_evaluate = (int)config('shop.add_evaluate', 0);
        if (empty($add_evaluate)) {
            throw new HttpException(trans('comment.add_evaluate_closed'), 1);
        }

        if (count($cmt_img_list) > 9) {
            throw new HttpException(trans('comment.comment_img_number'), 1);
        }

        $rec_id = $cmt['rec_id'] ?? 0;
        $add_comment_id = $cmt['comment_id'] ?? 0;
        if (empty($rec_id) || empty($add_comment_id)) {
            throw new HttpException(trans('user.js_languages.parameter_error'), 1);
        }

        $goods_id = $cmt['id'] ?? 0;
        $order_id = $cmt['order_id'] ?? 0;
        $goods_tag = $cmt['tag'] ?? ''; // 商品评价标签
        $goods_tag = is_array($goods_tag) ? implode(',', $goods_tag) : $goods_tag;
        $cmt_content = e($cmt['content'] ?? '');
        if (empty($cmt_content)) {
            throw new HttpException(trans('user.comm_content'), 1); // 评论内容不能为空
        }

        $count = DB::table('comment')->where('rec_id', $rec_id)->where('user_id', $user_id)->count();
        if ($count >= 2) {
            throw new HttpException(trans('user.comm_error') . ',' . trans('comment.been_evaluated'), 1);// 同一用户、同一订单商品重复评论（已追加评价）
        }

        /* 评论是否需要审核 */
        $status = 1 - (int)config('shop.comment_check', 0);

        $model = OrderGoods::where('rec_id', $rec_id)->where('goods_id', $goods_id)->where('main_count', 0)
            ->where('is_received', 0);

        $model = $model->with([
            'getOrder' => function ($query) {
                $query->select('order_id', 'confirm_take_time')->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->where('pay_status', PS_PAYED)->where('shipping_status', SS_RECEIVED);
            }
        ]);

        $model = $model->where('is_comment', 1)->first();

        $order_goods = $model ? $model->toArray() : [];

        if (empty($order_goods)) {
            throw new HttpException(trans('comment.order_goods_not_exist'), 1);
        }

        if ($user_id > 0 && $order_goods['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        // 是否在可追评时间内
        $add_evaluate_time = (int)config('shop.add_evaluate_time', 0);
        $now = TimeRepository::getGmTime();
        $confirm_take_time = $order_goods['get_order']['confirm_take_time'] ?? 0;

        $can_add_evaluate = 0;
        // 当前时间小于等于订单确认收货时间+可追评时间 now <= confirm_take_time + add_evaluate_time;
        if ($confirm_take_time > 0 && $confirm_take_time >= ($now - $add_evaluate_time * 24 * 60 * 60)) {
            $can_add_evaluate = 1;
        }

        if (empty($can_add_evaluate)) {
            throw new HttpException(trans('comment.not_in_add_evaluate_time'), 1);
        }

        $ru_id = $order_goods['ru_id'] ?? 0;

        $user = DB::table('users')->where('user_id', $user_id)->select('user_name', 'email')->first();
        $user_name = $user->user_name ?? '';
        $email = $user->email ?? '';

        $now = TimeRepository::getGmTime();

        /* 保存评论内容 */
        $values = [
            'comment_type' => $cmt['type'],
            'id_value' => $goods_id,
            'email' => $email,
            'user_name' => $user_name,
            'content' => $cmt_content,
            'comment_rank' => $cmt['rank'],
            'comment_server' => $cmt['server'] ?? 5,
            'comment_delivery' => $cmt['delivery'] ?? 5,
            'add_time' => $now,
            'ip_address' => request()->getClientIp(),
            'status' => $status,
            'parent_id' => 0,
            'user_id' => $user_id,
            'ru_id' => $ru_id,
            'order_id' => $order_id,
            'rec_id' => $rec_id,
            'goods_tag' => $goods_tag,
            'add_comment_id' => $add_comment_id
        ];

        $comment_id = DB::table('comment')->insertGetId($values);

        if ($comment_id > 0) {
            DB::table('order_goods')->where('rec_id', $rec_id)->where('user_id', $user_id)->update(['is_comment' => 2]);

            /* 保存评论图片 */
            if ($cmt_img_list) {
                // oss图片处理
                $cmt_img_list = $this->dscRepository->transformOssFile($cmt_img_list);
                foreach ($cmt_img_list as $k => $v) {
                    $other = [
                        'user_id' => $user_id,
                        'order_id' => $order_id,
                        'rec_id' => $rec_id,
                        'goods_id' => $goods_id,
                        'comment_id' => $comment_id,
                        'comment_img' => $v,
                        'img_thumb' => $v,
                        'cont_desc' => ''
                    ];
                    DB::table('comment_img')->insert($other);
                }
            }

            /**
             * 追评不计权重
             */

            // 更新订单统计
            Artisan::call('app:user:order', ['user_id' => $user_id]);

            // 发布成功或等待审核
            $msg = $status ? trans('comment.Add_success') : trans('comment.add_success_wait');
            return ['error' => 0, 'comment_id' => $comment_id, 'msg' => $msg];
        }

        throw new HttpException(trans('user.comm_error'), 1);
    }

}