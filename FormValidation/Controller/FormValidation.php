<?php

namespace Forms\Controller;

class FormValidation extends \Cockpit\AuthController {
  
    /*
      Notes:
      
        requires: PECL intl extension (for punycode conversion of urls and mail adresses)
        
        Work in progress! Feel free to contribute with code, bug reports or feature requests.
        
    */
  
    protected $error = [];
    protected $fields = [];
    public $data = [];
    protected $exit = false;
    protected $allow_extra_fields = false;
    protected $validate_and_touch_data = false;
  
    function __construct($data = [], $frm = []){

        $this->data = $data;
        if(isset($frm['fields'])) $this->fields = $frm['fields'];
        if(isset($frm['allow_extra_fields'])) $this->allow_extra_fields = $frm['allow_extra_fields'];
        if(isset($frm['validate_and_touch_data'])) $this->validate_and_touch_data = $frm['validate_and_touch_data'];

        $this->validate();

    }
  
    function validate(){
      
        //for debugging
        // $this->error["debug"][] = $this->fields;
        
        // touch original data if you don't want to do this step in your frontend
        if($this->validate_and_touch_data)
            foreach($this->data as $key => &$val)
                $this->data[$key] = htmlspecialchars(strip_tags(trim($val)));
        
        // compare sent field names with names from the form builder
        if(!$this->allow_extra_fields){
            
            $diff = array_diff(array_keys($this->data), array_column($this->fields, "name"));
            
            if(!empty($diff)){
                $this->error["validator"][] = "These fields are not allowed: ". implode(", ", $diff);
                return;
            }
            
        }
        
        // check, if key names are alphanumeric
        if(!$this->alnumKeys($this->data))
            return;
        
        // check, for validation options
        if (empty($this->fields)){
          
            // no validation options available
            
            // to do ...
            
            return;
            
        }
        
        // validations
        $required = [];
        $validate = [];
        $type = [];
        $honeypot = false;
        foreach($this->fields as $field){
            
            if(isset($field['required']) && $field['required'])
                $required[] = $field['name'];
            
            if(isset($field['validate']) && $field['validate'])
                $validate[] = $field['name'];
            
            if(isset($field['options']['validate']['honeypot']))
                $honeypot = $field['options']['validate']['honeypot'];
            
            if(isset($field['options']['validate']['type']) && array_key_exists($field['name'], $this->data))
                $type[$field['name']] = $field['options']['validate']['type'];
            
        }
        
        // 1. honeypot
        if($honeypot && isset($this->data[$honeypot['fieldname']])
          && $this->data[$honeypot['fieldname']] != $honeypot['expected_value']){
            
            if(isset($honeypot['response'])){
                
                if($honeypot['response'] == '404'){
                    $this->exit = true;
                    return;
                }
                else{
                    $this->error[$honeypot['fieldname']] = $honeypot['response'];
                }
                
            }
            else{
                $this->error["honeypot"] = "Hello spambot";
            }
        }
            
        // don't validate honeypot again
        if (($key = array_search($honeypot['fieldname'], $validate)) !== false)
            unset($validate[$key]);
        
        
        // 2. required
        foreach($required as $name){
            
            if(!isset($this->data[$name]) || empty($this->data[$name])){
                
                $this->error[$name][] = "is required";
                
                // don't validate this field again
                if (($key = array_search($name, $validate)) !== false)
                    unset($validate[$key]);

            }
            
        }
        
        // 3. contains
            // to do ...
        
        // 4. type
        foreach($validate as $name){
            
            if(isset($type[$name])){
                
                foreach($type[$name] as $match_type => $not_inverse){
                    
                    $match = $this->matchType($name, $match_type);
                    
                    if($not_inverse && !$match || !$not_inverse && $match){
                        $must = $match ? "must be" : "must not be";
                        $must = !$not_inverse ? "must not be" : "must be";
                        $this->error[$name][] = "$must $match_type";
                    }
                    
                }
                
            }
            
            
        }
        
    }
  
    function alnumKeys($arr){
        
        // returns false if any key name is not alphanumeric or '-' or '_'
        
        $ret = true;
        $valid = ["-", "_"];
        
        foreach(array_keys($arr) as $key){
            if( !ctype_alnum( str_replace($valid, "", $key) ) ){
                $this->error[$key][] = "allowed characters in key names: 'a-zA-Z0-9', '-' and '_'";
                $ret = false;
            }
                
        }
        
        return $ret;
        
    }
  
    function matchType($field, $match_type/*, $not_inverse = true*/){
        
        // $this->error["matchType"][$match_type][] = $not_inverse;
        
        $ret = false;
        switch($match_type){
            
            case 'mail':
                $ret = filter_var(idn_to_ascii($this->data[$field]), FILTER_VALIDATE_EMAIL);
                break;
                
            case 'phone':
                $ret = !preg_match('~[^-\s\d./()+]~', $this->data[$field]);
                break;
                
            case 'url':
                $ret = filter_var(idn_to_ascii($this->data[$field]), FILTER_VALIDATE_URL);
                break;
            
        }
        
        return $ret;
        
    }
    
    function response(){
      
        if($this->exit)
            return false;
        
        if(!empty($this->error))
            return $this->error;
        
        return true;
    }
  
}