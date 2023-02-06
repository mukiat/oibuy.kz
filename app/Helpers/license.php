<?php

use App\Libraries\Transport;
use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\SessionRepository;

/**
 * 获得网店 license 信息
 *
 * @access  public
 * @param integer $size
 *
 * @return  array
 */
function get_shop_license()
{
    // 取出网店 license
    $license_info = ShopConfig::whereIn('code', ['certificate_id', 'token', 'certi'])
        ->take(3);

    $license_info = BaseRepository::getToArrayGet($license_info);

    $license = [];
    if ($license_info) {
        foreach ($license_info as $value) {
            $license[$value['code']] = $value['value'];
        }
    }

    return $license;
}

/**
 * 功能：生成certi_ac验证字段
 * @param string     POST传递参数
 * @param string     证书token
 * @return  string
 */
function make_shopex_ac($post_params, $token)
{
    if (!is_array($post_params)) {
        return;
    }

    // core
    ksort($post_params);
    $str = '';
    foreach ($post_params as $key => $value) {
        if ($key != 'certi_ac') {
            $str .= $value;
        }
    }

    return md5($str . $token);
}

/**
 * 功能：处理登录返回结果
 *
 * @param array $cert_auth 登录返回的用户信息
 * @return  array
 */
function process_login_license($cert_auth)
{
    if (!is_array($cert_auth)) {
        return [];
    }

    $cert_auth['auth_str'] = trim($cert_auth['auth_str']);
    if (!empty($cert_auth['auth_str'])) {
        $cert_auth['auth_str'] = $GLOBALS['_LANG']['license_' . $cert_auth['auth_str']];
    }

    $cert_auth['auth_type'] = trim($cert_auth['auth_type']);
    if (!empty($cert_auth['auth_type'])) {
        $cert_auth['auth_type'] = $GLOBALS['_LANG']['license_' . $cert_auth['auth_type']];
    }

    return $cert_auth;
}

/**
 * 功能：license 登录
 *
 * @param string $certi_added
 */
function license_login($certi_added = '')
{

}

/**
 * 功能：license 注册
 *
 * @param string $certi_added
 */
function license_reg($certi_added = '')
{

}
