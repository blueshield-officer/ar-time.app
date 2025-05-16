<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use Smarty;

class Mailer {
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }
    public function sendMail($to, $subject) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ar-time <no-reply@ar-time.org>',
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($to, $subject, $this->template, implode("\r\n", $headers));
    }
}