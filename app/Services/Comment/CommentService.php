<?php

namespace App\Services\Comment;

use App\Exceptions\HttpException;
use App\Libraries\Pager;
use App\Models\Comment;
use App\Models\CommentBaseline;
use App\Models\CommentImg;
use App\Models\CommentSeller;
use App\Models\Goods;
use App\Models\MerchantsShopInformation;
use App\Models\OrderGoods;
use App\Models\Users;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Order\OrderCommentRepository;
use App\Services\Goods\GoodsCommentService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderGoodsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 商城评论
 * Class Comment
 * @package App\Services
 */
class CommentService
{
    protected $dscRepository;
    protected $orderGoodsService;
    protected $goodsCommentService;
    protected $merchantCommonService;

    public function __construct(
        DscRepository $dscRepository,
        OrderGoodsService $orderGoodsService,
        GoodsCommentService $goodsCommentService,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->orderGoodsService = $orderGoodsService;
        $this->goodsCommentService = $goodsCommentService;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 获取评论图片
     *
     * @param array $where
     * @param array $order
     * @return mixed
     */
    public function getCommentImgList($where = [], $order = ['sort' => 'id', 'order' => 'desc'])
    {
        $res = CommentImg::whereRaw(1);

        if (isset($where['user_id'])) {
            $res = $res->where('user_id', $where['user_id']);
        }

        if (isset($where['order_id'])) {
            $res = $res->where('order_id', $where['order_id']);
        }

        if (isset($where['rec_id'])) {
            $res = $res->where('rec_id', $where['rec_id']);
        }

        if (isset($where['goods_id'])) {
            $res = $res->where('goods_id', $where['goods_id']);
        }

        if (isset($where['comment_id'])) {
            $res = $res->where('comment_id', $where['comment_id']);
        }

        $res = $res->orderBy($order['sort'], $order['order']);

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        if ($res) {
            foreach ($res as $key => $row) {
                $row['comment_img'] = $this->dscRepository->getImagePath($row['comment_img']);
                $row['img_thumb'] = $this->dscRepository->getImagePath($row['img_thumb']);

                $res[$key] = $row;
            }
        }

        return $res;
    }

    /**
     *  获取评论内容信息
     *
     * @access  public
     * @param int $comment_id
     * @return  array
     */
    public function getCommentInfo($comment_id = 0)
    {
        $res = Comment::where('comment_id', $comment_id)->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 获取商品评论数量 (不含追评)
     * @param $goods_id
     * @return mixed
     */
    public function goodsCommentCount($goods_id)
    {
        $model = Comment::where('id_value', $goods_id)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->where('add_comment_id', 0);

        $list['all'] = $this->commentNumberFormat($model->whereIn('comment_rank', [0, 1, 2, 3, 4, 5])->count());// 全部评价

        $list['good'] = $this->commentNumberFormat($model->whereIn('comment_rank', [4, 5])->count());//好评

        $list['in'] = $this->commentNumberFormat($model->whereIn('comment_rank', [2, 3])->count());//中评

        $list['rotten'] = $this->commentNumberFormat($model->whereIn('comment_rank', [0, 1])->count());//差评

        // 有图评价
        $list['img'] = $this->commentNumberFormat(Comment::where('id_value', $goods_id)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->where('add_comment_id', 0)
            ->whereIn('comment_rank', [0, 1, 2, 3, 4, 5])
            ->whereHasIn('getCommentImg', function ($query) {
                $query->where('comment_img', '<>', '');
            })->count());

        // 商品评论标签
        $comment_tag = Goods::where('goods_id', $goods_id)->value('goods_product_tag');

        if ($comment_tag) {
            $comment_tag = BaseRepository::getExplode($comment_tag);

            $list['comment'] = collect($comment_tag)->map(function ($item, $key) use ($goods_id) {
                return [
                    'tag_name' => $item,
                    'count' => $this->commentNumberFormat(Comment::where('id_value', $goods_id)->whereRaw("FIND_IN_SET('" . $item . "', goods_tag)")->count()),
                ];
            })->all();
        }

        return $list;
    }

    /**
     * 商品评论
     *
     * @param int $uid
     * @param $goods_id
     * @param string $rank
     * @param int $page
     * @param int $size
     * @param string $goods_tag
     * @return array
     */
    public function GoodsComment($uid = 0, $goods_id, $rank = '', $page = 1, $size = 10, $goods_tag = '')
    {
        $res = Comment::where('id_value', $goods_id)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->where('add_comment_id', 0);

        if ($uid > 0) {
            $res = $res->where('user_id', $uid);
        }

        // 关联会员
        $res = $res->with([
            'user' => function ($query) {
                $query->select('user_id', 'user_name', 'nick_name', 'user_picture');
            }
        ]);
        // 关联图片
        $res = $res->with([
            'getCommentImg' => function ($query) {
                $query->select('comment_id', 'id', 'comment_img');
            }
        ]);

        // 商品标签不为空时 把标签名称传过来
        if (!empty($goods_tag)) {
            $res = $res->whereRaw("FIND_IN_SET('" . $goods_tag . "', goods_tag)");
        }

        $rank = !empty($rank) ? $rank : 'all';

        if ($rank == 'all') {
            $res = $res->whereIn('comment_rank', [0, 1, 2, 3, 4, 5]);// 全部评价
        } elseif ($rank == 'good') {
            $res = $res->whereIn('comment_rank', [4, 5]);//好评
        } elseif ($rank == 'in') {
            $res = $res->whereIn('comment_rank', [2, 3]);//中评
        } elseif ($rank == 'rotten') {
            $res = $res->whereIn('comment_rank', [0, 1]);//差评
        } elseif ($rank == 'img') {
            $res = $res->whereHasIn('getCommentImg', function ($query) {
                $query->where('comment_img', '!=', '');
            });
            $res = $res->whereIn('comment_rank', [0, 1, 2, 3, 4, 5]);
        }

        $res = $res->orderBy('add_time', 'desc')
            ->offset(($page - 1) * $size)
            ->limit($size)
            ->get();
        $res = $res ? $res->toArray() : [];

        $commentlist = [];
        if ($res) {
            foreach ($res as $k => $v) {
                $commentlist[$k]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $v['add_time']);
                $commentlist[$k]['content'] = $v['content'];
                $commentlist[$k]['rank'] = $v['comment_rank'];
                $commentlist[$k]['user_name'] = $v['user']['nick_name'] ? $v['user']['nick_name'] : $v['user']['user_name'];
                $commentlist[$k]['user_name'] = setAnonymous($commentlist[$k]['user_name']);

                $commentlist[$k]['user_picture'] = !empty($v['user']['user_picture']) ? $v['user']['user_picture'] : $this->dscRepository->dscUrl('img/user_default.png');
                $commentlist[$k]['user_picture'] = $this->dscRepository->getImagePath($commentlist[$k]['user_picture']);

                // 订单商品信息
                $goods = OrderGoods::select('goods_attr', 'goods_id', 'goods_name')
                    ->where('rec_id', $v['rec_id'])
                    ->where('goods_id', $v['id_value']);
                $goods = BaseRepository::getToArrayFirst($goods);
                $commentlist[$k]['goods'] = $goods;
                $commentlist[$k]['goods_name'] = $goods['goods_name'] ?? '';
                $commentlist[$k]['goods_attr'] = $goods['goods_attr'] ?? '';

                // 回复评论
                $re = Comment::select('user_name', 'content', 'add_time')
                    ->where('parent_id', $v['comment_id'])
                    ->first();
                $re = $re ? $re->toArray() : [];
                if ($re) {
                    $commentlist[$k]['re_content'] = $re['content'];
                    $commentlist[$k]['re_username'] = $re['user_name'];
                    $commentlist[$k]['re_add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $re['add_time']);
                }

                // 处理图片
                $commentlist[$k]['comment_img'] = '';
                if (isset($v['get_comment_img']) && $v['get_comment_img']) {
                    $img = [];
                    foreach ($v['get_comment_img'] as $key => $val) {
                        $img[$key] = $this->dscRepository->getImagePath($val['comment_img']);
                    }
                    $commentlist[$k]['comment_img'] = $img;
                } else {
                    if ($rank == 'img') {
                        unset($commentlist[$k]);
                    }
                }

                // 追评内容
                $add_comment = Comment::select('comment_id', 'user_name', 'content', 'add_time', 'order_id')
                    ->where('add_comment_id', $v['comment_id'])
                    ->with([
                        'getCommentImg' => function ($query) {
                            $query->select('comment_id', 'id', 'comment_img');
                        }
                    ])
                    ->first();
                $add_comment = $add_comment ? $add_comment->toArray() : [];
                if ($add_comment) {
                    $add_comment['content'] = html_out($add_comment['content']);
                    // 追评时间格式化  用户多少天后追评 发起追评时间 - 订单确认收货时间
                    $confirm_take_time = DB::table('order_info')->where('order_id', $add_comment['order_id'])->value('confirm_take_time');
                    $add_comment['add_time_humans'] = OrderCommentRepository::commentTimeForHumans($confirm_take_time, $add_comment['add_time']);

                    // 追评图片列表
                    $comment_img = [];
                    $add_comment['get_comment_img'] = $add_comment['get_comment_img'] ?? [];
                    if ($add_comment['get_comment_img']) {
                        foreach ($add_comment['get_comment_img'] as $i => $val) {
                            $comment_img[$i] = $this->dscRepository->getImagePath($val['comment_img']);
                        }
                    }
                    $add_comment['get_comment_img'] = $comment_img;
                }
                $commentlist[$k]['add_comment'] = $add_comment;
            }

            ksort($commentlist);
        }

        return $commentlist;
    }

    /**
     * 用户评价数量
     * @param int $user_id
     * @param int $is_comment 0：0:待评论 1:已评论/待追评
     * @param int $order_id
     * @return mixed
     */
    public function getUserOrderCommentCount($user_id = 0, $is_comment = 0, $order_id = 0)
    {
        return app(OrderCommentService::class)->getOrderCommentCount($user_id, $is_comment, $order_id);
    }

    /**
     * 用户评价列表
     *
     * @param int $user_id
     * @param int $is_comment 0:待评论 1:已评论/待追评
     * @param int $order_id
     * @param int $page
     * @param int $size
     * @return mixed
     * @throws \Exception
     */
    public function getUserOrderCommentList($user_id = 0, $is_comment = 0, $order_id = 0, $page = 1, $size = 10)
    {
        $data = app(OrderCommentService::class)->getOrderComment($user_id, $is_comment, $order_id, $page, $size);

        if ($data) {
            foreach ($data as $key => $value) {
                $data[$key]['goods_url'] = $this->dscRepository->buildUri('goods', ['gid' => $value['goods_id']]);
            }
        }

        return $data;
    }

    /**
     * 评论页商品
     * @param int $rec_id
     * @param int $user_id
     * @param int $is_add_evaluate // 0：首次评论 1：追加评论
     * @return array
     * @throws HttpException
     */
    public function getOrderGoods($rec_id = 0, $user_id = 0, $is_add_evaluate = 0)
    {
        if (empty($rec_id)) {
            throw new HttpException('parameters of illegal.', 1);
        }

        if ($is_add_evaluate == 1) {
            // 是否开启追评
            $add_evaluate = (int)config('shop.add_evaluate', 0);
            if (empty($add_evaluate)) {
                throw new HttpException(trans('comment.add_evaluate_closed'), 1);
            }
        } else {
            // 是否开启评价
            $can_evaluate = (int)config('shop.shop_can_comment', 0);
            if (empty($can_evaluate)) {
                throw new HttpException(trans('comment.shop_can_comment_closed'), 1);
            }
        }

        $model = OrderGoods::where('rec_id', $rec_id)->where('main_count', 0)->where('is_received', 0)
            ->with([
                'getGoods' => function ($query) {
                    $query->select('goods_id', 'shop_price', 'goods_thumb', 'goods_product_tag');
                }
            ]);

        if ($is_add_evaluate == 1) {
            $model = $model->where('is_comment', 1);
        } else {
            $model = $model->where('is_comment', 0);
        }

        $model = $model->select('rec_id', 'order_id', 'user_id', 'goods_id', 'ru_id');

        $order_goods = BaseRepository::getToArrayFirst($model);

        if (empty($order_goods)) {
            throw new HttpException(trans('comment.order_goods_not_exist'), 1);
        }

        if ($user_id > 0 && $order_goods['user_id'] != $user_id) {
            throw new HttpException(trans('user.unauthorized_access'), 1);
        }

        if (!empty($order_goods)) {
            $order_goods = collect($order_goods)->merge($order_goods['get_goods'])->except('get_goods')->all();

            $order_goods['shop_price_formated'] = $this->dscRepository->getPriceFormat($order_goods['shop_price']);
            $order_goods['goods_thumb'] = $this->dscRepository->getImagePath($order_goods['goods_thumb']);
            // 商品评价标签
            $order_goods['goods_product_tag'] = BaseRepository::getExplode($order_goods['goods_product_tag']);

            if ($is_add_evaluate == 1) {
                // 追加评价 显示首次评价星级
                $comment = OrderCommentRepository::commentFirst($order_goods['goods_id'], $rec_id, $user_id, ['comment_id', 'comment_rank']);
                $order_goods['comment_rank'] = $comment['comment_rank'] ?? 5;
                $order_goods['comment_id'] = $comment['comment_id'] ?? 0;
            }

            $order_goods['degree_count'] = 0;
            if ($order_goods['ru_id'] > 0) {
                // 用户此订单是否对商家提交满意度
                $order_goods['degree_count'] = CommentSeller::where('order_id', $order_goods['order_id'])->where('user_id', $user_id)->count();
            }
        }

        return $order_goods;
    }

    /**
     * 获得商品评论总条数
     *
     * @param int $goods_id
     * @param string $type
     * @param int $count_type
     * @return int
     */
    public function mentsCountAll($goods_id = 0, $type = 'comment_rank', $count_type = 0)
    {
        if (empty($goods_id)) {
            return 0;
        }

        $count = Comment::where('id_value', $goods_id)
            ->where('status', 1)
            ->where('parent_id', 0)
            ->where('add_comment_id', 0)
            ->whereIn($type, [1, 2, 3, 4, 5])
            ->count();

        if ($count == 0) {
            if ($count_type == 0) {
                $count = 1;
            } else {
                $count = 0;
            }

            return $count;
        } else {
            return $count;
        }
    }

    /**
     * 获得商品评论-$num-颗星总条数
     *
     * @param int $goods_id
     * @param int $num
     * @param string $type
     * @return mixed
     */
    public function mentsCountRankNum($goods_id = 0, $num = 0, $type = 'comment_rank')
    {
        if (empty($goods_id)) {
            return 0;
        }

        $count = Comment::where('id_value', $goods_id)
            ->where('status', 1)
            ->where('parent_id', 0)
            ->where('add_comment_id', 0)
            ->where($type, $num)
            ->count();

        return $count;
    }

    /**
     * 获得商品评论显示星星
     *
     * @param null $all
     * @param null $one
     * @param null $two
     * @param null $three
     * @param null $four
     * @param null $five
     * @param string $baseline
     * @return array
     */
    public function getConmentsStars($all = null, $one = null, $two = null, $three = null, $four = null, $five = null, $baseline = '')
    {
        $one_num = 1;
        $two_num = 2;
        $three_num = 3;
        $four_num = 4;
        $five_num = 5;
        $allNmu = $all * 5;                         //总星星数
        $oneAll = $one * $one_num;           //1颗总星星数
        $twoAll = $two * $two_num;           //2颗总星星数
        $threeAll = $three * $three_num;            //3颗总星星数
        $fourAll = $four * $four_num;          //4颗总星星数
        $fiveAll = $five * $five_num;         //5颗总星星数
        $allStars = $oneAll + $twoAll + $threeAll + $fourAll + $fiveAll;  //显示总星星数

        $badReview = $one / $all;          //差评条数
        $middleReview = ($two + $three) / $all;       //中评条数
        $goodReview = ($four + $five) / $all;        //好评条数

        $badmen = $one;            //差评人数
        $middlemen = $two + $three;          //中评人数
        $goodmen = $four + $five;          //好评人数
        $allmen = $one + $two + $three + $four + $five;      //全部评分人数

        $percentage = sprintf("%.2f", ($allStars / $allNmu * 100));

        $arr = [
            'score' => sprintf("%.2f", (round($percentage / 20, 2))), //分数
            'badReview' => round($badReview, 2) * 100, //差评百分比
            'middlReview' => round($middleReview, 2) * 100, //中评百分比
            'goodReview' => round($goodReview, 2) * 100, //好评百分比
            'allReview' => $percentage, //总体百分比
            'badmen' => $badmen, //差评人数
            'middlemen' => $middlemen, //中评人数
            'goodmen' => $goodmen, //好评人数
            'allmen' => $allmen, //全部评论人数
        ];

        if ($percentage >= 1 && $percentage < 40) {               //1颗星
            $arr['stars'] = 1;
        } elseif ($percentage >= 40 && $percentage < 60) {  //2颗星
            $arr['stars'] = 2;
        } elseif ($percentage >= 60 && $percentage < 80) {  //3颗星
            $arr['stars'] = 3;
        } elseif ($percentage >= 80 && $percentage < 100) {  //4颗星
            $arr['stars'] = 4;
        } elseif ($percentage == 100) {
            $arr['score'] = 5;
            $arr['stars'] = 5;
            $arr['badReview'] = 0;        //差评百分比
            $arr['middlReview'] = 0;        //中评百分比
            $arr['goodReview'] = 100;        //好评百分比
            $arr['allReview'] = 100;       //总体百分比
            return $arr;
        } else { //默认状态 --没有评论时
            $arr = [
                'score' => 5, //分数
                'stars' => 5, //星数
                'badReview' => 0, //差评百分比
                'middlReview' => 0, //中评百分比
                'goodReview' => 100, //好评百分比
                'allReview' => 100, //总体百分比
                'allmen' => 0, //全部评论人数
                'badmen' => 0, //差评人数
                'middlemen' => 0, //中评人数
                'goodmen' => 0, //好评人数
            ];
        }

        $review = $arr['badReview'] + $arr['middlReview'] + $arr['goodReview'];

        //计算判断是否超出100值，如有超出则按最大值减去超出值
        if ($review > 100) {
            $review = $review - 100;
            $maxReview = max($arr['badReview'], $arr['middlReview'], $arr['goodReview']);

            if ($maxReview == $arr['badReview']) {
                $arr['badReview'] = $arr['badReview'] - $review;
            } elseif ($maxReview == $arr['middlReview']) {
                $arr['middlReview'] = $arr['middlReview'] - $review;
            } elseif ($maxReview == $arr['goodReview']) {
                $arr['goodReview'] = $arr['goodReview'] - $review;
            }
        }

        $arr['left'] = $arr['stars'] * 18;

        if ($baseline) {
            $res = CommentBaseline::selectRaw($baseline)->whereRaw(1);
            $res = BaseRepository::getToArrayFirst($res);

            $baseline = $res && isset($res[$baseline]) ? $res[$baseline] : 0;

            $arr['up_down'] = $arr['goodReview'] - $baseline;

            if ($arr['up_down'] > $baseline) {
                $arr['is_status'] = 1; //高于
            } elseif ($arr['up_down'] < $baseline) {
                $arr['is_status'] = 0; //低于
                $arr['up_down'] = abs($arr['up_down']);
            } else {
                $arr['is_status'] = 2; //持平
            }
        }
        return $arr;
    }

    /**
     * 商品评论百分比，及数量统计
     *
     * @param int $goods_id
     * @return array
     */
    public function getCommentsPercent($goods_id = 0)
    {
        $arr = [
            'score' => 5, //分数
            'stars' => 5, //星数
            'badReview' => 0, //差评百分比
            'middlReview' => 0, //中评百分比
            'goodReview' => 100, //好评百分比
            'allReview' => 100, //总体百分比
            'allmen' => 0, //全部评论人数
            'badmen' => 0, //差评人数
            'middlemen' => 0, //中评人数
            'goodmen' => 0, //好评人数
        ];

        $count = Comment::where('id_value', $goods_id)
            ->where('status', 1)
            ->where('parent_id', 0)
            ->where('add_comment_id', 0)
            ->count();

        $arr['allmen'] = $count;

        if ($arr['allmen'] == 0) {
            return $arr;
        } else {
            $mc_one = $this->mentsCountRankNum($goods_id, 1);  //一颗星
            $mc_two = $this->mentsCountRankNum($goods_id, 2);     //两颗星
            $mc_three = $this->mentsCountRankNum($goods_id, 3);    //三颗星
            $mc_four = $this->mentsCountRankNum($goods_id, 4);  //四颗星
            $mc_five = $this->mentsCountRankNum($goods_id, 5);  //五颗星

            $arr['goodmen'] = $mc_four + $mc_five;
            $arr['middlemen'] = $mc_two + $mc_three;
            $arr['badmen'] = $mc_one;

            $arr['goodReview'] = round(($arr['goodmen'] / $arr['allmen']) * 100, 1);
            $arr['middlReview'] = round(($arr['middlemen'] / $arr['allmen']) * 100, 1);
            $arr['badReview'] = round(($arr['badmen'] / $arr['allmen']) * 100, 1);

            return $arr;
        }
    }

    /**
     * 获取商家所有商品评分类型汇总
     *
     * @param $ru_id
     * @return array
     * @throws \Exception
     */
    public function getMerchantsGoodsComment($ru_id)
    {
        $arr = [];
        if ($ru_id) {
            $cache_name = 'seller_comment_' . $ru_id;

            $seller_cmt = cache($cache_name);
            $arr = !is_null($seller_cmt) ? $seller_cmt : false;

            if ($arr === false) {
                $res = MerchantsShopInformation::where('user_id', $ru_id)->take(1);
                $res = BaseRepository::getToArrayGet($res);

                foreach ($res as $key => $row) {
                    $arr[$key] = $row;

                    //商品评分
                    $arr[$key]['mc_all_Rank'] = $this->sellerMentsCountAll($row['user_id'], 'desc_rank');       //总条数
                    $arr[$key]['mc_one_Rank'] = $this->sellerMentsCountRankNum($row['user_id'], 1, 'desc_rank');  //一颗星
                    $arr[$key]['mc_two_Rank'] = $this->sellerMentsCountRankNum($row['user_id'], 2, 'desc_rank');     //两颗星
                    $arr[$key]['mc_three_Rank'] = $this->sellerMentsCountRankNum($row['user_id'], 3, 'desc_rank');    //三颗星
                    $arr[$key]['mc_four_Rank'] = $this->sellerMentsCountRankNum($row['user_id'], 4, 'desc_rank');  //四颗星
                    $arr[$key]['mc_five_Rank'] = $this->sellerMentsCountRankNum($row['user_id'], 5, 'desc_rank');  //五颗星
                    //服务评分
                    $arr[$key]['mc_all_Server'] = $this->sellerMentsCountAll($row['user_id'], 'service_rank');       //总条数
                    $arr[$key]['mc_one_Server'] = $this->sellerMentsCountRankNum($row['user_id'], 1, 'service_rank');  //一颗星
                    $arr[$key]['mc_two_Server'] = $this->sellerMentsCountRankNum($row['user_id'], 2, 'service_rank');     //两颗星
                    $arr[$key]['mc_three_Server'] = $this->sellerMentsCountRankNum($row['user_id'], 3, 'service_rank');    //三颗星
                    $arr[$key]['mc_four_Server'] = $this->sellerMentsCountRankNum($row['user_id'], 4, 'service_rank');  //四颗星
                    $arr[$key]['mc_five_Server'] = $this->sellerMentsCountRankNum($row['user_id'], 5, 'service_rank');  //五颗星
                    //时效评分
                    $arr[$key]['mc_all_Delivery'] = $this->sellerMentsCountAll($row['user_id'], 'delivery_rank');       //总条数
                    $arr[$key]['mc_one_Delivery'] = $this->sellerMentsCountRankNum($row['user_id'], 1, 'delivery_rank');  //一颗星
                    $arr[$key]['mc_two_Delivery'] = $this->sellerMentsCountRankNum($row['user_id'], 2, 'delivery_rank');     //两颗星
                    $arr[$key]['mc_three_Delivery'] = $this->sellerMentsCountRankNum($row['user_id'], 3, 'delivery_rank');    //三颗星
                    $arr[$key]['mc_four_Delivery'] = $this->sellerMentsCountRankNum($row['user_id'], 4, 'delivery_rank');  //四颗星
                    $arr[$key]['mc_five_Delivery'] = $this->sellerMentsCountRankNum($row['user_id'], 5, 'delivery_rank');  //五颗星

                    $sid = CommentSeller::where('ru_id', $row['user_id'])->value('sid');

                    if ($sid > 0) {

                        //商品评分
                        @$arr['commentRank']['mc_all'] += $arr[$key]['mc_all_Rank'];
                        @$arr['commentRank']['mc_one'] += $arr[$key]['mc_one_Rank'];
                        @$arr['commentRank']['mc_two'] += $arr[$key]['mc_two_Rank'];
                        @$arr['commentRank']['mc_three'] += $arr[$key]['mc_three_Rank'];
                        @$arr['commentRank']['mc_four'] += $arr[$key]['mc_four_Rank'];
                        @$arr['commentRank']['mc_five'] += $arr[$key]['mc_five_Rank'];

                        //服务评分
                        @$arr['commentServer']['mc_all'] += $arr[$key]['mc_all_Server'];
                        @$arr['commentServer']['mc_one'] += $arr[$key]['mc_one_Server'];
                        @$arr['commentServer']['mc_two'] += $arr[$key]['mc_two_Server'];
                        @$arr['commentServer']['mc_three'] += $arr[$key]['mc_three_Server'];
                        @$arr['commentServer']['mc_four'] += $arr[$key]['mc_four_Server'];
                        @$arr['commentServer']['mc_five'] += $arr[$key]['mc_five_Server'];

                        //时效评分
                        @$arr['commentDelivery']['mc_all'] += $arr[$key]['mc_all_Delivery'];
                        @$arr['commentDelivery']['mc_one'] += $arr[$key]['mc_one_Delivery'];
                        @$arr['commentDelivery']['mc_two'] += $arr[$key]['mc_two_Delivery'];
                        @$arr['commentDelivery']['mc_three'] += $arr[$key]['mc_three_Delivery'];
                        @$arr['commentDelivery']['mc_four'] += $arr[$key]['mc_four_Delivery'];
                        @$arr['commentDelivery']['mc_five'] += $arr[$key]['mc_five_Delivery'];
                    }
                }

                @$arr['cmt']['commentRank']['zconments'] = $this->getConmentsStars($arr['commentRank']['mc_all'], $arr['commentRank']['mc_one'], $arr['commentRank']['mc_two'], $arr['commentRank']['mc_three'], $arr['commentRank']['mc_four'], $arr['commentRank']['mc_five'], 'goods');
                @$arr['cmt']['commentServer']['zconments'] = $this->getConmentsStars($arr['commentServer']['mc_all'], $arr['commentServer']['mc_one'], $arr['commentServer']['mc_two'], $arr['commentServer']['mc_three'], $arr['commentServer']['mc_four'], $arr['commentServer']['mc_five'], 'service');
                @$arr['cmt']['commentDelivery']['zconments'] = $this->getConmentsStars($arr['commentDelivery']['mc_all'], $arr['commentDelivery']['mc_one'], $arr['commentDelivery']['mc_two'], $arr['commentDelivery']['mc_three'], $arr['commentDelivery']['mc_four'], $arr['commentDelivery']['mc_five'], 'shipping');

                @$arr['cmt']['all_zconments']['score'] = sprintf("%.2f", ($arr['cmt']['commentRank']['zconments']['score'] + $arr['cmt']['commentServer']['zconments']['score'] + $arr['cmt']['commentDelivery']['zconments']['score']) / 3);
                @$arr['cmt']['all_zconments']['allReview'] = round((($arr['cmt']['commentRank']['zconments']['allReview'] + $arr['cmt']['commentServer']['zconments']['allReview'] + $arr['cmt']['commentDelivery']['zconments']['allReview']) / 3), 2);
                @$arr['cmt']['all_zconments']['position'] = 100 - $arr['cmt']['all_zconments']['allReview'] - 3;

                cache()->forever($cache_name, $arr);
            }
        }

        return $arr;
    }

    /**
     * 获得订单商品评论总条数
     *
     * @param $ru_id
     * @param $type
     * @return mixed
     */
    public function sellerMentsCountAll($ru_id, $type)
    {
        return CommentSeller::where('ru_id', $ru_id)->whereIn($type, [1, 2, 3, 4, 5])->count();
    }

    /**
     * 获得商品评论-$num-颗星总条数
     *
     * @param $ru_id
     * @param $num
     * @param $type
     * @return mixed
     */
    public function sellerMentsCountRankNum($ru_id, $num, $type)
    {
        return CommentSeller::where('ru_id', $ru_id)->where($type, $num)->count();
    }

    /**
     * 商品评分数
     *
     * @param int $goods_id
     * @return array
     */
    public function goodsZconments($goods_id = 0)
    {
        $mc_all = $this->mentsCountAll($goods_id);       //总条数
        $mc_one = $this->mentsCountRankNum($goods_id, 1);  //一颗星
        $mc_two = $this->mentsCountRankNum($goods_id, 2);     //两颗星
        $mc_three = $this->mentsCountRankNum($goods_id, 3);    //三颗星
        $mc_four = $this->mentsCountRankNum($goods_id, 4);  //四颗星
        $mc_five = $this->mentsCountRankNum($goods_id, 5);  //五颗星
        $zconments = $this->getConmentsStars($mc_all, $mc_one, $mc_two, $mc_three, $mc_four, $mc_five);

        return $zconments;
    }

    /**
     * 查询会员回复信息列表
     *
     * @param int $goods_id
     * @param int $comment_id
     * @param int $type
     * @param int $reply_page
     * @param int $libType
     * @param int $reply_size
     * @return array
     */
    public function getReplyList($goods_id = 0, $comment_id = 0, $type = 0, $reply_page = 1, $libType = 0, $reply_size = 2)
    {
        $reply_pager = [];
        $reply_count = 0;

        if ($type == 1) {
            $reply_list = Comment::where('id_value', $goods_id)
                ->where('parent_id', $comment_id)
                ->where('user_id', session('user_id'))
                ->where('status', 0)
                ->orderBy('comment_id', 'desc');

            $reply_list = BaseRepository::getToArrayGet($reply_list);
        } else {
            $reply_count = Comment::where('id_value', $goods_id)
                ->where('parent_id', $comment_id)
                ->where('status', 1);
            $reply_count = CommonRepository::constantMaxId($reply_count, 'user_id');
            $reply_count = $reply_count->count();

            $id = '"' . $goods_id . "|" . $comment_id . '"';

            $pagerParams = [
                'total' => $reply_count,
                'listRows' => $reply_size,
                'id' => $id,
                'page' => $reply_page,
                'funName' => 'reply_comment_gotoPage',
                'pageType' => 1,
                'libType' => $libType,
                'cfigType' => 1
            ];
            $reply_comment = new Pager($pagerParams);

            $reply_pager = $reply_comment->fpage([0, 4, 5, 6, 9]);

            $reply_list = Comment::where('id_value', $goods_id)
                ->where('parent_id', $comment_id)
                ->where('status', 1);
            $reply_list = CommonRepository::constantMaxId($reply_list, 'user_id');
            $reply_list = $reply_list->orderBy('comment_id', 'desc');

            $start = ($reply_page - 1) * $reply_size;

            if ($start > 0) {
                $reply_list = $reply_list->skip($start);
            }

            if ($reply_size > 0) {
                $reply_list = $reply_list->take($reply_size);
            }

            $reply_list = BaseRepository::getToArrayGet($reply_list);

            if ($reply_page == 1) {
                $floor = 0;
            } else {
                $floor = ($reply_page - 1) * $reply_size;
            }

            if ($reply_list) {
                foreach ($reply_list as $key => $row) {
                    $floor = $floor + 1;
                    $reply_list[$key]['floor'] = $floor;

                    $reply_list[$key]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
                    $reply_list[$key]['content'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
                }
            }
        }

        $arr = ['reply_list' => $reply_list, 'reply_pager' => $reply_pager, 'reply_count' => $reply_count, 'reply_size' => $reply_size];

        return $arr;
    }

    /**
     * 文章评论列表
     * @param $article_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getArticleCommentList($article_id, $page = 1, $size = 10)
    {
        $begin = ($page - 1) * $size;

        $comment_list = Comment::where('id_value', $article_id)
            ->where('comment_type', 1)
            ->where('status', 1);

        $comment_list = $comment_list->with([
            'user'
        ]);
        $comment_list = $comment_list->orderBy('add_time', 'desc')
            ->offset($begin)
            ->limit($size);

        $comment_list = BaseRepository::getToArrayGet($comment_list);

        if ($comment_list) {
            foreach ($comment_list as $key => $val) {
                $user_name = $val['user']['nick_name'] ?? '';

                //iconv_strlen计算有多少个字符,不是字节长度
                $name_len = ceil(iconv_strlen($user_name, 'UTF-8') / 3);
                if ($name_len > 2) {
                    $name_len = 3;
                } elseif ($name_len == 2) {
                    $name_len = 1;
                } else {
                    $user_name .= '*';
                    $name_len = 1;
                }

                $comment_list[$key]['user_name'] = $this->dscRepository->stringToStar($user_name, $name_len, 6);

                $comment_list[$key]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $val['add_time']);
                $comment_list[$key]['amity_time'] = Carbon::createFromTimestamp($val['add_time'] + config('shop.timezone') * 3600)->diffForHumans();
                $user_picture = $val['user']['user_picture'] ?? '';
                $comment_list[$key]['user_picture'] = $this->dscRepository->getImagePath($user_picture);
            }
        }

        return $comment_list;
    }


    /**
     * 查询评论内容
     *
     * @param $id
     * @param $type
     * @param int $page
     * @return array
     * @throws \Exception
     */
    public function getAssignArticleComment($id, $type, $page = 1)
    {
        $tag = [];

        /* 取得评论列表 */
        $count = Comment::where('id_value', $id)
            ->where('comment_type', $type)
            ->where('status', 1)
            ->where('parent_id', 0);

        $count = $count->count();

        $size = config('shop.comments_number') ?? 5;

        $pagerParams = [
            'total' => $count,
            'listRows' => $size,
            'id' => $id,
            'page' => $page,
            'funName' => 'gotoPage',
            'pageType' => 1
        ];
        $comment = new Pager($pagerParams);
        $pager = $comment->fpage([0, 4, 5, 6, 9]);

        $res = Comment::where('id_value', $id)
            ->where('comment_type', $type)
            ->where('status', 1)
            ->where('parent_id', 0);

        $res = $res->with([
            'user'
        ]);

        $start = ($page - 1) * $size;
        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = $res->orderBy('add_time', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        $ids = '';
        if ($res) {
            foreach ($res as $row) {
                //处理用户名 by wu
                //iconv_strlen计算有多少个字符,不是字节长度
                $name_len = ceil(iconv_strlen($row['user_name'], 'UTF-8') / 3);
                if ($name_len > 2) {
                    $name_len = 3;
                } elseif ($name_len == 2) {
                    $name_len = 1;
                } else {
                    $row['user_name'] .= '*';
                    $name_len = 1;
                }

                $row['user_name'] = $this->dscRepository->stringToStar($row['user_name'], $name_len, 3);

                $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
                $arr[$row['comment_id']]['id'] = $row['comment_id'];
                $arr[$row['comment_id']]['email'] = $row['email'];
                $arr[$row['comment_id']]['username'] = $row['user_name'];
                $arr[$row['comment_id']]['user_id'] = $row['user_id'];
                $arr[$row['comment_id']]['id_value'] = $row['id_value'];
                $arr[$row['comment_id']]['useful'] = $row['useful'];
                $arr[$row['comment_id']]['status'] = $row['status'];
                $user_picture = $row['user']['user_picture'] ?? '';
                $arr[$row['comment_id']]['user_picture'] = $this->dscRepository->getImagePath($user_picture);

                $arr[$row['comment_id']]['content'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
                $arr[$row['comment_id']]['rank'] = $row['comment_rank'];
                $arr[$row['comment_id']]['server'] = $row['comment_server'];
                $arr[$row['comment_id']]['delivery'] = $row['comment_delivery'];
                $arr[$row['comment_id']]['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
                $arr[$row['comment_id']]['buy_goods'] = $this->orderGoodsService->getUserBuyGoodsOrder($row['id_value'], $row['user_id'], $row['order_id']);

                //商品印象
                if ($row['goods_tag']) {
                    $row['goods_tag'] = explode(",", $row['goods_tag']);
                    foreach ($row['goods_tag'] as $key => $val) {
                        $tag[$key]['txt'] = $val;
                        //印象数量
                        $tag[$key]['num'] = $this->goodsCommentService->commentGoodsTagNum($row['id_value'], $val);
                    }
                    $arr[$row['comment_id']]['goods_tag'] = $tag;
                }

                $reply = $this->getReplyList($row['id_value'], $row['comment_id']);
                $arr[$row['comment_id']]['reply_list'] = $reply['reply_list'];
                $arr[$row['comment_id']]['reply_count'] = $reply['reply_count'];
                $arr[$row['comment_id']]['reply_size'] = $reply['reply_size'];
                $arr[$row['comment_id']]['reply_pager'] = $reply['reply_pager'];

                $where = [
                    'goods_id' => $row['id_value'],
                    'comment_id' => $row['comment_id']
                ];
                $img_list = $this->getCommentImgList($where);

                $arr[$row['comment_id']]['img_list'] = $img_list;
                $arr[$row['comment_id']]['img_cont'] = count($img_list);

                $arr[$row['comment_id']]['user_picture'] = $this->dscRepository->getImagePath($arr[$row['comment_id']]['user_picture']);
            }
        }

        /* 取得已有回复的评论 */
        if ($ids) {

            $ids = BaseRepository::getExplode($ids);
            $res = Comment::whereIn('parent_id', $ids);
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {

                $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

                foreach ($res as $row) {
                    $arr[$row['parent_id']]['re_content'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
                    $arr[$row['parent_id']]['re_add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
                    $arr[$row['parent_id']]['re_email'] = $row['email'];
                    $arr[$row['parent_id']]['re_username'] = $row['user_name'];
                    $arr[$row['parent_id']]['re_status'] = $row['status'];

                    $shop_info = $merchantList[$row['ru_id']] ?? [];
                    $arr[$row['parent_id']]['shop_name'] = $shop_info['shop_name'] ?? '';
                }
            }
        }

        return ['comments' => $arr, 'pager' => $pager, 'count' => $count, 'size' => $size];
    }

    /**
     * 提交评论
     * @param $id
     * @param int $parent_id
     * @param string $content
     * @param int $user_id
     * @return bool
     */
    public function submitComment($id, $parent_id = 0, $content = '', $user_id = 0)
    {
        if (empty($content) || empty($user_id)) {
            return false;
        }

        $time = TimeRepository::getGmTime();

        $user_info = Users::where('user_id', $user_id)->select('user_name', 'nick_name');
        $user_info = BaseRepository::getToArrayFirst($user_info);

        //因为平台后台设置,如果需要审核 comment_check值为1
        //status:是否被管理员批准显示，1，是；0，未批准
        $status = config('shop.comment_check') == 1 ? 0 : 1;

        $data = [
            'content' => $content,
            'user_id' => $user_id,
            'user_name' => isset($user_info['nick_name']) ? $user_info['nick_name'] : (isset($user_info['user_name']) ? $user_info['user_name'] : ''),
            'id_value' => $id,
            'comment_type' => 1,
            'parent_id' => $parent_id,
            'status' => $status,
            'add_time' => $time,
            'ip_address' => request()->getClientIp()
        ];

        return Comment::insertGetId($data);
    }

    /**评论总数
     * @param $article_id
     * @return mixed
     */
    public function getCommentMumber($article_id)
    {
        $comment_num = Comment::where('id_value', $article_id)
            ->where('comment_type', 1)
            ->where('status', 1)
            ->count();
        return $comment_num;
    }

    /**
     * 评论数量格式化
     * @param int $count
     * @return mixed
     */
    public static function commentNumberFormat($count = 0)
    {
        if (empty($count)) {
            return 0;
        }

        switch ($count) {
            case $count >= 1000 && $count <= 9999 :
                $new_count = floor($count / 1000) . '000+';
                break;
            case $count >= 10000 :
                $new_count = substr(sprintf("%.2f", $count / 10000), 0, -1) . '万+';
                $new_count = str_replace('.0', '', $new_count);
                break;
            default:
                $new_count = $count;
                break;
        }

        return $new_count;
    }

    /**
     * 剔除指定评价图
     *
     * @param int $user_id
     * @param int $img_id
     * @param int $order_id
     * @param int $goods_id
     * @return array
     */
    public function deleteCommentImg($user_id = 0, $img_id = 0, $order_id = 0, $goods_id = 0)
    {
        if (empty($user_id)) {
            return [];
        }

        $model = CommentImg::where('user_id', $user_id)->where('comment_id', 0)->where('order_id', $order_id)->where('goods_id', $goods_id);

        $model = $model->select('id', 'comment_img', 'img_thumb')->get();

        $img_list = $model ? $model->toArray() : [];

        if ($img_list) {
            foreach ($img_list as $key => $val) {
                if ($img_id == $val['id']) {
                    $this->dscRepository->getOssDelFile([$val['comment_img'], $val['img_thumb']]);
                    @unlink(storage_public($val['comment_img']));
                    @unlink(storage_public($val['img_thumb']));

                    CommentImg::where('id', $img_id)->delete();
                    unset($img_list[$key]);
                } else {
                    $img_list[$key]['comment_img'] = $this->dscRepository->getImagePath($val['comment_img']);
                    $img_list[$key]['img_thumb'] = $this->dscRepository->getImagePath($val['img_thumb']);
                }
            }
        }

        return $img_list;
    }

    /**
     * 剔除用户未保存晒单图
     *
     * @param int $user_id
     * @return bool
     */
    public function deleteCommentImgList($user_id = 0)
    {
        if (empty($user_id)) {
            return false;
        }

        $model = CommentImg::where('user_id', $user_id)->where('comment_id', 0)->select('comment_img', 'img_thumb')->get();

        $img_list = $model ? $model->toArray() : [];

        if ($img_list) {
            foreach ($img_list as $key => $val) {
                $this->dscRepository->getOssDelFile([$val['comment_img'], $val['img_thumb']]);
                @unlink(storage_public($val['comment_img']));
                @unlink(storage_public($val['img_thumb']));
            }
        }

        CommentImg::where('user_id', $user_id)->where('comment_id', 0)->delete();
        return true;
    }
}
