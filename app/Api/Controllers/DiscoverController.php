<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Services\Discover\DiscoverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class DiscoverController
 * @package App\Api\Controllers
 */
class DiscoverController extends Controller
{
    protected $discoverService;

    public function __construct(
        DiscoverService $discoverService
    )
    {
        $this->discoverService = $discoverService;
    }

    /**
     * 首页
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $uid = $this->authorization();

        $data = $this->discoverService->Index($uid);

        return $this->succeed($data);
    }

    /**
     * 网友讨论圈列表
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $dis_type = (int)$request->input('dis_type');

        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);

        $uid = $this->authorization();

        $data = $this->discoverService->List($uid, $dis_type, $page, $size);

        return $this->succeed($data);
    }

    /**
     * 我的帖子列表
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function mylist(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'dis_type' => 'required|integer'
        ]);

        $dis_type = (int)$request->input('dis_type', 1);
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);

        $uid = $this->authorization();

        if (!$uid) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->discoverService->List($uid, $dis_type, $page, $size, 'mylist');

        return $this->succeed($data);
    }

    /**
     * 帖子详情
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function detail(Request $request)
    {
        $this->validate($request, [
            'dis_type' => 'required|integer',
            'dis_id' => 'required|integer',
        ]);

        $dis_type = (int)$request->input('dis_type', 0);
        $dis_id = (int)$request->input('dis_id', 0);

        $uid = $this->authorization();

        $data = $this->discoverService->Detail($uid, $dis_type, $dis_id);

        return $this->succeed($data);
    }

    /**
     * 评论列表
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function CommentList(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'dis_type' => 'required'
        ]);

        $dis_type = $request->input('dis_type');
        $goods_id = (int)$request->input('goods_id');
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);

        $uid = $this->authorization();

        $data = $this->discoverService->CommentList($dis_type, $page, $size, $uid, $goods_id);

        return $this->succeed($data);
    }

    /**
     * 提交评论
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function Comment(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'dis_type' => 'required|integer'
        ]);

        $dis_type = (int)$request->input('dis_type');
        $parent_id = (int)$request->input('parent_id');
        $quote_id = (int)$request->input('quote_id', 0);
        $dis_text = e($request->input('dis_text'));
        $reply_type = e($request->input('reply_type'));// 回复类型 回复他人
        $goods_id = (int)$request->input('goods_id');

        $uid = $this->authorization();
        if (!$uid) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->discoverService->Commnet($dis_type, $parent_id, $quote_id, $dis_text, $uid, $goods_id, $reply_type);

        return $this->succeed($data);
    }

    /**
     * 我的帖子
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function my(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'dis_type' => 'required|integer'
        ]);

        $dis_type = (int)$request->input('dis_type');
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);

        $uid = $this->authorization();

        if (!$uid) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->discoverService->My($dis_type, $page, $size, $uid);

        return $this->succeed($data);
    }

    /**
     * 回复我的
     * @param Request $request
     * @return JsonResponse
     */
    public function reply(Request $request)
    {
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);

        $uid = $this->authorization();

        $data = $this->discoverService->Reply($uid, $page, $size);

        return $this->succeed($data);
    }

    /**
     * 发帖显示
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        $goods_id = (int)$request->input('goods_id');

        $uid = $this->authorization();

        $data = $this->discoverService->Show($uid, $goods_id);

        return $this->succeed($data);
    }

    /**
     * 发帖
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(Request $request)
    {
        $goods_id = (int)$request->input('goods_id');
        $dis_type = (int)$request->input('dis_type');
        $title = e($request->input('title'));
        $content = e($request->input('content'));

        $uid = $this->authorization();

        if (!$uid) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->discoverService->Create($uid, $goods_id, $dis_type, $title, $content);

        return $this->succeed($data);
    }

    /**
     * 点赞
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function like(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'dis_type' => 'required|integer',
            'dis_id' => 'required|integer',
        ]);

        $dis_type = (int)$request->post('dis_type');
        $dis_id = (int)$request->post('dis_id');

        $uid = $this->authorization();

        if (!$uid) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->discoverService->like($uid, $dis_type, $dis_id);

        return $this->succeed($data);
    }

    /**
     * 删除帖子
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function delete(Request $request)
    {
        //数据验证
        $this->validate($request, [
            'dis_type' => 'required|integer',
            'dis_id' => 'required|integer',
        ]);

        $dis_type = (int)$request->get('dis_type');
        $dis_id = (int)$request->get('dis_id');

        $uid = $this->authorization();

        if (!$uid) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $data = $this->discoverService->DeleteMycom($uid, $dis_type, $dis_id);

        return $this->succeed($data);
    }

    /**
     * 发现 列表
     * @param Request $request
     * @return JsonResponse
     */
    public function findList(Request $request)
    {
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);

        $data = $this->discoverService->findList($page, $size);

        return $this->succeed($data);
    }

    /**
     * 发现 详情
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function findDetail(Request $request)
    {
        $this->validate($request, [
            'dis_id' => 'required|integer',
        ]);

        $dis_id = (int)$request->input('dis_id', 0);

        $uid = $this->authorization();

        $data = $this->discoverService->findDetail($dis_id);

        $data['my_picture'] = $uid ? $this->discoverService->getMyPicture($uid) : '';

        return $this->succeed($data);
    }

    /**
     * 发现 列表
     * @param Request $request
     * @return JsonResponse
     */
    public function findReplyComment(Request $request)
    {
        $this->validate($request, [
            'dis_id' => 'required|integer',
        ]);
        $page = (int)$request->input('page', 1);
        $size = (int)$request->input('size', 10);
        $dis_id = (int)$request->input('dis_id', 0);

        $data = $this->discoverService->findReplyCommentList($dis_id, $page, $size);

        return $this->succeed($data);
    }
}
