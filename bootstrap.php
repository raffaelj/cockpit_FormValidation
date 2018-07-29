<?php

// init + load i18n

$app('i18n')->locale = $app->retrieve('i18n', 'en');

$locale = $app->module('cockpit')->getUser('i18n', $app('i18n')->locale);

if ($translationspath = $app->path("#config:cockpit/i18n/{$locale}.php")) {
    $app('i18n')->locale = $locale;
    $app('i18n')->load($translationspath, $locale);
}

// load class Admin and overwrite the original Forms\Controller\Admin
require_once(__DIR__ . '/Controller/Admin.php');


// overwrite submit function for custom validation responses
$this->module("forms")->extend([

    'submit' => function($form, $data, $options = []) {

        $frm = $this->form($form);

        if (!$frm) {
            return false;
        }
        
        if (isset($frm['validate']) && $frm['validate']) {
            
            // load validation class
            require_once(__DIR__ . '/Controller/FormValidation.php');
            
            
            
            $validated = new Forms\Controller\FormValidation($data, $frm);
            
            // send 404 to sender
            if (false==$validated->response())
              return false;
            
            // continue if true or send error messages to sender
            if (true!==$validated->response())
              return ["error" => $validated->response(), "data" => $validated->data];
            
        }
        
        // custom form validation
        if ($this->app->path("#config:forms/{$form}.php") && false===include($this->app->path("#config:forms/{$form}.php"))) {
            return false;
        }

        if (isset($frm['email_forward']) && $frm['email_forward']) {

            $emails          = array_map('trim', explode(',', $frm['email_forward']));
            $filtered_emails = [];

            foreach ($emails as $to){

                // Validate each email address individually, push if valid
                if (filter_var($to, FILTER_VALIDATE_EMAIL)){
                    $filtered_emails[] = $to;
                }
            }

            if (count($filtered_emails)) {

                $frm['email_forward'] = implode(',', $filtered_emails);

                // There is an email template available
                if ($template = $this->app->path("#config:forms/emails/{$form}.php")) {

                    // $body = $this->app->renderer->file($template, $data, false);
                    $body = $this->app->view($template, compact('data','form','frm'));

                // Prepare template manually
                } else {

                    $body = [];
                    
                    $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;
                    $body[] = "<b>New message from {$formname} - ". $this->app->getSiteUrl() ."</b>\r\n<br>";

                    foreach ($data as $key => $value) {
                        $body[] = "<b>{$key}:</b>";
                        $body[] = (is_string($value) ? $value:json_encode($value));
                    }

                    $body = implode("\n<br>", $body);
                }

                $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;
                
                if(isset($frm['email_subject']) && !empty($frm['email_subject'])){
                    
                    // $subject = $frm['email_subject'];
                    
                    $pattern = '{{%s}}';

                    $map = [];
                    foreach($data as $var => $value){
                        // $string = $this->app->helpers['utils']->safe_truncate($value, 90, $append = '...'); // Uncaught Error: Call to a member function safe_truncate() on string
                        // $map[sprintf($pattern, $var)] = $string;
                        $map[sprintf($pattern, $var)] = $value;
                    }
                    
                    $subject = strtr($frm['email_subject'], $map);
                    
                }
                else{
                    $subject = $this->app->helpers["i18n"]->get('New form data in').": {$formname}";
                }

                $this->app->mailer->mail(
                    $frm['email_forward'],
                    $subject,
                    $body,
                    $options
                );
            }
        }

        if (isset($frm['save_entry']) && $frm['save_entry']) {
            $entry = ['data' => $data];
            $this->save($form, $entry);
        }

        return $data;
    }
]);