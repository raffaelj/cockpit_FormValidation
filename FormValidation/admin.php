<?php

// bind custom function form($name) and keep the other functions intact

include_once(__DIR__.'/Controller/CustomAdmin.php');

$app->on('admin.init', function() {

    $this->bind('/forms/form/:name', function($params){

        return $this->invoke('Forms\\Controller\\CustomAdmin', 'form', ['name' => $params['name']]);

    });

});
