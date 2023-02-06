<?php

namespace App\Services\User;

use App\Libraries\QRCode;
use App\Repositories\Common\DscRepository;
use think\Image;

/**
 * 我的分享
 * Class InviteService
 * @package App\Services\User
 */
class InviteService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 生成分享二维码
     *
     * @param int $user_id
     * @param string $platform
     * @param int $ru_id
     * @param int $type
     * @return array|bool
     * @throws \Exception
     */
    public function getInvite($user_id = 0, $platform = 'H5', $ru_id = 0, $type = 0)
    {
        $share = [];

        $affiliate = config('shop.affiliate', '');
        if ($affiliate) {
            $share = unserialize($affiliate);
        }

        if ($share && $share['on'] == 0) {
            return false;
        }

        // 二维码内容
        //$parent_id = base64_encode($user_id);
        $url = dsc_url('/#/home') . '?' . http_build_query(['parent_id' => $user_id], '', '&');

        //保存二维码目录
        $file_path = storage_public('data/attached/share_qrcode/');
        if (!is_dir($file_path)) {
            make_dir($file_path);
        }
        //二维码背景
        $qrcode_bg = public_path('img/affiliate.png');

        // 客户端来源 h5或小程序
        $from_type = $platform == 'MP-WEIXIN' ? 1 : 0;

        // 二维码
        $qr_code = $file_path . 'user_share_' . $from_type . '_' . $user_id . '_qrcode.png';
        // 输出图片
        $out_img = $file_path . 'user_share_' . $from_type . '_' . $user_id . '_bg.png';

        // 生成二维码条件
        $generate = false;
        if (file_exists($out_img)) {
            $lastmtime = filemtime($out_img) + 3600 * 24 * 30; // 30天有效期之后重新生成
            if (time() >= $lastmtime) {
                $generate = true;
            }
        }

        if (!file_exists($out_img) || $generate == true) {

            if (file_exists(MOBILE_WXAPP) && $platform == 'MP-WEIXIN') {
                // 生成小程序码
                $app_page = 'pages/index/index'; // 小程序、App链接

                // 推荐参数 $scene = 'parent_id='. $user_id;
                $scene = $user_id;
                $qr_path = str_replace(storage_public(), '', $qr_code);

                $wxacode = new \App\Modules\Wxapp\Libraries\Wxacode($ru_id, $type);
                $wxacode->unlimit($app_page, $qr_path, $scene, '280px');

            } else {
                // 生成二维码
                QRCode::png($url, $qr_code);
            }

            if (file_exists($qrcode_bg)) {
                // 背景图+二维码
                $bg_width = Image::open($qrcode_bg)->width(); // 背景图宽
                $bg_height = Image::open($qrcode_bg)->height(); // 背景图高

                if (file_exists($qr_code)) {
                    $logo_width = Image::open($qr_code)->width(); // logo图宽 300
                    $logo_height = Image::open($qr_code)->height(); // logo图高 300
                    Image::open($qrcode_bg)->water($qr_code, [($bg_width - $logo_width) / 2, $bg_height - $logo_height - 30], 100)->save($out_img);
                } else {
                    Image::open($qrcode_bg)->save($out_img);
                }
            }
        }

        $image_name = 'data/attached/share_qrcode/' . basename($out_img);

        $result = [];

        if (!empty($share['config']['separate_desc'])) {
            // html输出
            $separate_desc = str_replace(["\r\n", "\r", "\n"], '<br>', $share['config']['separate_desc']);
            $share['config']['separate_desc'] = html_out($separate_desc);
        }
        $result['share'] = $share;

        // 返回图片
        $result['img_src'] = $this->dscRepository->getImagePath($image_name);
        $result['file'] = $image_name;

        return $result;
    }
}
