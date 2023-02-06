<?php

namespace App\Repositories\Order;

use App\Models\Comment;
use App\Models\CommentImg;
use App\Models\Goods;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Support\Carbon;

/**
 * 订单商品评价
 * Class OrderCommentRepository
 * @package App\Repositories\Order
 */
class OrderCommentRepository
{
    /**
     * orderCommentModel
     * @param int $user_id
     * @param int $is_comment
     * @param int $order_id
     * @return mixed
     */
    protected static function orderCommentModel($user_id = 0, $is_comment = 0, $order_id = 0)
    {
        // 订单商品 is_received 是否退货退款：0 否, 1 是
        $model = OrderGoods::where('order_id', '>', 0)->where('main_count', 0)->where('user_id', $user_id)->where('is_received', 0);

        // is_comment 0 未评价 1 已评价/追评
        if ($is_comment == 1) {
            $model = $model->whereIn('is_comment', [1, 2]);
        } else {
            $model = $model->where('is_comment', 0);
        }

        if ($order_id) {
            $model = $model->where('order_id', $order_id);
        }

        // 订单已确认收货
        $model = $model->whereHasIn('getOrder', function ($query) use ($user_id, $order_id) {
            $query = $query->where('user_id', $user_id)->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED])->whereIn('pay_status', [PS_PAYED, PS_PAYING])->where('shipping_status', SS_RECEIVED)->where('main_count', 0)->where('confirm_take_time', '>', 0);
            if ($order_id) {
                $query->where('order_id', $order_id);
            }
        })->whereHasIn('getSellerShopInfo', function ($query) {
            $query->where('shop_can_comment', 1);
        });

        return $model;
    }

    /**
     * 用户订单评论
     * @param int $user_id
     * @param int $is_comment 0 未评价 1 已评价/追评
     * @param int $order_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public static function getOrderComment($user_id = 0, $is_comment = 0, $order_id = 0, $page = 1, $size = 10)
    {
        if (empty($user_id)) {
            return [];
        }

        $model = self::orderCommentModel($user_id, $is_comment, $order_id);

        $offset = [
            'start' => ($page - 1) * $size,
            'limit' => $size
        ];
        $model = $model->offset($offset['start'])->limit($offset['limit']);

        $list = $model->select('rec_id', 'order_id', 'user_id', 'goods_id', 'ru_id', 'goods_name', 'goods_price', 'goods_number', 'goods_attr', 'is_comment')
            ->orderBy('rec_id', 'DESC')
            ->get();

        $list = $list ? $list->toArray() : [];

        return $list;
    }

    /**
     * 用户订单评论数量
     * @param int $user_id
     * @param int $is_comment 0 未评价 1 已评价/追评
     * @param int $order_id
     * @return int
     */
    public static function getOrderCommentCount($user_id = 0, $is_comment = 0, $order_id = 0)
    {
        if (empty($user_id)) {
            return 0;
        }

        $model = self::orderCommentModel($user_id, $is_comment, $order_id);

        return $model->count() ?? 0;
    }

    /**
     * 获取商品信息
     *
     * @param array $goods_id
     * @param array $columns
     * @return array
     */
    public static function getGoodsInfo($goods_id = [], $columns = ['*'])
    {
        if (empty($goods_id)) {
            return [];
        }

        $goods_id = BaseRepository::getExplode($goods_id);

        $model = Goods::select($columns)->whereIn('goods_id', $goods_id);

        $list = BaseRepository::getToArrayGet($model);

        // 返回 以goods_id 为键值数组
        return collect($list)->mapWithKeys(function ($item) {
            return [$item['goods_id'] => $item];
        })->toArray();
    }

    /**
     * 订单信息
     *
     * @param array $order_id
     * @param array $columns
     * @return array
     */
    public static function getOrder($order_id = [], $columns = ['*'])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = BaseRepository::getExplode($order_id);

        $model = OrderInfo::select($columns)->whereIn('order_id', $order_id);

        $list = BaseRepository::getToArrayGet($model);

        // 返回 以order_id 为键值数组
        return collect($list)->mapWithKeys(function ($item) {
            return [$item['order_id'] => $item];
        })->toArray();
    }

    /**
     * 订单商品评价列表
     *
     * @param int $goods_id
     * @param int $rec_id
     * @param int $user_id
     * @param array $columns
     * @return array
     */
    public static function commentList($goods_id = 0, $rec_id = 0, $user_id = 0, $columns = ['*'])
    {
        if (empty($goods_id) || empty($rec_id) || empty($user_id)) {
            return [];
        }

        $model = Comment::where('comment_type', 0)
            ->where('id_value', $goods_id)
            ->where('rec_id', $rec_id)
            ->where('parent_id', 0)
            ->where('user_id', $user_id);

        $list = $model->select($columns)->orderBy('comment_id', 'ASC')
            ->get();

        return $list ? $list->toArray() : [];
    }

    /**
     * 评价图片列表
     *
     * @param int $goods_id
     * @param int $comment_id
     * @param array $columns
     * @return array
     */
    public static function commentImgList($goods_id = 0, $comment_id = 0, $columns = ['*'])
    {
        if (empty($goods_id) || empty($comment_id)) {
            return [];
        }

        $model = CommentImg::where('comment_id', $comment_id)->where('goods_id', $goods_id);

        $list = $model->select($columns)->orderBy('id', 'DESC')
            ->get();

        return $list ? $list->toArray() : [];
    }

    /**
     * 首次评价信息
     * @param int $goods_id
     * @param int $rec_id
     * @param int $user_id
     * @param array $columns
     * @return array
     */
    public static function commentFirst($goods_id = 0, $rec_id = 0, $user_id = 0, $columns = ['*'])
    {
        if (empty($goods_id) || empty($rec_id) || empty($user_id)) {
            return [];
        }

        $model = Comment::where('comment_type', 0)
            ->where('id_value', $goods_id)
            ->where('rec_id', $rec_id)
            ->where('parent_id', 0)
            ->where('user_id', $user_id)
            ->where('add_comment_id', 0);

        $list = $model->select($columns)->orderBy('comment_id', 'ASC')
            ->first();

        return $list ? $list->toArray() : [];
    }

    /**
     * 获取商品评价标签数量
     * @param int $goods_id
     * @param string $tag_name
     * @return int
     */
    public static function commentGoodsTagNum($goods_id = 0, $tag_name = '')
    {
        if (empty($tag_name)) {
            return 0;
        }

        $tag_name = !empty($tag_name) ? trim($tag_name) : '';

        $res = Comment::where('id_value', $goods_id);
        $res = BaseRepository::getToArrayGet($res);

        $str = BaseRepository::getKeyPluck($res, 'goods_tag');
        $str = BaseRepository::getImplode($str);

        if ($str) {
            return substr_count($str, $tag_name);
        }

        return 0;
    }

    /**
     * 计算2个日期差 返回 格式化时间
     * @param int $timestamp
     * @param int $timestamp2
     * @return string
     */
    public static function commentTimeForHumans($timestamp = 0, $timestamp2 = 0)
    {
        $timestring = TimeRepository::getLocalDate('Y-m-d H:i:s', $timestamp);
        $timestring2 = TimeRepository::getLocalDate('Y-m-d H:i:s', $timestamp2);

        $date1 = Carbon::parse($timestring);
        $date2 = Carbon::parse($timestring2);

        // 时间差 1天内
        if (abs($timestamp2 - $timestamp) < 24 * 60 * 60) {
            // 1小时内
            if (abs($timestamp2 - $timestamp) < 1 * 60 * 60) {
                // 1分钟内
                if (abs($timestamp2 - $timestamp) < 1 * 60) {
                    // 几秒钟后
                    $diff = $date1->diffInSeconds($date2);
                    $diff = trans('comment.after_seconds', ['seconds' => $diff]);
                } else {
                    // 几分钟后
                    $diff = $date1->diffInMinutes($date2);
                    $diff = trans('comment.after_minutes', ['minutes' => $diff]);
                }
            } else {
                // 几小时后
                $diff = $date1->diffInHours($date2);
                $diff = trans('comment.after_hours', ['hours' => $diff]);
            }
        } else {
            // 几天后
            $diff = $date1->diffInDays($date2);
            $diff = trans('comment.after_days', ['days' => $diff]);
        }

        return $diff;
    }

}