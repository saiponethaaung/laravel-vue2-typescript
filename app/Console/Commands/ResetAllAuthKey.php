<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use App\Notifications\SendQrCode;
use App\Traits\Common\GenerateUniqueOTPCodeTrait;

class ResetAllAuthKey extends Command
{
    use GenerateUniqueOTPCodeTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pixybots:reset-auth-otp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all auth otp and send email to user!';

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
        $users = User::get();

        foreach($users as $user) {
            $user->auth_code = $this->generateUniqueCode();
            $user->save();
            $user->notify(new SendQrCode($this->getImageQr($user->id)));
        }
    }
}
