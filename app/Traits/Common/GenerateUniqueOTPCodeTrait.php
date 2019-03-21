<?php

namespace App\Traits\Common;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

trait GenerateUniqueOTPCodeTrait
{
    public function generateUniqueCode()
    {
        $google2FA = new Google2FA();
        $uniqueSecret = false;
        $code = $google2FA->generateSecretKey();
        do{
            if(User::where('auth_code', $code)->count()>0){
                $code = $google2FA->generateSecretKey();
            }else{
                $uniqueSecret = true;
            }
        }while($uniqueSecret===false);

        return $code;
    }

    public function getImageQr($email, $code)
    {
        $g2fa = new Google2FA();
        $g2fa->setAllowInsecureCallToGoogleApis(true);
        return $g2fa->getQRCodeGoogleUrl(
            env('APP_NAME'),
            $email,
            $code
        );
    }
}