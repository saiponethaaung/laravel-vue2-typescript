<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MemberInviteWithProject extends Mailable
{
    use Queueable, SerializesModels;
    
    protected $project;
    protected $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $project)
    {
        $this->email = $email;
        $this->project = $project;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('v1.mail.MemberInviteWithProject', ['email' => $this->email, 'project' => $this->project]);
    }
}
