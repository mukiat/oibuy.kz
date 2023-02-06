<?php

namespace App\Modules\Custom\Controllers\Api;

use App\Api\Foundation\Controllers\Controller;
use App\Models\Article;
use App\Models\Users;
use App\Modules\Custom\Models\CustomConfig;
use App\Modules\Custom\Models\LogoutReason;
use App\Modules\Custom\Models\LogoutUser;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomUserController extends Controller
{
    /**
     * 注销文章
     * @return \Illuminate\Http\JsonResponse
     */
    public function article()
    {
        $article = [];
        $article_id = CustomConfig::query()->where('code', 'article_id')
                ->where('group', 'user_logout')
                ->value('value') ?? 0;
        if ($article_id) {
            $article = Article::query()->where('article_id', $article_id)
                ->where('is_open', 1);
            $article = BaseRepository::getToArrayFirst($article);
        }

        return $this->succeed($article);
    }

    /**
     * 注销原因
     * @return \Illuminate\Http\JsonResponse
     */
    public function reason()
    {
        $reasons = LogoutReason::query()
            ->select('id', 'reason_name')
            ->orderBy('create_time', 'desc');
        $reasons = BaseRepository::getToArrayGet($reasons);

        return $this->succeed($reasons);
    }

    /**
     * 注销用户
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $id = (int)$request->input('id', 0);
        $user_id = $this->authorization();
        if ($id && $user_id) {
            $logout_reason = LogoutReason::query()->where('id', $id)->value('reason_name') ?? '';
            $user = Users::query()->where('user_id', $user_id);
            $user = BaseRepository::getToArrayFirst($user);
            if (!empty($user)) {
                DB::beginTransaction(); // 开启事务
                try {
                    $data = [
                        'user_name' => $user['user_name'] ?? '',
                        'nick_name' => $user['nick_name'] ?? '',
                        'mobile' => $user['mobile_phone'] ?? '',
                        'logout_reason' => $logout_reason,
                        'create_time' => TimeRepository::getGmTime(),
                    ];
                    LogoutUser::query()->create($data);

                    /* 通过插件来删除用户 */
                    $users = init_users();
                    $users->remove_user($user['user_name']); //删除用户所有数据

                    DB::commit(); // 提交事务
                }catch (\Exception $e) {
                    DB::rollBack(); // 回滚事务
                }
                return $this->succeed('success');
            }
        }

        // return $this->failed('fail');
		return $this->succeed('success');
    }
}
