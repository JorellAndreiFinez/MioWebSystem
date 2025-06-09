<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class EnrollmentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $studentName;
    public $email;
    public $username;
    public $password;

     public function __construct($studentName, $email, $username, $password)
    {
        $this->studentName = $studentName;
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Enrollment Successful')
                    ->view('emails.enrollment-success');
    }
}
