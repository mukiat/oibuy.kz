<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\SellerBillOrder;
use App\Repositories\Common\BaseRepository;
use App\Services\Order\OrderDataHandleService;
use Illuminate\Console\Command;

class OrderServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update:order {action=order}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order command';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action == 'order') {

        } elseif ($action == 'goodsReceived') {
            $this->orderGoodsReceived();
        } elseif ($action == 'sellerBillOrder') {
            $this->updateSellerBillOrder();
        }
    }

    /**
     * 更新订单商品状态
     */
    private function orderGoodsReceived()
    {
        $order = OrderInfo::select('order_id')->where(function ($query) {
            $query->whereIn('order_status', [OS_RETURNED, OS_ONLY_REFOUND, OS_RETURNED_PART])
                ->orWhere(function ($query) {
                    $query->orWhereIn('order_status', [OS_CONFIRMED, OS_SPLITED])
                        ->where('pay_status', PS_PAYED)
                        ->where('shipping_status', SS_RECEIVED);
                });
        })->orWhere('main_count', '>', 0);

        $order->chunk(10, function ($list) {
            foreach ($list as $key => $val) {
                OrderGoods::where('order_id', $val->order_id)->chunk(10, function ($goods) {
                    foreach ($goods as $k => $v) {
                        $main_count = OrderInfo::where('order_id', $v->order_id)->value('main_count');
                        $return_count = OrderReturn::select('rec_id')->where('order_id', $v->order_id)->where('rec_id', $v->rec_id)->where('refound_status', 1)->count();
                        $comment_count = Comment::select('rec_id')->where('order_id', $v->order_id)->where('rec_id', $v->rec_id)->where('parent_id', 0)->count();

                        $is_main = 0;
                        if ($main_count > 0) {
                            $is_main = 1;
                        }

                        $is_received = 0;
                        if ($return_count > 0) {
                            $is_received = 1;
                        }

                        $is_comment = 0;
                        if ($comment_count > 0) {
                            $is_comment = 1;
                        }

                        $other = [
                            'is_received' => $is_received,
                            'main_count' => $is_main,
                            'is_comment' => $is_comment
                        ];

                        $update = OrderGoods::where('rec_id', $v->rec_id)->update($other);

                        sleep(0.2);

                        if ($update > 0) {
                            $this->info("更新成功，订单商品：" . $v->rec_id);
                        } else {
                            $this->info("更新失败，值已更新过，订单商品：" . $v->rec_id);
                        }
                    }
                });
            }
        });
    }

    /**
     * 更新商家订单的seller_id值为0问题
     */
    public function updateSellerBillOrder()
    {
        $row = SellerBillOrder::select('order_id', 'order_sn', 'pay_status', 'seller_id')->where('seller_id', 0)
            ->whereHasIn('getOrder', function ($query) {
                $query->where('ru_id', '>', 0);
            })->get();
        $row = $row ? $row->toArray() : [];

        $orderIdList = BaseRepository::getKeyPluck($row, 'order_id');
        $orderList = OrderDataHandleService::orderDataList($orderIdList, ['order_id', 'ru_id']);

        $row = BaseRepository::getArrayChunk($row, 5);

        if ($row) {
            foreach ($row as $key => $val) {
                foreach ($val as $k => $v) {

                    $order = $orderList[$v['order_id']] ?? [];

                    if ($order) {
                        SellerBillOrder::where('order_id', $order['order_id'])->update([
                            'seller_id' => $order['ru_id']
                        ]);

                        dump('订单号：' . $val['order_sn'] . "-- 商家ID：" . $order['ru_id']);
                    }
                }
            }
        }
    }
}
