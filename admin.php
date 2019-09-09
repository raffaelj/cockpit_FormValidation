<?php

// bind custom function form($name) and keep the other functions intact

$app->bind('/forms/form', function($params){

    return $this->invoke('FormValidation\\Controller\\CustomAdmin', 'form');

});

$app->bind('/forms/form/:name', function($params){

    return $this->invoke('FormValidation\\Controller\\CustomAdmin', 'form', ['name' => $params['name']]);

});

$app->bind('/formvalidation/copyMailTemplate/:name', function($params){

    return $this->invoke('FormValidation\\Controller\\CustomAdmin', 'copyMailTemplate', [$params['name'] ?? '']);

});
