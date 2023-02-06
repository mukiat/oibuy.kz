<?php

namespace App\Modules\Web\Controllers;

use App\Services\Cart\CartCommonService;

class AjaxCartController extends InitController
{
    protected $cartCommonService;

    public function __construct(
        CartCommonService $cartCommonService
    )
    {
        $this->cartCommonService = $cartCommonService;
    }

    public function index()
    {
        if ($this->checkReferer() === false) {
            return response()->json(['error' => 1, 'message' => 'referer error']);
        }

        $act = addslashes(trim(request()->input('act', '')));

        $result = ['error' => 0, 'content' => ''];

        /*------------------------------------------------------ */
        //-- 查询购物车商品数量
        /*------------------------------------------------------ */
        if ($act == 'cart_number') {
            $result = $this->cartCommonService->cartNumber();
        }

        return response()->json($result);
    }
}
