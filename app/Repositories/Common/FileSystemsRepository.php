<?php

namespace App\Repositories\Common;

use App\Extensions\Zipper;
use App\Kernel\Repositories\Common\FileSystemsRepository as Base;
use Illuminate\Support\Facades\Storage;

/**
 * Class FileSystemsRepository
 * @method static fileExists($file = '') 判断路径的文件是否存在
 * @method static dirExists($path = '', $make = 0) 判断路径的目录是否存在
 * @method static fileModeInfo() 文件或目录权限检查函数
 * @method static makeDir($folder) 检查目标文件夹是否存在，如果不存在则自动创建该目录
 * @package App\Repositories\Common
 */
class FileSystemsRepository extends Base
{
    /**
     * 下载压缩包文件
     *
     * @param string $dir
     * @param string $zip_name
     * @return bool|string
     * @throws \Exception
     */
    public static function download_zip($dir = '', $zip_name = '')
    {
        if (empty($dir)) {
            return false;
        }

        $file_path = storage_public($dir);
        if (!is_dir($file_path)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        // 压缩打包文件并下载
        $zip_path = storage_public($dir . 'zip/');
        if (!is_dir($zip_path)) {
            Storage::disk('public')->makeDirectory($dir . 'zip/');
        }

        $zipper = new Zipper();
        $files = glob($file_path . '*.*'); // 排除子目录

        if (empty($files)) {
            return false;
        }

        $zip_name = !empty($zip_name) ? $zip_name : date('YmdHis') . ".zip";

        $zip_file = $zip_path . $zip_name;

        $zipper->make($zip_file)->add($files)->close();

        if (file_exists($zip_file)) {
            // 删除文件
            $files = Storage::disk('public')->files($dir);
            Storage::disk('public')->delete($files);

            return $zip_file;
        }

        return false;
    }
}
