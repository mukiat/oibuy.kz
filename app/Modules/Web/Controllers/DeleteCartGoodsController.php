<?php

namespace App\Modules\Web\Controllers;

use App\Services\Cart\CartGoodsService;

/**
 * 删除购车商品
 */
class DeleteCartGoodsController extends InitController
{
    protected $cartGoodsService;

    public function __construct(
        CartGoodsService $cartGoodsService
    )
    {
        $this->cartGoodsService = $cartGoodsService;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index()
    {
        $result = $this->cartGoodsService->ajaxDeleteCartGoods();
        return response()->json($result);
    }
}
