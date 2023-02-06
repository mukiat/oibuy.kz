<?php

namespace App\Modules\Admin\Controllers;

use App\Extensions\File;
use App\Repositories\Export\ExportHistoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class ExportHistoryController
 * @package App\Modules\Admin\Controllers
 */
class ExportHistoryController extends BaseController
{
    protected $ru_id = 0;

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 文件导出记录
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'order');

        // 查询队列的数据状态，处理已完成的队列显示下载按钮
        $export_history = ExportHistoryRepository::export_list($this->ru_id, $type);

        $this->assign('type', $type);
        $this->assign('export_history', $export_history);
        $this->assign('callback', urldecode($request->get('callback')));
        return $this->display('admin.export.index');
    }

    /**
     * 导出数据下载
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function download(Request $request)
    {
        $id = $request->get('id');
        if (is_null($id)) {
            return $this->message('id empty', null, 2);
        }

        $item = ExportHistoryRepository::export_info($this->ru_id, $id, ['download_url']);

        $file_name = $item->download_url . '.xls';
        if (config('shop.open_oss') == 1 && !Storage::disk('public')->exists($file_name)) {
            File::batchDownloadOss(['0' => $file_name]);
        }
        // 判断文件是否存在
        if (Storage::disk('public')->exists($file_name)) {
            // 开始文件下载
            $file = Storage::disk('public')->path($file_name);
            return response()->download($file);
        }

        return $this->message(trans('common.not_data'), null, 2);
    }

    /**
     * 文件删除操作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function delete(Request $request)
    {
        $id = $request->get('id');
        if (is_null($id)) {
            return response()->json(['error' => 1, 'msg' => 'id empty']);
        }

        $item = ExportHistoryRepository::export_info($this->ru_id, $id, ['download_url']);

        // 删除文件
        File::remove($item->download_url . '.xls');

        // 删除记录
        ExportHistoryRepository::delete($this->ru_id, $id);

        return response()->json(['error' => 0, 'msg' => trans('admin/common.success')]);
    }
}
