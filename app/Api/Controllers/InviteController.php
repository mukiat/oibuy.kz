<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Services\User\InviteService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * 我的分享
 * Class InviteController
 * @package App\Api\Controllers
 */
class InviteController extends Controller
{
    /**
     * @var InviteService
     */
    protected $inviteService;

    public function __construct(InviteService $inviteService)
    {
        $this->inviteService = $inviteService;
    }

    /**
     * 邀请
     * @param Request $request
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        // 数据验证
        $this->validate($request, [
            'platform' => 'filled|string',
            'ru_id' => 'filled|integer',
            'type' => 'filled|integer',
        ]);

        //获取会员id
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $platform = e($request->input('platform', 'H5')); // 来源 H5或小程序 MP-WEIXIN
        $ru_id = 0;//(int)$request->input('ru_id', 0);
        $type = (int)$request->input('type', 0);

        // 生成推荐分成二维码
        $invite_info = $this->inviteService->getInvite($user_id, $platform, $ru_id, $type);

        if (isset($invite_info['file']) && $invite_info['file']) {
            // 同步镜像上传到OSS
            $this->ossMirror($invite_info['file'], true);
        }

        return $this->succeed($invite_info);
    }
}
