<?php

namespace App\Custom\Guestbook\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class GuestbookCommand
 */
class GuestbookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:guestbook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test custom guestbook';


    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
      // TODO
    }
}
