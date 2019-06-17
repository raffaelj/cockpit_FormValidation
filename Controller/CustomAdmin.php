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
        // only changed the template dir from 'form' to 'formvalidation'
        return $this->render('formvalidation:views/form.php', compact('form'));
    }

    // added new route to copy the default email template file
    public function copyTemplate($name = '') {

        if (empty($name)) return false;

        $this('fs')->mkdir(COCKPIT_CONFIG_DIR . '/forms/emails');

        $source      = $this->app->path('formvalidation:templates/emails/contactform.php');
        $destination = COCKPIT_CONFIG_DIR . '/forms/emails/' . $name . '.php';

        return $this('fs')->copy($source, $destination, false);

    }

}
