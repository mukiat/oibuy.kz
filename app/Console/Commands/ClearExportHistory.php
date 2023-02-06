<?php

namespace App\Console\Commands;

use App\Extensions\File;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearExportHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired export history';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        $disk = File::diskFile();

        DB::table('export_history')->select('id', 'download_url')
            ->whereDate('created_at', '<', Carbon::now()->subDays(3))
            ->orderBy('id')->chunk(100, function ($histories) use ($disk) {
                foreach ($histories as $history) {
                    if (!is_null($history)) {
                        // 删除文件
                        Storage::delete($history->download_url . '.xls');
                        if ($disk != 'public') {
                            Storage::disk($disk)->delete($history->download_url . '.xls');
                        }
                        // 移除记录
                        DB::table('export_history')
                            ->where('id', $history->id)
                            ->delete();
                    }
                }
            });
    }
}
