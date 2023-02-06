<?php

namespace App\Services\User;

use App\Models\Users;
use App\Models\UsersReal;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 会员实名认证
 * Class VerifyService
 * @package App\Services\User
 */
class VerifyService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 添加或者更新会员实名认证
     * @param array $info
     * @return bool
     */
    public function updateVerify($info = [])
    {
        if (empty($info)) {
            return false;
        }

        $info['real_name'] = e(trim($info['real_name']));
        $info['self_num'] = e(trim($info['self_num'])); // 身份证号
        $info['bank_mobile'] = e(trim($info['bank_mobile']));
        $info['bank_name'] = e(trim($info['bank_name']));
        $info['bank_card'] = e(trim($info['bank_card']));

        $info['add_time'] = TimeRepository::getGmTime();
        $info['review_status'] = 0;
        $info['review_content'] = 0;
        $info['user_type'] = 0;

        $info = BaseRepository::getArrayfilterTable($info, 'users_real');

        /* 获取会员是已添加实名认证信息 */
        $real_id = UsersReal::where('user_id', $info['user_id'])->where('user_type', 0)->value('real_id');
        $real_id = $real_id ? $real_id : 0;

        if ($real_id > 0) {
            // 更新指定记录
            UsersReal::where('real_id', $real_id)->where('user_id', $info['user_id'])->update($info);
        } else {
            if (isset($info['real_id'])) {
                unset($info['real_id']);
            }
            // 插入一条新记录
            UsersReal::insert($info);
        }
        return true;
    }

    /**
     * 会员实名认证详情
     * @param int $user_id
     * @return array
     */
    public function infoVerify($user_id = 0)
    {
        if (empty($user_id)) {
            return [];
        }

        $res = UsersReal::where('user_id', $user_id)->first();
        $res = $res ? $res->toArray() : [];

        if (!empty($res)) {
            $res['front_of_id_card'] = $this->dscRepository->getImagePath($res['front_of_id_card']);
            $res['reverse_of_id_card'] = $this->dscRepository->getImagePath($res['reverse_of_id_card']);
            $res['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $res['add_time']);
            $res['self_num_encryption'] = substr($res['self_num'], 0, 4) . '**********' . substr($res['self_num'], -4);
            $user_picture = Users::where('user_id', $user_id)->value('user_picture');
            $res['avatar'] = $this->dscRepository->getImagePath($user_picture);
        }

        return $res;
    }
}
