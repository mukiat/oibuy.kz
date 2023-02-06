<?php

namespace App\Console\Commands;

use App\Models\Users;
use App\Services\Order\OrderMobileService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserOrderNumServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user:order {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user order statistics command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        $user_id = (int)$this->argument('user_id');

        $list = Users::select('user_id', 'user_name')->whereHasIn('getOrder');

        if ($user_id > 0) {
            $list = $list->where('user_id', $user_id);
        }

        $list->chunk(5, function ($list) {
            foreach ($list as $key => $val) {
                if ($val) {
                    $val = collect($val)->toArray();

                    $user = app(OrderMobileService::class)->orderNum($val['user_id']);

                    $where = [
                        'user_id' => $val['user_id']
                    ];
                    $values = [
                        'user_name' => $val['user_name'],
                        'order_all_num' => $user['all'],
                        'order_nopay' => $user['nopay'],
                        'order_nogoods' => $user['nogoods'],
                        'order_isfinished' => $user['isfinished'],
                        'order_isdelete' => $user['isdelete'],
                        'order_team_num' => $user['team_num'],
                        'order_not_comment' => $user['not_comment'],
                        'order_return_count' => $user['return_count']
                    ];
                    DB::table('user_order_num')->updateOrInsert($where, $values);
                }
            }
        });
    }
}
