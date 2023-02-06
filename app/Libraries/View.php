<?php

namespace App\Libraries;

use Exception;
use Illuminate\Support\Facades\Blade;
use Throwable;

/**
 * Class View
 * @package App\Libraries
 */
class View extends Template
{
    /**
     * @param string $filename
     * @param string $cache_id
     * @return array|string|null
     * @throws Throwable
     */
    public function fetch($filename = '', $cache_id = '')
    {
        if (substr($filename, 0, 4) == 'str:') {
            $content = substr($filename, 4);
            $content = $this->parseReg($content);
            return $this->parseStr($content, $this->_var);
        }

        $filename = str_replace(['.dwt', '.lbi'], '', ltrim($filename, '/'));

        return view('frontend::' . $filename, $this->_var)->render();
    }

    /**
     * @param string $filename
     * @param string $cache_id
     * @return array|string|null
     * @throws Throwable
     */
    public function display($filename = '', $cache_id = '')
    {
        return $this->fetch($filename);
    }

    /**
     * @param $content
     * @return string|string[]|null
     */
    private function parseReg($content)
    {
        $preg_rules = [
            // variable label
            '/\$(\w+)\.(\w+)\.(\w+)\.(\w+)\.(\w+)\.(\w+)/is' => "\$\\1['\\2']['\\3']['\\4']['\\5']['\\6']",
            '/\$(\w+)\.(\w+)\.(\w+)\.(\w+)\.(\w+)/is' => "\$\\1['\\2']['\\3']['\\4']['\\5']",
            '/\$(\w+)\.(\w+)\.(\w+)\.(\w+)/is' => "\$\\1['\\2']['\\3']['\\4']",
            '/\$(\w+)\.(\w+)\.(\w+)/is' => "\$\\1['\\2']['\\3']",
            '/\$(\w+)\.(\w+)/is' => "\$\\1['\\2']",
            '/{(\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*)}/i' => "{{ \\1 }}",
        ];

        foreach ($preg_rules as $k => $v) {
            $content = preg_replace($k, $v, $content);
        }

        return $content;
    }

    /**
     * @param $string
     * @param $data
     * @return false|string
     * @throws Exception
     */
    private function parseStr($string, $data)
    {
        $str = Blade::compileString($string);

        ob_start() and extract($data, EXTR_SKIP);
        try {
            eval('?>' . $str);
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }
}
