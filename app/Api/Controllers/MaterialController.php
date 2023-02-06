<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Extensions\File;
use App\Repositories\Common\DscRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MaterialController
 * @package App\Api\Controllers
 */
class MaterialController extends Controller
{
    /**
     * @var DscRepository
     */
    private $dscRepository;

    /**
     * MaterialController constructor.
     * @param DscRepository $dscRepository
     */
    public function __construct(DscRepository $dscRepository)
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 保存素材图片
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function uploads(Request $request)
    {
        $uid = $this->authorization();
        if (!$uid) {
            return $this->failed('permission denied');
        }

        /**
         * 功能：文件上传，支持file文件和base64字符串上传
         * file类型：支持单一文件上传
         * base64类型：支持多文件上传
         */

        $type = 'image'; // 上传类型 : image、video

        $savePath = 'uploads/' . $type . '/';

        $urls = File::api_upload($savePath, $type, $uid);

        if (!empty($urls)) {
            $this->dscRepository->getOssAddFile($urls);

            foreach ($urls as $key => $url) {
                $urls[$key] = $this->dscRepository->getImagePath($url);
            }

            return $this->succeed($urls);
        }

        return $this->failed('upload fail');
    }

    /**
     * 保存素材视频
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function video(Request $request)
    {
        $uid = $this->authorization();
        if (!$uid) {
            return $this->failed('permission denied');
        }

        /**
         * 功能：文件上传，支持file文件和base64字符串上传
         * file类型：支持单一文件上传
         * base64类型：支持多文件上传
         */

        $type = 'video'; // 上传类型 : image、video

        $savePath = 'uploads/' . $type . '/';

        $urls = File::api_upload($savePath, $type, $uid);

        if (!empty($urls)) {
            $this->dscRepository->getOssAddFile($urls);

            foreach ($urls as $key => $url) {
                $urls[$key] = $this->dscRepository->getImagePath($url);
            }

            return $this->succeed($urls);
        }

        return $this->failed('upload fail');
    }

}
