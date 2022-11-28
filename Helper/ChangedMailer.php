<?php
/**
 * Overwrite core Mailer service to enable attachments with filenames
 * from $_FILES with tmp_name and name
 */

namespace FormValidation\Helper;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use \Mailer;
use \Mailer_Message;

class ChangedMailer extends \Mailer {

    public function createMessage($to, $subject, $message, $options=[]) {

        $mail = new PHPMailer(true);

        if ($this->transport == 'smtp') {

            $mail->isSMTP();

            if (isset($this->options['host']) && $this->options['host'])      {
                $mail->Host = $this->options['host']; // Specify main and backup server
            }

            if (isset($this->options['auth']) && $this->options['auth']) {
                $mail->SMTPAuth = $this->options['auth']; // Enable SMTP authentication
            }

            if (isset($this->options['user']) && $this->options['user']) {
                $mail->Username = $this->options['user']; // SMTP username
            }

            if (isset($this->options['password']) && $this->options['password']) {
                $mail->Password = $this->options['password']; // SMTP password
            }

            if (isset($this->options['port']) && $this->options['port']) {
                $mail->Port = $this->options['port']; // smtp port
            }

            if (isset($this->options['encryption']) && $this->options['encryption']) {
                $mail->SMTPSecure = $this->options['encryption']; // Enable encryption: 'ssl' , 'tls' accepted
            }

            // Extra smtp options
            if (isset($this->options['smtp']) && is_array($this->options['smtp'])) {
                $mail->SMTPOptions = $this->options['smtp'];
            }
        }

        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->CharSet = 'utf-8';

        $mail->IsHTML($message !=  strip_tags($message)); // auto-set email format to HTML

        if (is_string($to)) {
            $to_array = explode(',', $to);
        } else {
            $to_array = $to ?? [];
        }

        foreach ($to_array as $to_single) {
            $mail->addAddress($to_single);
        }

        if (isset($options['altMessage']) && $options['altMessage']) {
            $mail->AltBody = $options['altMessage'];
        }

        if (isset($options['embedded'])) {
            foreach ($options['embedded'] as $id => $file) {
                $mail->AddEmbeddedImage($file, $id);
            }
        }

        if (isset($options['attachments'])) {

            foreach ($options['attachments'] as $id => $file) {

                if (is_string($id)) {
                    $mail->addStringAttachment($file, $id);
                } else {
                    // $mail->addAttachment($file);

                    // added option to add attachments with file names
                    // useful, if sending uploaded files with tmp names
                    if (is_string($file)) {
                        $mail->addAttachment($file);
                    }
                    elseif (is_array($file) && isset($file['path'])) {
                        $path  = $file['path'];
                        $alias = $file['name'] ?? '';
                        $mail->addAttachment($path, $alias);
                    }
                }
            }
        }

        if (isset($options['cc'])) {
            foreach ($options['cc'] as $email) {
                $mail->AddCC($email);
            }
        }

        if (isset($options['bcc'])) {
            foreach ($options['bcc'] as $email) {
                $mail->addBCC($email);
            }
        }

        $msg = new Mailer_Message($mail);

        return $msg;
    }

}
