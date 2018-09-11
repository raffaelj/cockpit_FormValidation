<?php

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    // load class Admin and overwrite the original Forms\Controller\Admin
    include_once(__DIR__.'/Controller/Admin.php');
}


$app->on('forms.submit.before', function($form, &$data, $frm, &$options) {
    
    // validation
    
    if (isset($frm['validate']) && $frm['validate']) {
            
        // load validation class
        require_once(__DIR__ . '/Controller/FormValidation.php');
        
        $validated = new Forms\Controller\FormValidation($data, $frm);
        
        // send 404 to sender
        if (false==$validated->response()) {
            $this->response->body = false;
            die;
        }
        
        // continue if true or send error messages to sender
        if (true!==$validated->response()) {
            
            $data = ["error" => $validated->response(), "data" => $validated->data];
            
            $this->response->mime = 'json';
            $this->response->body = $data;
            die;
            
        }
        
    }
    
    
    // mail subject
    
    $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;
                
    if(isset($frm['email_subject']) && !empty($frm['email_subject'])){
        
        $pattern = '{{%s}}';
        
        $datamap = $data;
        $datamap['app.name'] = $this['app.name'];

        $map = [];
        foreach($datamap as $var => $value){
          
            // $string = $this->app->helpers['utils']->safe_truncate($value, 90, $append = '...');
                // Uncaught Error: Call to a member function safe_truncate() on string
            // $map[sprintf($pattern, $var)] = $string;
            
            $map[sprintf($pattern, $var)] = $value;
        }
        
        $subject = strtr($frm['email_subject'], $map);
        
    }
    else{
        // $subject = $this->app->helpers["i18n"]->get('New form data for').": {$formname}";
        $subject = "New form data for: {$formname}";
    }
    
    // add mail subject to options
    $options['subject'] = $subject;
    
    // add reply_to
    if (isset($frm['reply_to']) && !empty($frm['reply_to']) && isset($data[$frm['reply_to']]) && filter_var(idn_to_ascii(trim($data[$frm['reply_to']])), FILTER_VALIDATE_EMAIL) ) {
        
        $options['reply_to'] = trim($data[$frm['reply_to']]);
        
    }
    
    
    // add altMessage
    // $options['altMessage'] = "...";
    
    // to do...
    
});
