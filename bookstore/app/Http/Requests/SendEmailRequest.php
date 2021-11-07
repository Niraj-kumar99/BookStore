<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class SendEmailRequest
{
    public function sendMail($email,$token)
    {
        $name = 'niraj';
        $email = $email;
        $subject = 'Regarding your Password Reset';
        $data ="Your password Reset Link <br>".$token;
          
        require '..\vendor\autoload.php';
        $mail = new PHPMailer(true);

        try
        {                                       
            $mail->isSMTP();                                          
            $mail->Host       = 'smtp.gmail.com';                        
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = 'kumarnkj35@gmail.com';                  
            $mail->Password   = 'hiniraj35166';                              
            $mail->SMTPSecure = 'tls'; 
            $mail->Port       = 587;
            $mail->setFrom('kumarnkj35@gmail.com', 'niraj'); 
            $mail->addAddress($email,$name);
            $mail->isHTML(true);  
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            $dt = $mail->send();

            if($dt)
            {
                return true;
            } 
            else
            {
                return false;
            } 

        }
        catch (Exception $e) 
        {
            return back()->with('error','Message could not be sent.');

        }
    }



/*
public function sendEmailToCollab($email,$data,$currentUserfirstName,$currentUserlastName,$currentUserEmail)
    {
        $subject = 'Sharing a note to you:';

        $data = $currentUserfirstName.' '.$currentUserlastName.' '.$currentUserEmail.' shared a note to you <br>'.$data;
        //$name = $Touser_namefirstname;  //-------
        $email = $email;
        $subject = 'Sharing a note to you:';

        $mail = new PHPMailer(true);

        try
        {                                       
            $mail->isSMTP();                                          
            $mail->Host       = env('MAIL_HOST');                        
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = env('MAIL_USERNAME');                  
            $mail->Password   = env('MAIL_PASSWORD');                              
            $mail->SMTPSecure = 'tls'; 
            $mail->Port       = 587;
            $mail->setFrom(env('MAIL_USERNAME'),env('MAIL_FROM_NAME')); 
            $mail->addAddress($email);
            $mail->isHTML(true);  
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            $dt = $mail->send();

            if($dt)
                return true;
            else
                return false;

        }
        catch (Exception $e) 
        {
            return back()->with('error','Message could not be sent.');
        }
    }
    */

}