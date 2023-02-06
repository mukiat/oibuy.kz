<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class Upgrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Application version update command';

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
     * @return mixed
     */
    public function handle()
    {
        $currentVersion = config('shop.dsc_version');

        Artisan::call('migrate', ['--force' => true]);
        if (version_compare($currentVersion, VERSION, '<')) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        $patchFiles = glob(app_path('Patch/Migration_*.php'));
        foreach ($patchFiles as $patch) {
            $className = basename($patch, '.php');
            $v = str_replace('_', '.', Str::substr($className, 10));

            if (version_compare($v, $currentVersion, '>=')) {
                $class = '\\App\\Patch\\' . $className;
                if (class_exists($class)) {
                    echo $v . " updating\n";
                    app($class)->run();
                }
            }
        }
    }
}
