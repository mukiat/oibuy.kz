<?php

namespace App\Extensions;

use App\Libraries\Http;
use App\Libraries\QRCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use think\Image;

/**
 * 生成海报类 H5端
 * Class SharePoster
 * @package App\Extensions
 */
class SharePoster
{
    const DATA_PATH = 'data/attached/';

    protected $file_dir = ''; // 分享图生成所在主目录
    protected $logo_dir = ''; // 二维码logo图目录
    protected $thumb_dir = ''; // 缩略图目录

    protected $background_image_path = ''; // 背景图路径
    protected $thumb_image = ''; // 缩略图路径
    protected $qrcode_image = ''; // 二维码路径
    protected $logo_image = ''; // 二维码logo图片路径
    protected $out_image = ''; // 最终分享图片路径

    protected $unique_suffix = ''; // 生成最终文件名后缀
    protected $suffix = ''; // 生成logo、thumb文件名后缀 （文件可复用）

    protected $bg_width = 0, $bg_height = 0; // 背景图宽度、高度

    protected $fonts_path = 'data/attached/fonts/msyh.ttf';// 字体文件路径
    protected $font_color = "#333333"; //

    protected $thumb_x = 0, $thumb_y = 0; // 缩略图相对坐标
    protected $currency_format_x = 0, $currency_format_y = 0; // 货币符号相对坐标
    protected $price_x = 0, $price_y = 0; // 价格相对坐标
    protected $title_x = 0, $title_y = 0; // 标题相对坐标
    protected $qrcode_x = 0, $qrcode_y = 0; // 二维码相对坐标

    protected $currency_format = '￥'; // 货币符号
    protected $price = ''; // 价格
    protected $title = ''; // 标题
    protected $code_url = ''; // 二维码链接

    public function __construct(int $user_id = 0, int $goods_id = 0, string $extension_code = '', int $share_type = 0)
    {
        // 分享图生成所在主目录
        $this->file_dir = storage_public(self::DATA_PATH . 'goods_share/');
        if (!file_exists($this->file_dir)) {
            mkdir($this->file_dir, 0755, true);
        }

        // logo图目录
        $this->logo_dir = $this->file_dir . 'logo/';
        if (!file_exists($this->logo_dir)) {
            mkdir($this->logo_dir, 0755, true);
        }
        // 缩略图目录
        $this->thumb_dir = $this->file_dir . 'goods_thumb/';
        if (!file_exists($this->thumb_dir)) {
            mkdir($this->thumb_dir, 0755, true);
        }

        $this->init($user_id, $goods_id, $extension_code, $share_type);
    }

    /**
     * init
     * @param int $user_id
     * @param int $goods_id
     * @param string $extension_code
     * @param int $share_type
     */
    protected function init(int $user_id = 0, int $goods_id = 0, string $extension_code = '', int $share_type = 0)
    {
        // 字体文件目录
        $this->fonts_path = storage_public($this->fonts_path);

        // 客户端来源 0 h5, 1 小程序
        $from_type = 0;

        // 生成文件名后缀
        $extension_code = $extension_code ? $extension_code . '_' : '';
        $this->unique_suffix = $extension_code . $share_type . $from_type . $goods_id . $user_id;
        $this->suffix = $extension_code . $goods_id;

        // 二维码
        $this->qrcode_image = $this->file_dir . 'goods_qrcode_' . $this->unique_suffix . '.png';
        // 输出logo
        $this->logo_image = $this->logo_dir . 'logo_' . $this->suffix . '.png';
        // 输出图片
        $this->out_image = $this->file_dir . 'goods_share_' . $this->unique_suffix . '.png';
    }

    /**
     * @param string $background_image_path
     */
    public function setBackgroundImagePath(string $background_image_path)
    {
        $this->background_image_path = $background_image_path;
    }

    /**
     * @param string $thumb_image
     * @param int $thumb_y
     */
    public function setThumbImage(string $thumb_image, int $thumb_y = 110)
    {
        $this->thumb_image = $thumb_image;
        $this->thumb_y = $thumb_y;
    }

    /**
     * @param string $logo_image
     */
    public function setLogoImage(string $logo_image)
    {
        $this->logo_image = $logo_image;
    }

    /**
     * @param string $title
     * @param int $title_x
     * @param int $title_y
     * @param int $cut
     */
    public function setTitle(string $title, int $title_x = 65, int $title_y = 35, int $cut = 48)
    {
        // 第一行文字长度 超出换行
        $first_line = Str::limit($title, $cut, '');
        $two_line = Str::limit($title, $cut + 30); // 第二行文字
        if ($two_line) {
            $two_line = Str::replaceFirst($first_line, '', $two_line);
        }

        if (empty($two_line)) {
            $title = Str::limit($title, $cut, '...');
        } else {
            $title = $first_line . "\r\n" . $two_line;
        }

        $this->title = $title;
        $this->title_x = $title_x;
        $this->title_y = $title_y;
    }

    /**
     * @param string $currency_format
     * @param int $currency_format_x
     */
    public function setCurrencyFormat(string $currency_format, int $currency_format_x = 65)
    {
        $this->currency_format = $currency_format;
        $this->currency_format_x = $currency_format_x;
    }

    /**
     * @param string $price
     * @param int $price_x
     * @param int $price_y
     */
    public function setPrice(string $price, int $price_x = 25, int $price_y = 30)
    {
        $price = strip_tags($price);
        $price = str_replace($this->currency_format, '', $price);

        $this->price = $price;
        $this->price_x = $price_x;
        $this->price_y = $price_y;
    }

    /**
     * @param string $code_url
     */
    public function setCodeUrl(string $code_url)
    {
        $this->code_url = $code_url;
    }

    /**
     * @return string
     */
    public function getCurrencyFormat(): string
    {
        return $this->currency_format;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getCodeUrl(): string
    {
        return $this->code_url;
    }

    public function createOutImage()
    {
        if ($this->generate() == true) {
            // 1. 生成背景图
            $this->createBackgroundImage();
            // 2. 生成缩略图
            $this->createThumbImage();
            // 3. 添加价格
            $this->appendPrice();
            // 4. 添加标题
            $this->appendTitle();

            // 5. 生成二维码
            $this->createQrcodeLogo();

            // 6. 最后生成 商品图+二维码
            $this->createQrcodeImage();
        }

        return self::DATA_PATH . 'goods_share/' . basename($this->out_image);
    }

    /**
     * 生成二维码条件
     * 1. 图片不存在
     * 2. 生成图片后 一天有效期过期
     * @return bool
     */
    protected function generate()
    {
        // 生成二维码条件: true 需要生成 false 不需要生成
        $generate = true;

        if (config('shop.open_oss') == 1) {
            $disk = File::diskFile();
            $out_file_name = str_replace(storage_public(), '', $this->out_image);
            $exist_oss = Storage::disk($disk)->exists($out_file_name);
            if ($exist_oss) {
                $lastmtime = Storage::disk($disk)->lastModified($out_file_name) + 3600 * 24 * 1; // 1天有效期之后重新生成;
                $generate = time() >= $lastmtime;
            }
        } else {
            if (file_exists($this->out_image)) {
                $lastmtime = filemtime($this->out_image) + 3600 * 24 * 1; // 1天有效期之后重新生成
                $generate = time() >= $lastmtime;
            }
        }

        return $generate;
    }

    // 1 生成背景图
    protected function createBackgroundImage()
    {
        $this->bg_width = $bg_width = Image::open($this->background_image_path)->width(); // 背景图宽
        $this->bg_height = $bg_height = Image::open($this->background_image_path)->height(); // 背景图高

        Image::open($this->background_image_path)->save($this->out_image);

        return $this;
    }

    // 2 生成缩略图
    protected function createThumbImage()
    {
        // 缩略图url
        $thumb_image = $this->thumb_image;
        // 保存缩略图路径
        $thumb_image_path = $this->thumb_dir . $this->suffix . basename($this->thumb_image);

        // 生成商品图缩略图
        if (!file_exists($thumb_image_path)) {
            // 远程图片（非本站）
            if (strtolower(substr($thumb_image, 0, 4)) == 'http' && strpos($thumb_image, url('/')) === false) {
                $contents = Http::doGet($thumb_image);

                if (empty($contents)) {
                    $contents = file_get_contents($thumb_image);
                }

                $thumb = $thumb_image_path;
                Storage::disk('public')->put(str_replace(storage_public(), '', $thumb_image_path), $contents);
            } else {
                // 本站图片 带http 或 不带http
                if (strtolower(substr($thumb_image, 0, 4)) == 'http') {
                    $thumb_image = str_replace(storage_url('/'), '', $thumb_image);
                }

                $thumb = storage_public($thumb_image);
            }

            if (file_exists($thumb)) {
                Image::open($thumb)->thumb($this->bg_width, $this->bg_width, Image::THUMB_FILLED)->save($thumb_image_path);
            }
        }

        // 商品缩略图 上边距
        if (file_exists($thumb_image_path)) {
            Image::open($this->out_image)->water($thumb_image_path, [0, $this->thumb_y], 100)->save($this->out_image);
        }

        return $this;
    }

    //  商品价格
    protected function appendPrice(string $price_color = '#EC5151')
    {
        // 价格最终y坐标 = 缩略图上边距 + 缩略图高度 + 价格上边距
        $price_top = $this->thumb_y + $this->bg_width + $this->price_y;

        // 货币符号
        if (!empty($this->currency_format)) {
            Image::open($this->out_image)->text($this->currency_format, $this->fonts_path, 25, $price_color, [$this->currency_format_x, $price_top + 8])->save($this->out_image);
        }

        // 价格
        if (!empty($this->price)) {
            Image::open($this->out_image)->text($this->price, $this->fonts_path, 35, $price_color, [$this->currency_format_x + $this->price_x, $price_top])->save($this->out_image);
        }

        return $this;
    }

    //  商品名称
    protected function appendTitle()
    {
        if (!empty($this->title)) {
            // 标题文字最终y坐标 = 价格最终y坐标 + 文字y坐标
            $price_top = $this->thumb_y + $this->bg_width + $this->price_y;
            $title_top = $price_top + $this->title_y;
            Image::open($this->out_image)->text($this->title, $this->fonts_path, 25, $this->font_color, [$this->title_x, $title_top])->save($this->out_image);
        }

        return $this;
    }

    /**
     * 生成logo
     * @param int $logo_size
     * @return string
     */
    protected function createLogoImage(int $logo_size = 60)
    {
        // logo url
        $logo_image = $this->logo_image;
        // 保存logo图路径
        $logo_image_path = $this->logo_dir . $this->suffix . basename($this->logo_image);

        // 生成logo图
        if (!file_exists($logo_image_path)) {
            // 远程图片（非本站）
            if (strtolower(substr($logo_image, 0, 4)) == 'http' && strpos($logo_image, url('/')) === false) {
                $contents = Http::doGet($logo_image);

                if (empty($contents)) {
                    $contents = file_get_contents($logo_image);
                }

                $avatar = $logo_image_path;
                Storage::disk('public')->put(str_replace(storage_public(), '', $logo_image_path), $contents);
            } else {
                // 本站图片 带http 或 不带http
                if (strtolower(substr($logo_image, 0, 4)) == 'http') {
                    $logo_image = str_replace(storage_url('/'), '', $logo_image);
                }

                $avatar = storage_public($logo_image);
            }

            if (file_exists($avatar)) {
                Image::open($avatar)->thumb($logo_size, $logo_size, Image::THUMB_FILLED)->save($logo_image_path);
            }
        }

        return $logo_image_path;
    }

    // 生成二维码
    protected function createQrcodeLogo(int $qr_size = 287)
    {
        $url = $this->code_url;

        // 生成logo
        $logo_image_path = $this->createLogoImage();

        // 生成二维码+logo
        if (file_exists($logo_image_path)) {
            QRCode::png($url, $this->qrcode_image, $logo_image_path, $qr_size);
        } else {
            // 无logo
            QRCode::png($url, $this->qrcode_image, null, $qr_size);
        }

        return $this;
    }

    // 生成 商品图+二维码
    protected function createQrcodeImage()
    {
        if (file_exists($this->qrcode_image)) {
            // 二维码坐标
            $logo_width = Image::open($this->qrcode_image)->width(); // logo图宽
            $logo_height = Image::open($this->qrcode_image)->height(); // logo图高

            // 二维码居中
            $qr_left = ($this->bg_width - $logo_width) / 2;

            // 二维码最终y坐标 = 标题文字最终y坐标
            $price_top = $this->thumb_y + $this->bg_width + $this->price_y;
            $title_top = $price_top + $this->title_y;
            $this->qrcode_y = $title_top + 130;

            if (file_exists($this->out_image)) {
                Image::open($this->out_image)->water($this->qrcode_image, [$qr_left, $this->qrcode_y], 100)->save($this->out_image);
            }
        }
    }

    /**
     * 清空所有海报图
     * @return bool
     */
    public static function removeShareImages()
    {
        $imgList = [];
        $goods_share_dir = 'data/attached/goods_share/';
        $goods_share_themes = storage_public($goods_share_dir);
        if (is_dir($goods_share_themes)) {
            // 删除子目录
            $directories = Storage::disk('public')->directories($goods_share_dir);
            foreach ($directories as $directory) {
                Storage::disk('public')->deleteDirectory($directory);
            }

            // 删除文件
            $goods_share_path = Storage::disk('public')->files($goods_share_dir);
            $goods_share_path = array_values(array_diff($goods_share_path, ['..', '.'])); // 过滤

            $ext = ['png', 'jpg', 'jpeg'];
            foreach ($goods_share_path as $item) {
                $extensions = strtolower(pathinfo($item, PATHINFO_EXTENSION)); // 文件扩展名
                if (in_array($extensions, $ext)) {
                    $imgList[] = $item; //把符合条件的文件存入数组
                }
            }
        }

        if (!empty($imgList)) {
            // 分块处理 每次1000
            foreach (collect($imgList)->chunk(1000) as $chunk) {
                $chunk = $chunk ? $chunk->toArray() : [];
                File::remove($chunk);
            }
            return true;
        }

        return false;
    }
}
