<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\Facebook\Webhook\ProcessWebhook;

class ProcessFacebookRequestWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pixybots:process-fbwh-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process incoming facebook request!';

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
        ProcessWebhook::dispatch();
        ProcessWebhook::dispatch();
        ProcessWebhook::dispatch();
        ProcessWebhook::dispatch();
        ProcessWebhook::dispatch();
    }
}
