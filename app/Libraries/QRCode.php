<?php

namespace App\Libraries;

use App\Repositories\Common\DscRepository;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Exception\QrCodeException;
use Endroid\QrCode\QrCode as BaseQrCode;

/**
 * Class QRCode
 * @package App\Libraries
 */
class QRCode
{
    /**
     * 生成二维码图片
     * @param string $url 二维码内容
     * @param string $img 二维码保存路径
     * @param null|string $logo 二维码LOGO
     * @param int $size 二维码尺寸
     * @param int $logoWidth 二维码LOGO宽度
     * @param int|null $logoHeight 二维码LOGO高度
     * @throws QrCodeException
     */
    public static function png($url, $img, $logo = null, $size = 300, int $logoWidth = 68, int $logoHeight = null)
    {
        /* 生成目录 */
        $dir = dirname($img);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $qrCode = self::init($url, $logo, $size, $logoWidth, $logoHeight);

        $qrCode->writeFile($img);
    }

    /**
     * 生成二维码图片流
     * @param string $url 二维码内容
     * @param null|string $logo 二维码LOGO
     * @param int $size 二维码尺寸
     * @param int $logoWidth 二维码LOGO宽度
     * @param int|null $logoHeight 二维码LOGO高度
     * @return string 二维码图片流
     * @throws QrCodeException
     */
    public static function stream($url, $logo = null, $size = 300, int $logoWidth = 68, int $logoHeight = null)
    {
        $qrCode = self::init($url, $logo, $size, $logoWidth, $logoHeight);

        header('Content-Type: ' . $qrCode->getContentType());
        return $qrCode->writeString();
    }

    /**
     * @param $url
     * @param null $logo
     * @param int $size
     * @param int $logoWidth
     * @param int|null $logoHeight
     * @return BaseQrCode
     * @throws QrCodeException
     */
    private static function init($url, $logo = null, $size = 300, int $logoWidth = 68, int $logoHeight = null)
    {
        $qrCode = new BaseQrCode($url);

        // Set options
        $qrCode->setSize($size);
        $qrCode->setWriterByName('png');
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
        $qrCode->setValidateResult(false);
        $qrCode->setMargin(0);

        if (!empty($logo)) {
            if (mb_substr($logo, 0, 4) === 'http') {
                $logoImg = storage_path('framework/temp/');
                app(DscRepository::class)->getHttpBasename($logo, $logoImg);
                $logo = $logoImg . basename($logo);
            }
            $qrCode->setLogoPath($logo);
            $qrCode->setLogoSize($logoWidth, $logoHeight);
        }

        return $qrCode;
    }
}
