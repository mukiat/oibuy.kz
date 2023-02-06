<?php

namespace App\Modules\Stores\Controllers;

use App\Api\Foundation\Components\ApiResponse;
use Ecjia\Component\AutoLogin\AuthEncrypter;
use Ecjia\Component\AutoLogin\AuthLoginDecrypt;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Class OAuthController
 * @package App\Modules\Stores\Controllers
 */
class OAuthController extends Controller
{
    use ApiResponse;

    /**
     * @param Request $request
     * @return string
     */
    public function shopKeeperByEcjia(Request $request)
    {
        $authCode = $request->get('code');
        $callback = urldecode($request->get('redirect', '/home'));
        $redirectUrl = $request->root() . '/client/store/#' . $callback;

        $storeUserId = $this->decodeByEcjia($authCode);
        $storeUserJwt = $this->JWTEncode(['store_user_id' => $storeUserId]);

        return <<<EOF
<script type="text/javascript">
let TOKEN_NAME = 'dscmd_token';
let JWT = '{$storeUserJwt}';
let redirectTo = '{$redirectUrl}';

window.localStorage.setItem(TOKEN_NAME, JWT);
if (window.localStorage.getItem(TOKEN_NAME)) {
    window.location.href = redirectTo;
}
</script>
EOF;
    }

    /**
     * ecjia 掌柜
     * @param $authCode
     * @return int
     */
    public function decodeByEcjia($authCode)
    {
        $auth_key = config('services.ecjia_b2b2c.key');
        $cipher = config('services.ecjia_b2b2c.cipher', 'AES-256-CBC');

        try {
            $authEncrypter = new AuthEncrypter($auth_key, $cipher);
            $params = (new AuthLoginDecrypt($authCode, $authEncrypter, 10))->decrypt();
            return intval($params['store_user_id']);
        } catch (\Exception $exception) {
            Log::error('ECJia Synchronous login failure');
            return 0;
        }
    }
}
