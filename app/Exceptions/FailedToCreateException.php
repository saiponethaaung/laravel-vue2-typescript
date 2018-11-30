<?php

namespace App\Exceptions;

use Exception;

class FailedToCreateException extends Exception
{
    protected $message;

    public function __construct($message) {
        parent::__construct();
        $this->message = $message;
    }

    public function render($request)
    {
        return response()->json([
            'status' => false,
            'code' => 422,
            'mesg' => 'Failed to create!',
            'debugMesg' => $this->message,
            'raw' => $request
        ],  422);
    }
}
