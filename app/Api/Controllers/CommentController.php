<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Exceptions\HttpException;
use App\Services\Comment\CommentService;
use App\Services\Comment\OrderCommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class CommentController
 * @package App\Api\Controllers
 */
class CommentController extends Controller
{
    protected $commentService;

    public function __construct(
        CommentService $commentService
    )
    {
        $this->commentService = $commentService;
    }

    /**
     * 商品评论数量
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function title(Request $request)
    {
        $this->validate($request, [
            'goods_id' => 'required|integer',
        ]);

        $goods_id = $request->input('goods_id');

        $data = $this->commentService->goodsCommentCount($goods_id);

        return $this->succeed($data);
    }

    /**
     * 商品评论列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function goods(Request $request)
    {
        $this->validate($request, [
            'goods_id' => 'required|integer',
            'page' => 'required|integer',
            'size' => 'required|integer',
        ]);

        $goods_id = (int)$request->input('goods_id', 0);

        $rank = $request->input('rank', 'all');
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);

        $goods_tag = $request->input('goods_tag', '');

        $data = $this->commentService->GoodsComment(0, $goods_id, $rank, $page, $size, $goods_tag);

        return $this->succeed($data);
    }

    /**
     * 订单评价数量
     *
     * @param Request $request
     * @param OrderCommentService $orderCommentService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function order_goods_title(Request $request, OrderCommentService $orderCommentService)
    {
        $order_id = (int)$request->input('id', 0); // 订单id

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = [
            'signNum0' => $orderCommentService->getOrderCommentCount($user_id, 0, $order_id),// 待评价数量
            'signNum1' => $orderCommentService->getOrderCommentCount($user_id, 1, $order_id), // 已评价数量
            'add_evaluate' => (int)config('shop.add_evaluate', 0), // 是否开启追评
        ];

        return $this->succeed($data);
    }

    /**
     * 待评论列表
     *
     * @param Request $request
     * @param OrderCommentService $orderCommentService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function commentlist(Request $request, OrderCommentService $orderCommentService)
    {
        $this->validate($request, [
            'page' => 'filled|integer',
            'size' => 'filled|integer',
            'sign' => 'required|integer',
            'id' => 'filled|integer',
        ]);

        $sign = $request->input('sign', 0); // 0：待评论 1：已评价/追评
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $order_id = $request->input('id', 0); // 订单id

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $orderCommentService->getOrderComment($user_id, $sign, $order_id, $page, $size);

        return $this->succeed($data);
    }

    /**
     * 订单商品评论页
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addcomment(Request $request)
    {
        $this->validate($request, [
            'rec_id' => 'required|integer',
            'is_add_evaluate' => 'filled|integer',
        ]);

        $rec_id = $request->input('rec_id', 0);
        $is_add_evaluate = $request->input('is_add_evaluate', 0); // 0：首次评论 1：追加评论

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        try {
            $data = $this->commentService->getOrderGoods($rec_id, $user_id, $is_add_evaluate);
        } catch (HttpException $httpException) {
            return $this->setErrorCode(422)->failed($httpException->getMessage());
        }

        return $this->succeed($data);
    }

    /**
     * 订单商品评论提交
     * @param Request $request
     * @param OrderCommentService $orderCommentService
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addGoodsComment(Request $request, OrderCommentService $orderCommentService)
    {
        //验证数据
        $this->validate($request, [
            'type' => 'required|integer',     // 评论类型
            'id' => 'required|integer',       // 商品id
            'rank' => 'required|integer',
            'server' => 'filled|integer',
            'delivery' => 'filled|integer',
            'order_id' => 'required|integer', // 订单id
            'rec_id' => 'required|integer',   // 订单商品id
            'content' => 'required|string|max:500',   // 评价内容 最大500个字符
            'is_add_evaluate' => 'filled|integer',
            'comment_id' => 'filled|integer', // 追加评价 须上传首次评价id
        ]);

        $cmt = $request->post();
        $cmt_img_list = $request->input('pic');

        $is_add_evaluate = $request->input('is_add_evaluate', 0); // 0: 首次评价 1: 追加评价

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        try {
            if ($is_add_evaluate == 1) {
                // 追加评价
                $data = $orderCommentService->addEvaluateOrderGoods($user_id, $cmt, $cmt_img_list);
            } else {
                // 首次评价
                $data = $orderCommentService->evaluateOrderGoods($user_id, $cmt, $cmt_img_list);
            }

        } catch (HttpException $httpException) {
            return $this->setErrorCode(422)->failed($httpException->getMessage());
        }

        return $this->succeed($data);
    }
}
