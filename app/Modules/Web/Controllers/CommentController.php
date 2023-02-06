<?php

namespace App\Modules\Web\Controllers;

use App\Exceptions\HttpException;
use App\Libraries\CaptchaVerify;
use App\Libraries\Image;
use App\Models\Comment;
use App\Models\CommentImg;
use App\Models\Users;
use App\Repositories\Common\DscRepository;
use App\Services\Comment\CommentService;
use App\Services\Comment\OrderCommentService;
use Illuminate\Support\Facades\Validator;

/**
 * 提交用户评论
 */
class CommentController extends InitController
{
    protected $commentService;
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository,
        CommentService $commentService
    )
    {
        $this->commentService = $commentService;
        $this->dscRepository = $dscRepository;
    }

    public function index()
    {
        if (!request()->exists('cmt') && !request()->exists('act')) {
            /* 只有在没有提交评论内容以及没有act的情况下才跳转 */
            return dsc_header("Location: ./\n");
        }

        $act = e(trim(request()->input('act')));

        $user_id = session('user_id', 0);

        $result = ['error' => 0, 'message' => '', 'content' => ''];

        /*------------------------------------------------------ */
        //-- 无刷新上传图片ajax
        /*------------------------------------------------------ */
        if ($act == 'ajax_return_images') {
            $img_file = isset($_FILES['file']) ? $_FILES['file'] : [];

            $order_id = intval(request()->input('order_id'));
            $rec_id = intval(request()->input('rec_id'));
            $goods_id = intval(request()->input('goods_id'));
            $user_id = intval(request()->input('userId'));
            $comment_id = intval(request()->input('comment_id', 0));

            if (empty($user_id)) {
                $result['error'] = 1;
                $result['msg'] = $GLOBALS['_LANG']['please_login'];
                return response()->json($result);
            }

            $img_count = CommentImg::where('user_id', $user_id)
                ->where('order_id', $order_id)
                ->where('rec_id', $rec_id)
                ->where('goods_id', $goods_id)
                ->where('comment_id', $comment_id)
                ->count();

            if ($img_count >= 9) {
                $result['error'] = 1;
                $result['msg'] = $GLOBALS['_LANG']['comment_img_number'];
                return response()->json($result);
            }

            // 上传图片
            $image = new Image(['bgcolor' => $GLOBALS['_CFG']['bgcolor']]);

            $img_file = $image->upload_image($img_file, 'cmt_img/' . date('Ym')); //原图
            if ($img_file === false) {
                $result['error'] = 1;
                $result['msg'] = $image->error_msg();
                return response()->json($result);
            }

            $img_thumb = $image->make_thumb($img_file, $GLOBALS['_CFG']['single_thumb_width'], $GLOBALS['_CFG']['single_thumb_height'], storage_public(DATA_DIR . '/cmt_img/' . date('Ym') . '/thumb/')); //缩略图
            $img_thumb = $img_thumb ? str_replace(storage_public(), '', $img_thumb) : '';
            $this->dscRepository->getOssAddFile([$img_file, $img_thumb]);

            if ($img_file) {
                $data = [
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'rec_id' => $rec_id,
                    'goods_id' => $goods_id,
                    'comment_id' => 0,
                    'comment_img' => $img_file,
                    'img_thumb' => $img_thumb
                ];
                // 评价上传图片列表
                CommentImg::insert($data);
            }

            // 评价上传图片列表
            $imgWhere = [
                'user_id' => $user_id,
                'order_id' => $order_id,
                'rec_id' => $rec_id,
                'goods_id' => $goods_id,
                'comment_id' => 0
            ];
            $img_list = $this->commentService->getCommentImgList($imgWhere);

            $result['imglist_count'] = $img_list ? count($img_list) : 0;
            // 返回最后一张上传图片
            $result['currentImg_path'] = isset($img_list[0]['comment_img']) ? $img_list[0]['comment_img'] : '';
            $result['currentImg_id'] = isset($img_list[0]['id']) ? $img_list[0]['id'] : 0;

            $this->smarty->assign('img_list', $img_list);
            $result['content'] = $this->smarty->fetch("library/comment_image.lbi");

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 删除晒单图片
        /*------------------------------------------------------ */
        elseif ($act == 'del_pictures') {
            $img_id = intval(request()->input('cur_imgId'));
            $order_id = intval(request()->input('order_id'));
            $goods_id = intval(request()->input('goods_id'));

            if (empty($user_id) || !$img_id) {
                $result['error'] = 1;
                $result['msg'] = $GLOBALS['_LANG']['please_login'];
                return response()->json($result);
            }

            // 剔除指定评价图 返回剩余的图片列表
            $img_list = $this->commentService->deleteCommentImg($user_id, $img_id, $order_id, $goods_id);

            $this->smarty->assign('img_list', $img_list);
            $result['content'] = $this->smarty->fetch("library/comment_image.lbi");

            return response()->json($result);
        }
        /*------------------------------------------------------ */
        //-- 晒单图片列表ajax
        /*------------------------------------------------------ */
        elseif ($act == 'ajax_return_images_list') {
            $order_id = intval(request()->input('order_id'));
            $goods_id = intval(request()->input('goods_id'));

            if (empty($user_id)) {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['_LANG']['please_login'];
                return response()->json($result);
            }

            $imgWhere = [
                'user_id' => $user_id,
                'order_id' => $order_id,
                'goods_id' => $goods_id
            ];
            $img_list = $this->commentService->getCommentImgList($imgWhere);

            if ($img_list) {
                $this->smarty->assign('img_list', $img_list);
                $result['content'] = $this->smarty->fetch("library/comment_image.lbi");
            } else {
                $result['error'] = 1;
            }

            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 订单商品评论提交
        /*------------------------------------------------------ */
        elseif ($act == 'comm_order_goods') {
            $result = ['error' => 0, 'msg' => '', 'content' => ''];

            $cmt = strip_tags(urldecode(request()->input('cmt')));
            $cmt = json_str_iconv($cmt);
            $cmt = json_decode(stripslashes($cmt)); // 处理json转义

            if (empty($cmt)) {
                $result['error'] = 1;
                $result['msg'] = 'cmt data is empty';
                return response()->json($result);
            }
            if (empty($user_id)) {
                $result['error'] = 1;
                $result['msg'] = $GLOBALS['_LANG']['please_login'];
                return response()->json($result);
            }

            $sign = $cmt->sign ?? 0;
            $rec_id = intval($cmt->rec_id ?? 0);
            $is_add_evaluate = $cmt->is_add_evaluate ?? 0;

            $result['sign'] = $sign;

            /* 验证码检查 */
            if ((intval(config('shop.captcha')) & CAPTCHA_COMMENT) && gd_version() > 0 && $is_add_evaluate == 0) {
                $captcha_str = isset($cmt->captcha) ? e(trim($cmt->captcha)) : '';
                $captcha_code = app(CaptchaVerify::class)->check($captcha_str, 'user_comment', $rec_id);
                if (!$captcha_code) {
                    $result['error'] = 1;
                    $result['msg'] = $GLOBALS['_LANG']['invalid_captcha'];
                    return response()->json($result);
                }
            }

            // 提交评价数据
            $cmt = [
                'type' => 0,
                'rec_id' => $rec_id,
                'id' => intval($cmt->goods_id ?? 0), //商品id
                'order_id' => intval($cmt->order_id ?? 0),
                'tag' => $cmt->impression ?? '', // 买家印象
                'content' => $cmt->content ?? '',
                'rank' => $cmt->comment_rank ?? 5,
                'server' => $cmt->comment_rank ?? 5,
                'delivery' => $cmt->comment_rank ?? 5,
                'comment_id' => intval($cmt->comment_id ?? 0), // 追加评价 须上传首次评价id
                // 商家满意度评价
                'desc_rank' => $cmt->desc_rank,
                'service_rank' => $cmt->service_rank,
                'delivery_rank' => $cmt->delivery_rank,
                'sender_rank' => $cmt->sender_rank
            ];

            // 数据验证
            $validator = Validator::make($cmt, [
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

            // 返回错误
            if ($validator->fails()) {
                return response()->json(['error' => 1, 'msg' => $validator->errors()->first()]);
            }

            try {
                // 评价图片列表
                $cmt_img_list = [];

                if ($is_add_evaluate == 1) {
                    // 追加评价
                    $data = app(OrderCommentService::class)->addEvaluateOrderGoods($user_id, $cmt, $cmt_img_list);
                } else {
                    // 首次评价
                    $data = app(OrderCommentService::class)->evaluateOrderGoods($user_id, $cmt, $cmt_img_list);
                }

                if ($data['comment_id'] > 0) {
                    // 更新本次提交评价的上传图片列表
                    CommentImg::where('user_id', $user_id)
                        ->where('order_id', $cmt['order_id'])
                        ->where('rec_id', $rec_id)
                        ->where('goods_id', $cmt['id'])
                        ->where('comment_id', 0)
                        ->update(['comment_id' => $data['comment_id']]);
                }

                // 返回链接拼接参数
                $data['sign'] = $sign;

            } catch (HttpException $httpException) {
                return response()->json(['error' => 1, 'msg' => $httpException->getMessage()]);
            }

            return response()->json($data);
        }

        /*------------------------------------------------------ */
        //-- 商品评论列表
        /*------------------------------------------------------ */
        elseif ($act == 'comment_all' || $act == 'comment_good' || $act == 'comment_middle' || $act == 'comment_short' || $act == 'gotopage') {
            /*
             * act 参数不为空
             * 默认为评论内容列表
             * 根据 _GET 创建一个静态对象
             */

            $id = htmlspecialchars(request()->input('id', 0));
            $type = intval(request()->input('type'));
            $page = intval(request()->input('page', 1));

            $id = explode("|", $id);

            $goods_id = $id[0];
            $cmtType = $id[1];

            $comments = assign_comment($goods_id, $type, $page, $cmtType);

            $this->smarty->assign('comment_type', $type);
            $this->smarty->assign('id', $id);
            $this->smarty->assign('username', session('user_name'));
            $this->smarty->assign('email', session('email'));
            $this->smarty->assign('comments', $comments['comments']);
            $this->smarty->assign('pager', $comments['pager']);

            $this->smarty->assign('count', $comments['count']);
            $this->smarty->assign('size', $comments['size']);

            $result['content'] = $this->smarty->fetch("library/comments_list.lbi");
            return response()->json($result);
        }

        /*------------------------------------------------------ */
        //-- 评论有用+1
        /*------------------------------------------------------ */
        elseif ($act == 'add_useful') {
            $res = ['err_msg' => '', 'content' => '', 'err_no' => 0];

            $id = intval(request()->input('id'));
            $type = request()->input('type', 'comment');
            $goods_id = intval(request()->input('goods_id'));

            if (!empty($id)) {
                if (!isset($user_id) || $user_id == 0) {
                    $res['url'] = get_return_goods_url($goods_id);
                    $res['err_no'] = 1;
                } else {
                    $comment = Comment::select('useful_user', 'useful')->where('comment_id', $id)->first();

                    $comment = $comment ? $comment->toArray() : [];

                    if ($comment && $comment['useful_user']) {
                        $useful_user = explode(',', $comment['useful_user']);
                        if (in_array($user_id, $useful_user)) {
                            $res['err_no'] = 2;
                            return response()->json($res);
                        } else {
                            array_push($useful_user, $user_id);
                            $useful_user = implode(',', $useful_user);
                        }
                    } else {
                        $useful_user = [0];
                        array_push($useful_user, $user_id);
                        $useful_user = implode(',', $useful_user);
                    }

                    $count = Comment::select('useful_user', 'useful')->where('comment_id', $id)->count();

                    if ($count == 1) {
                        $update = Comment::where('comment_id', $id)->increment('useful', 1, ['useful_user' => $useful_user]);

                        if ($update) {
                            $res = ['option' => 'true', 'id' => $id, 'type' => $type, 'useful' => $comment['useful'] + 1, 'err_no' => 0];
                        } else {
                            $res = ['error' => '', 'id' => $id, 'type' => $type, 'err_no' => 2];
                        }
                    } else {
                        $res = ['option' => '', 'id' => $id, 'type' => $type, 'err_no' => 2];
                    }
                }
            }

            return response()->json($res);
        }

        /*------------------------------------------------------ */
        //-- 商品评论回复
        /*------------------------------------------------------ */
        elseif ($act == 'comment_reply') {
            $result = ['err_msg' => '', 'err_no' => 0, 'content' => ''];

            $comment_id = intval(request()->input('comment_id'));
            $reply_content = htmlspecialchars(trim(request()->input('reply_content', 0)));
            $goods_id = intval(request()->input('goods_id'));

            $comment_user = intval(request()->input('user_id'));
            $libType = intval(request()->input('libType'));

            $type = 0;
            $reply_page = 1;

            $add_time = gmtime();
            $real_ip = $this->dscRepository->dscIp();

            $result['comment_id'] = $comment_id;
            $result['reply_content'] = $reply_content;

            if (!isset($user_id) || $user_id == 0) {
                //$result['err_no'] = 1;
            } elseif ($comment_user == $user_id) {
                //$result['err_no'] = 2;
            } else {
                $comment_user_count = Comment::where('id_value', $goods_id)
                    ->where('parent_id', $comment_id)
                    ->where('user_id', $user_id)
                    ->count();

                if ($comment_user_count > 0) {
                    $result['err_no'] = 2;
                } else {
                    $comment_user_name = Users::where('user_id', $user_id)->value('user_name');

                    $status = 1 - $GLOBALS['_CFG']['comment_check'];

                    $other = [
                        'id_value' => $goods_id,
                        'content' => $reply_content,
                        'comment_type' => 2,
                        'user_name' => $comment_user_name,
                        'comment_rank' => 5,
                        'comment_server' => 5,
                        'comment_delivery' => 5,
                        'add_time' => $add_time,
                        'parent_id' => $comment_id,
                        'user_id' => $user_id,
                        'ip_address' => $real_ip,
                        'status' => $status
                    ];
                    Comment::insert($other);

                    $result['message'] = $GLOBALS['_CFG']['comment_check'] ? $GLOBALS['_LANG']['cmt_submit_wait'] : $GLOBALS['_LANG']['cmt_submit_done'];
                }
            }

            if ($libType == 1) {
                $size = 10;
            } else {
                $size = 2;
            }

            if ($result['err_no'] != 1) {
                $reply = $this->commentService->getReplyList($goods_id, $comment_id, $type, $reply_page, $libType, $size);
                $this->smarty->assign('reply_pager', $reply['reply_pager']);
                $this->smarty->assign('reply_count', $reply['reply_count']);
                $this->smarty->assign('reply_list', $reply['reply_list']);
                $this->smarty->assign('lang', $GLOBALS['_LANG']);

                $result['reply_count'] = $reply['reply_count'];

                if ($libType == 1) {
                    $result['content'] = $this->smarty->fetch("library/comment_repay.lbi");
                } else {
                    $result['content'] = $this->smarty->fetch("library/comment_reply.lbi");
                }
            }

            $result['url'] = get_return_goods_url($goods_id);

            return response()->json($result);
        }
    }
}
