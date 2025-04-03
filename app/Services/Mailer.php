<?php

namespace App\Services;

class Mailer
{
    private $destinationEmail;
    private $subject;
    private $message;
    private $htmlFile;

    public function __construct(){
    }
    public function to($email){
        $this->destinationEmail = $email;
    }
    public function subject($subject){
        $this->subject = $subject;
    }
    public function message($message){
        $this->message = $message;
    }
    public function htmlFile($htmlFile){
        $this->htmlFile = $htmlFile;
    }
    public function sendMail(){
        $to      = $this->destinationEmail;
        $subject = $this->subject;
        $message = $this->message;
        $headers = 'From: webmaster@example.com' . "\r\n" .
            'Reply-To: webmaster@example.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
    }

    public function test(){
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@example.com';
            $mail->Password = 'your-email-password';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your-email@example.com', 'Your Name');
            $mail->addAddress('recipient@example.com');

            $mail->isHTML(true);
            $mail->Subject = 'Your HTML Email';
            $mail->Body = '<h2>Welcome to Our Service</h2><p>This is an <b>HTML email</b> sent using PHPMailer.</p>';

            $mail->send();
            echo "Email sent successfully!";
        } catch (Exception $e) {
            echo "Email sending failed: {$mail->ErrorInfo}";
        }
    }
}