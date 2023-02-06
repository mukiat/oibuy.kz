<?php

namespace App\Repositories\Common;

use App\Kernel\Repositories\Common\StrRepository as Base;

/**
 * Class StrRepository
 * @method static msubstrEct($str = '', $start = 0, $length = 1, $charset = "utf-8", $suffix = '***', $position = 1) 字符串截取，支持中文和其他编码
 * @method static stringToStar($string = '', $num = 3, $start_len = '') 将字符串以 * 号格式显示 配合msubstr_ect函数使用
 * @method static studly($str = '') 将给定的字符串转换为 「变种驼峰命名」
 * @method static random($num = 0) 生成一个指定长度的随机字符串
 * @method static camel($str = '') 函数将给定字符串「蛇式」转化为 「驼峰式」示例：foo_bar转fooBar
 * @method static snake($str = '') 将给定的字符串转「驼峰式」换为「蛇式」示例：fooBar转foo_bar
 * @method static contains($content = '', $lookup = []) 判断给定的字符串是否包含给定的值（区分大小写）
 * @method static limit($str = '', $limit = 0) 按给定的长度截断给定的字符串
 * @method static lower($str = '') 将给定字符串转换为小写
 * @method static startsWith($name = '', $use = '') 判断给定的字符串的开头是否是指定值
 * @method static priceFormat($price = 0) 格式化价格带小数点，已带小数点的价格不建议使用这个方法，此方法不进行四舍五入作用
 * @method static after($content = '', $eliminate = '') 返回在字符串中指定值之后的所有内容
 * @method static before($content = '', $eliminate = '') 返回在字符串中指定值之前的所有内容
 * @method static replaceFirst($str = '', $replace = '', $replaceContent = '') 函数替换字符串中给定值的第一个匹配项
 * @method static replaceLast($str = '', $replace = '', $replaceContent = '') 函数替换字符串中最后一次出现的给定值
 * @package App\Repositories\Common
 */
class StrRepository extends Base
{
    /**
     * 页面编码为utf-8时使用，否则导出的中文为乱码
     * @param string $strInput
     * @return bool|string
     */
    public static function i($strInput = '')
    {
        if (empty($strInput)) {
            return '';
        }

        if (is_array($strInput)) {
            $strInput = implode(",", $strInput);
            $strInput = iconv('utf-8', 'gb2312//TRANSLIT//IGNORE', $strInput);

            return explode(',', $strInput);
        } else {
            return iconv('utf-8', 'gb2312//TRANSLIT//IGNORE', $strInput);
        }
    }

    /**
     * 生成用户名规则
     *
     * 长度限制最大15个字符 兼容UCenter用户名
     *
     * @param string $type
     * @param string $unionid
     * @return string
     */
    public static function generate_username($type = '', $unionid = '')
    {
        switch ($type) {
            case 'wechat':
                $prefix = 'wx';
                break;
            case 'wxapp':
                $prefix = 'wa';
                break;
            case 'qq':
                $prefix = 'qq';
                break;
            case 'weibo':
                $prefix = 'wb';
                break;
            case 'facebook':
                $prefix = 'fb';
                break;
            default:
                $prefix = 'sc';
                break;
        }

        $unionid = !empty($unionid) ? $unionid : str_random(28);
        return $prefix . substr(md5($unionid), -5) . substr(time(), 0, 4) . mt_rand(1000, 9999);
    }

    /**
     * 过滤微信昵称特殊字符
     * @param string $str
     * @return mixed|string
     */
    public static function filterSpecialCharacters($str = '')
    {
        $patterns = [
            '/[\xf0-\xf7].{3}/',
            '/[\x{1F600}-\x{1F64F}]/u',
            '/[\x{1F300}-\x{1F5FF}]/u',
            '/[\x{1F680}-\x{1F6FF}]/u',
            '/[\x{2600}-\x{26FF}]/u',
            '/[\x{2700}-\x{27BF}]/u',
        ];
        return preg_replace($patterns, '', $str);
    }
}
