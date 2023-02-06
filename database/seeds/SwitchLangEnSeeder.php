<?php

use Illuminate\Database\Seeder;
use JellyBool\Translug\Translug;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class SwitchLangEnSeeder extends Seeder
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var $translug
     */
    private $translug;

    /**
     * DeleteFileSeeder constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $config = [
            'appKey' => config('services.youdao.appKey'),
            'appSecret' => config('services.youdao.appSecret')
        ];
        $this->translug = new Translug($config);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*$this->switchEn();
        $this->switchManageEn();
        $this->pluginsSwitchEn();*/
    }

    /**
     * 转换前台语言包：中文转英文
     */
    public function switchEn()
    {
        $plugin = [
            'frontend' => 'lang/zh-CN/'
        ];

        $directory = [];
        foreach ($plugin as $key => $val) {
            $list = $this->filesystem->allFiles(resource_path($val));
            foreach ($list as $idx => $row) {
                $directory[$this->filesystem->dirname($row)][$idx] = $this->filesystem->dirname($row) . "/" . $this->filesystem->basename($row);
            }
        }

        if ($directory) {
            foreach ($directory as $dir_key => $dir_list) {
                if ($dir_list) {
                    foreach ($dir_list as $file_key => $file_value) {

                        $cache_name = $this->filesystem->basename($file_value);
                        $cache_name = str_replace('.php', '', $cache_name);

                        $cache_path = str_replace(resource_path('lang/zh-CN'), '', $dir_key);
                        $cache_path = ltrim($cache_path, '\\');

                        $_LANG = $this->readStaticCache($cache_path, $cache_name);

                        if ($_LANG === false) {
                            $_LANG = include_once($file_value);

                            var_dump($file_value);

                            $arr = $this->dscLang($_LANG);

                            $this->writeStaticCache($cache_path, $cache_name, $arr);
                        }
                    }
                }
            }
        }
    }

    /**
     * 转换前台语言包：中文转英文
     */
    public function switchManageEn()
    {
        $plugin = [
            'Modules/Admin/Languages/zh-CN/',
            'Modules/Seller/Languages/zh-CN/',
            'Modules/Stores/Languages/zh-CN/',
            'Modules/Suppliers/Languages/zh-CN/'
        ];

        $directory = [];
        foreach ($plugin as $key => $val) {
            $list = $this->filesystem->allFiles(app_path($val));
            foreach ($list as $idx => $row) {
                $directory[$this->filesystem->dirname($row)][$idx] = $this->filesystem->dirname($row) . "/" . $this->filesystem->basename($row);
            }
        }

        if ($directory) {
            foreach ($directory as $dir_key => $dir_list) {
                if ($dir_list) {
                    foreach ($dir_list as $file_key => $file_value) {

                        $cache_name = $this->filesystem->basename($file_value);
                        $cache_name = str_replace('.php', '', $cache_name);

                        $cache_path = str_replace(app_path('Modules'), '', $dir_key);
                        $cache_path = ltrim($cache_path, '\\');

                        $_LANG = $this->readStaticCache($cache_path, $cache_name, 'switch_manage_en/');

                        if ($_LANG === false) {
                            $_LANG = include_once($file_value);

                            var_dump($file_value);

                            $arr = $this->dscLang($_LANG);

                            $this->writeStaticCache($cache_path, $cache_name, $arr, 'switch_manage_en/');
                        }
                    }
                }
            }
        }
    }

    /**
     * 转换前台语言包：中文转英文
     */
    public function pluginsSwitchEn()
    {
        /* 更新插件语言 */
        $plugin = [
            'Connect',
            'Cron',
            'Payment',
            'Shipping'
        ];

        $arr = [];
        foreach ($plugin as $key => $val) {
            $directory = $this->filesystem->directories(plugin_path($val));

            if ($directory) {
                foreach ($directory as $idx => $row) {
                    $arr[$val][$idx] = $row . '/Languages/zh-CN.php';
                }
            }
        }

        if ($arr) {
            foreach ($arr as $dir_key => $dir_list) {
                if ($dir_list) {
                    foreach ($dir_list as $file_key => $file_value) {

                        $cache_name = $this->filesystem->basename($file_value);
                        $cache_name = str_replace('.php', '', $cache_name);

                        $cache_path = str_replace(plugin_path($dir_key), '', $file_value);
                        $cache_path = ltrim($cache_path, '\\');
                        $cache_path = str_replace('zh-CN.php', '', $cache_path);

                        $_LANG = $this->readStaticCache($cache_path, $cache_name, 'switch_plugins_en/' . $dir_key . '/');

                        if ($_LANG === false) {
                            $_LANG = include_once($file_value);

                            var_dump($file_value);

                            $arr = $this->dscLang($_LANG);

                            $this->writeStaticCache($cache_path, $cache_name, $arr, 'switch_plugins_en/' . $dir_key . '/');
                        }
                    }
                }
            }
        }
    }

    /**
     * 读结果缓存文件
     *
     * @param string $cache_path 读取文件目录路径
     * @param string $cache_name 读取文件目录文件名称
     * @param string $storage_path 存储文件目录路径
     * @param string $prefix $prefix 存储文件后缀
     * @return bool|mixed
     */
    private function readStaticCache($cache_path = '', $cache_name = '', $storage_path = 'switch_en/', $prefix = "php")
    {

        if (!Storage::disk('local')->exists($storage_path . $cache_path)) {
            Storage::disk('local')->makeDirectory($storage_path . $cache_path);
        }

        static $result = array();
        if (!empty($result[$cache_name]) && Storage::disk('local')->exists($storage_path . $cache_path . '/' . $cache_name . "." . $prefix)) {
            return $result[$cache_name];
        }

        if (Storage::disk('local')->exists($storage_path . $cache_path . '/' . $cache_name . "." . $prefix)) {

            $cache_file_path = storage_path('app/' . $storage_path . $cache_path . '/' . $cache_name . "." . $prefix);

            if (file_exists($cache_file_path)) {
                include_once($cache_file_path);
            } else {
                $_LANG = array();
            }

            $result[$cache_name] = $_LANG;
            return $result[$cache_name];
        } else {
            return false;
        }
    }

    /**
     * 写结果缓存文件
     *
     * @param string $cache_path 写入文件目录路径
     * @param string $cache_name 写入文件目录文件名称
     * @param string $caches 缓存数据
     * @param string $storage_path 存储文件目录路径
     * @param string $prefix 存储文件后缀
     */
    private function writeStaticCache($cache_path = '', $cache_name = '', $caches = '', $storage_path = 'switch_en/', $prefix = "php")
    {

        if (!Storage::disk('local')->exists($storage_path . $cache_path)) {
            Storage::disk('local')->makeDirectory($storage_path . $cache_path);
        }

        $cache_file_path = storage_path('app/' . $storage_path . $cache_path . '/' . $cache_name . "." . $prefix);

        $content = "<?php\r\n\r\n";
        $content .= "\$_LANG = " . var_export($caches, true) . ";\r\n";
        $content .= "\r\n\r\n";
        $content .= "return \$_LANG;\r\n";

        $cache_file_path = str_replace("//", '/', $cache_file_path);

        file_put_contents($cache_file_path, $content, LOCK_EX);
    }

    /**
     * 处理语言文件
     *
     * @param $_LANG
     * @return array
     */
    private function dscLang($_LANG)
    {
        $arr = [];
        foreach ($_LANG as $key => $value) {
            if ($value) {
                if (is_array($value)) {
                    foreach ($value as $idx => $row) {
                        if ($row) {
                            if (is_array($row)) {
                                foreach ($row as $jdx => $v) {
                                    if ($v) {
                                        if (is_array($v)) {
                                            foreach ($v as $i => $ival) {
                                                $arr[$key][$idx][$jdx][$i] = $this->translug->translate($ival);
                                                var_dump($arr[$key][$idx][$jdx][$i] . "**************" . '成功4');
                                            }
                                        } else {
                                            $arr[$key][$idx][$jdx] = $this->translug->translate($v);
                                            var_dump($arr[$key][$idx][$jdx] . "**************" . '成功1');
                                        }
                                    }
                                }
                            } else {
                                $arr[$key][$idx] = $this->translug->translate($row);
                                var_dump($arr[$key][$idx] . "**************" . '成功2');
                            }
                        }
                    }
                } else {
                    $arr[$key] = $this->translug->translate($value);
                    var_dump($arr[$key] . "**************" . '成功3');
                }
            }
        }

        return $arr;
    }
}