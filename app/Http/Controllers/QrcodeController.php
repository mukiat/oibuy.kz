<?php

namespace App\Http\Controllers;

use App\Libraries\QRCode;

/**
 * 动态输出二维码文件流
 *
 * Class QrcodeController
 * @package App\Http\Controllers
 */
class QrcodeController extends InitController
{
    public function index()
    {
        $code_url = request()->input('code_url', '');
        $size = request()->input('size', 188);

        return QRCode::stream($code_url, null, $size);
    }
}
