<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Libraries\QRCode;
use App\Repositories\Common\TimeRepository;
use Illuminate\Http\Request;


/**
 * Class QrcodeController
 * @package App\Api\Controllers
 */
class QrcodeController extends Controller
{
    /**
     * 二维码
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $info = $request->input('info', '');
        $size = $request->input('size', 188);

        return QRCode::stream($info, null, $size);
    }

    /**
     * 获取二维码
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function qrcodeUrl(Request $request)
    {
        $uid = $this->authorization();
        if (empty($uid)) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $info = $request->get('info', '');

        $time = TimeRepository::getGmTime();

        $data['qrcode_url'] = route('api.qrcode.index', ['info' => $info, 't' => $time]);

        return $this->succeed($data);
    }
    

}
