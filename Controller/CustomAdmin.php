<?php

// nearly exact copy of Forms/Controller/Admin.php
namespace FormValidation\Controller;

class CustomAdmin extends \Cockpit\AuthController {

    public function form($name = null) {

        $form = [ 'name'=>'', 'in_menu' => false ];

        if ($name) {

            $form = $this->module('forms')->form($name);

            if (!$form) {
                return false;
            }
        }
        
        // get field templates
        $templates = [];

        foreach ($this->app->helper('fs')->ls('*.php', 'formvalidation:templates/forms') as $file) {
            $templates[] = include($file->getRealPath());
        }

        foreach ($this->app->module('forms')->forms() as $col) {
            $templates[] = $col;
        }

        // changed the template dir from 'form' to 'formvalidation'
        return $this->render('formvalidation:views/form.php', compact('form', 'templates'));
    }

    // added new route to copy the default email template file
    public function copyMailTemplate($name = '') {

        if (empty($name)) return ['error' => 'The form needs a name'];

        $this('fs')->mkdir(COCKPIT_CONFIG_DIR . '/forms/emails');

        $source      = $this->app->path('formvalidation:templates/emails/contactform.php');
        $destination = COCKPIT_CONFIG_DIR . '/forms/emails/' . $name . '.php';

        $copied = $this('fs')->copy($source, $destination, false);

        return $copied ? ['success' => 1] : ['error' => 'Copying failed.'];

    }

}
