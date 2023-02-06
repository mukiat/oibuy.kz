<?php

namespace App\Http\Controllers;

use App\Repositories\Common\DscRepository;
use Illuminate\Http\Request;

/**
 * 下载显示页
 *
 * Class DownloadController
 * @package App\Http\Controllers
 */
class DownloadController extends InitController
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 下载显示页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Support\Facades\View
     */
    public function index(Request $request)
    {
        if (config('shop.pc_download_open', 1) == 0) {
            // 关闭下载页 显示已关闭
            return response()->view('closed', ['close_comment' => trans('index.download_close_comment')]);
        }

        // 下载页图片
        $download_img = config('shop.pc_download_img', '');
        $download_img = !empty($download_img) ? $this->dscRepository->getImagePath($download_img) : '';
        $this->assign('download_img', $download_img);

        $this->assign('json_languages', json_encode(trans('js_languages.js_languages.common')));
        $this->assign('copyright', DscRepository::copyright());//底部版权信息展示
        return $this->display('download');
    }

}
