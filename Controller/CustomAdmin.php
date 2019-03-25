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

}
