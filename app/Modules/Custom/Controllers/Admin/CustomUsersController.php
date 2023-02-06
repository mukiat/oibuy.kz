<?php

namespace App\Modules\Custom\Controllers\Admin;

use App\Models\Article;
use App\Modules\Admin\Controllers\InitController as Controller;
use App\Modules\Custom\Models\CustomConfig;
use App\Modules\Custom\Models\LogoutReason;
use App\Modules\Custom\Models\LogoutUser;
use App\Modules\Custom\Traits\CustomTrait;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;
use Illuminate\Http\Request;

/**
 * 会员注销相关控制器
 * Class CustomUserController
 * @package App\Modules\Admin\Controllers
 */
class CustomUsersController extends Controller
{
    use CustomTrait;

    // 分页数量
    protected $page_num = 10;

    public function __construct() {}

    protected function initialize()
    {
        // 初始化 每页分页数量
        $this->init_params();
    }

    /**
     * index
     */
    public function index(Request $request)
    {
        // 提交处理
        if ($request->isMethod('POST')) {
            $article_id = $request->input('article_id', 0);
            if ($article_id) {
                $data['value'] = $article_id;
                $data['group'] = 'user_logout';
                $data['code'] = 'article_id';
                $data['update_time'] = TimeRepository::getGmTime();
                CustomConfig::query()->updateOrCreate(['group' => 'user_logout', 'code' => 'article_id'], $data);
            }
            return $this->message('更新成功');
        }
        $article = Article::query()->select('article_id', 'title')
            ->where('is_open', 1)
            ->orderBy('sort_order', 'desc');
        $article = BaseRepository::getToArrayGet($article);
        $article_id = CustomConfig::query()->where('code', 'article_id')
                                            ->where('group', 'user_logout')
                                            ->value('value') ?? 0;
        $this->assign('article', $article);
        $this->assign('article_id', $article_id);
        return $this->display();
    }

    /**
     * 注销原因列表
     */
    public function reason(Request $request)
    {
        // 搜索
        $search_keywords = $request->input('search_keywords', '');

        $filter['search_keywords'] = $search_keywords;
        $offset = $this->pageLimit(route('admin/custom/users/reason', $filter), $this->page_num);

        // 列表
        $model = LogoutReason::query();

        if (!empty($filter)) {
            // 搜索名称
            $search_keywords = $filter['search_keywords'] ?? '';
            if (!empty($search_keywords)) {
                $model = $model->where('reason_name', 'like', "%" . $search_keywords . "%");
            }
        }

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $model = $model->orderBy('id', 'DESC')->get();

        $list = $model ? $model->toArray() : [];

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('filter', $filter);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('custom::admin.library.reason_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display();
    }

    /**
     * 注销原因添加/编辑
     */
    public function reason_edit(Request $request)
    {
        $id = $request->input('id', 0);
        // 提交处理
        if ($request->isMethod('POST')) {
            $data = $request->input('data', []);
            if ($id) {
                $transformerData = [
                    'reason_name' => $data['reason_name'],
                    'update_time' => TimeRepository::getGmTime(),
                ];
                LogoutReason::query()->where('id', $id)->update($transformerData);
            } else {
                $transformerData = [
                    'reason_name' => $data['reason_name'],
                    'create_time' => TimeRepository::getGmTime(),
                ];
                LogoutReason::query()->create($transformerData);
            }
            return $this->message('操作成功');
        }
        $info = LogoutReason::query()->where('id', $id);
        $info = BaseRepository::getToArrayFirst($info);
        $this->assign('info', $info);
        $this->assign('id', $id);
        return $this->display();
    }

    /**
     * 注销原因列表
     */
    public function reason_delete(Request $request)
    {
        $id = $request->input('id', 0);
        $localDeleteStatus = LogoutReason::query()->where('id', $id)->delete();

        if ($localDeleteStatus) {
            return response()->json(['error' => 0, 'msg' => lang('admin/common.delete') . lang('admin/common.success')]);
        }

        return response()->json(['error' => 1, 'msg' => lang('admin/common.delete') . lang('admin/common.fail')]);
    }

    /**
     * 注销用户列表
     */
    public function logout(Request $request)
    {
        // 搜索
        $search_keywords = $request->input('search_keywords', '');

        $filter['search_keywords'] = $search_keywords;
        $offset = $this->pageLimit(route('admin/custom/users/logout', $filter), $this->page_num);

        // 列表
        $model = LogoutUser::query();

        if (!empty($filter)) {
            // 搜索名称
            $search_keywords = $filter['search_keywords'] ?? '';
            if (!empty($search_keywords)) {
                $model = $model->where('user_name', 'like', "%" . $search_keywords . "%")
                                ->orWhere('mobile', 'like', "%" . $search_keywords . "%");
            }
        }

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $model = $model->orderBy('id', 'DESC')->get();

        $list = $model ? $model->toArray() : [];

        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['create_time']);
            }
        }

        $page = $this->pageShow($total);
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('filter', $filter);

        $is_ajax = $request->input('is_ajax');
        if ($is_ajax == 1) {
            $respond['content'] = $this->fetch('custom::admin.library.logout_query');
            $respond['filter'] = $filter;
            $respond['page_count'] = $page['page_count'] ?? 1;
            return response()->json($respond);
        }

        return $this->display();
    }
}
