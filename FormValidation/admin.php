<?php

// bind custom function form($name) and keep the other functions intact

$app->bind('/forms/form/:name', function($params){

    return $this->invoke('FormValidation\\Controller\\CustomAdmin', 'form', ['name' => $params['name']]);

});
