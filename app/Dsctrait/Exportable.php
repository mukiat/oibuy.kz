<?php

namespace App\Dsctrait;

use App\Services\Common\OfficeService;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class Exportable
 * @package App\Dsctrait
 */
trait Exportable
{
    /**
     * 导出 Excel 文件
     * @param string $name 文件名
     * @param array $head 列头
     * @param array $fields 定义导出列
     * @param array $data 导出数据
     * @param array $options 导出选项
     */
    private function fileWrite($name, $head, $fields, $data, $options)
    {
        $spreadsheet = new OfficeService();

        try {
            $spreadsheet->setDefaultStyle();
            $spreadsheet->exportExcel($name, $head, $fields, $data, $options);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        // 关闭
        $spreadsheet->disconnect();
    }
}
