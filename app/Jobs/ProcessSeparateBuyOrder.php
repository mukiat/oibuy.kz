<?php

namespace App\Jobs;

use App\Services\Flow\FlowOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * 购物流程店铺商品分单
 *
 * Class ProcessSeparateBuyOrder
 * @package App\Jobs
 */
class ProcessSeparateBuyOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $filter = [];

    /**
     * ProcessSeparateBuyOrder constructor.
     * @param $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param FlowOrderService $flowOrderService
     * @throws \Exception
     */
    public function handle(FlowOrderService $flowOrderService)
    {
        $order_id = $this->filter['order_id'] ?? 0;
        $user_id = $this->filter['user_id'] ?? 0;
        $flowOrderService->OrderSeparateBill($order_id, $user_id);
    }
}
