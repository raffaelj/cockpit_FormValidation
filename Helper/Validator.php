<?php
/*
  Notes:

    requires: PECL intl extension (for punycode conversion of urls and mail adresses)

    Work in progress! Feel free to contribute with code, bug reports or feature requests.

*/

namespace FormValidation\Helper;

class Validator extends \Lime\Helper {

    public $data = [];

    protected $error  = [];
    protected $fields = [];
    protected $exit   = false;
    protected $allow_extra_fields = false;

    public $maxUploadSizeSystem = 0;
    public $allowedUploadsSystem = '*';

    public $phpFileUploadErrors = [
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    ];

    public function init($data = [], $frm = []) {

        $this->data = $data;

        $this->maxUploadSizeSystem  = $this->app->retrieve('max_upload_size', 0);
        $this->allowedUploadsSystem = $this->app->retrieve('allowed_uploads', '*');

        if (isset($frm['fields']) && is_array($frm['fields'])) {
            $this->fields = $frm['fields'];
        }

        if (isset($frm['allow_extra_fields'])) {
            $this->allow_extra_fields = $frm['allow_extra_fields'];
        }

        // touch original data if you don't want to do this step in your frontend
        if (isset($frm['validate_and_touch_data']) && $frm['validate_and_touch_data']) {
            foreach ($this->data as $key => &$val) {
                if (is_string($val)) {
                    $this->data[$key] = htmlspecialchars(strip_tags(trim($val)));
                }
            }
        }

        $this->validate();

        return $this;

    } // end of init()

    public function validate() {

        // check, if key names are alphanumeric
        if (!$this->alnumKeys($this->data)) {
            // error message will be applied in alnumKeys()
            return;
        }

        // check, for validation options
        if (empty($this->fields)) {

            // no validation options available

            // to do ...

            return;

        }

        // validations
        $required = [];
        $validate = [];
        $type = [];
        $honeypot = false;
        $files = [];

        foreach ($this->fields as $field) {

            if (isset($field['required']) && $field['required']) {
                $required[] = $field['name'];
            }

            if (isset($field['validate']) && $field['validate']
                && !isset($field['options']['validate']['honeypot']) // don't validate honeypot twice
                ) {

                $validate[] = $field['name'];
            }

            if (isset($field['options']['validate']['honeypot'])) {
                $honeypot = $field;
            }

            if (isset($field['options']['validate']['type'])
                && array_key_exists($field['name'], $this->data)) {

                $type[$field['name']] = $field['options']['validate']['type'];
            }

            if (isset($field['options']['validate']['equals'])
                && array_key_exists($field['name'], $this->data)) {

                $equals[$field['name']] = $field['options']['validate']['equals'];
                if (is_string($equals[$field['name']])) {
                    $equals[$field['name']] = [$equals[$field['name']]];
                }
            }

            if (isset($field['options']['validate']['equalsi'])
                && array_key_exists($field['name'], $this->data)) {

                $equalsi[$field['name']] = $field['options']['validate']['equalsi'];
                if (is_string($equalsi[$field['name']])) {
                    $equalsi[$field['name']] = [$equalsi[$field['name']]];
                }
            }

            if ($field['type'] == 'file') {
                $files[] = $field['name'];
            }

        }

        // 1. honeypot
        if ($honeypot) {

            $honeypotOptions = $honeypot['options']['validate']['honeypot'];

            $honeypotName = $honeypot['options']['attr']['name']
                ?? $honeypotOptions['fieldname'] ?? $honeypot['name'];

            if (isset($this->data[$honeypotName])
                && $this->data[$honeypotName] != $honeypotOptions['expected_value']) {

                if (isset($honeypotOptions['response'])) {

                    if ($honeypotOptions['response'] == '404'){
                        $this->exit = true;
                        return;
                    }
                    else {
                        $this->error['honeypot'] = $honeypotOptions['response'];
                    }

                }
                else {
                    $this->error['honeypot'] = $this->helper('i18n')->get('Hello spambot');
                }

                return;

            }

        }

        // 2. compare sent field names with names from the form builder
        if (!$this->allow_extra_fields) {

            $diff = array_diff(array_keys($this->data), array_column($this->fields, 'name'));

            // honeypot might have a positive projection (will always be sent)
            // with a different name, then the field name
            if ($honeypot) {
                if (($key = array_search($honeypotName, $diff)) !== false) {
                    unset($diff[$key]);
                }
            }

            if (!empty($diff)) {

                $this->error["validator"][] = 'These fields are not allowed: '. implode(', ', $diff);
                return;

            }

        }

        // 3. required
        foreach ($required as $name) {

            if (!isset($this->data[$name]) || empty($this->data[$name])) {

                $this->error[$name][] = $this->helper('i18n')->get('is required');

                // don't validate this field again
                if (($key = array_search($name, $validate)) !== false) {
                    unset($validate[$key]);
                }

            }

        }

        // 3. contains
            // to do ...


        foreach ($validate as $name) {

            // 4. type
            if (isset($type[$name])) {

                foreach ($type[$name] as $match_type => $not_inverse) {

                    $match = $this->matchType($name, $match_type);

                    if ($not_inverse && !$match || !$not_inverse && $match) {
                        $must = $match ? "must be" : "must not be";
                        $must = !$not_inverse ? "must not be" : "must be";
                        $this->error[$name][] = $this->helper('i18n')->get("$must $match_type");
                    }

                }

            }

            // 5. equals
            if (isset($equals[$name])) {
                $foundMatch = false;
                foreach ($equals[$name] as $v) {
                    if ($this->data[$name] == $v) {
                        $foundMatch = true;
                        break;
                    }
                }
                if (!$foundMatch) {
                    $this->error[$name][] = $this->helper('i18n')->get("doesn't match");
                }
            }
            if (isset($equalsi[$name])) {
                $foundMatch = false;
                foreach ($equalsi[$name] as $v) {
                    if (strtolower($this->data[$name]) == strtolower($v)) {
                        $foundMatch = true;
                        break;
                    }
                }
                if (!$foundMatch) {
                    $this->error[$name][] = $this->helper('i18n')->get("doesn't match");
                }

            }

        }

        // file uploads
        foreach ($files as $name) {

            if (!isset($this->data[$name]) || empty($this->data[$name]) || !is_array($this->data[$name])) {
                continue;
            }

            $res = $this->validateUploadedAssets($this->data[$name], $name);

            if (!empty($res['errors'])) {
                foreach ($res['errors'] as $errorsPerFile) {
                    foreach ($errorsPerFile as $err) {
                        $this->error[$name][] = $err;
                    }
                }
            }

        }

    } // end of validate()

    public function alnumKeys($arr) {

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

    } // end of alnumKeys()

    public function matchType($field, $type) {

        switch ($type) {

            case 'mail':
                return $this->isEmail($this->data[$field]);

            case 'phone':
                return !preg_match('~[^-\s\d./()+]~', $this->data[$field]);

            case 'url':
                return $this->isUrl($this->data[$field]);

            case 'number':
                return \is_numeric($this->data[$field]);

        }

        return false;

    } // end of matchType()

    public function response() {

        if ($this->exit) return false;

        if (!empty($this->error)) return $this->error;

        return true;

    } // end of response()

    public function isEmail($str) {

        if (\function_exists('idn_to_ascii')) {
            return \filter_var(\idn_to_ascii($str, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_EMAIL);
        } else {
            return \filter_var($str, FILTER_VALIDATE_EMAIL);
        }

    } // end of isEmail()

    public function isUrl($str) {

        if (\function_exists('idn_to_ascii')) {
            return \filter_var(\idn_to_ascii($str, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_URL);
        } else {
            return \filter_var($str, FILTER_VALIDATE_URL);
        }

    } // end of isUrl()

    public function validateUploadedAssets($param = 'files', $fieldName = '') {

        $files = [];

        if (is_string($param) && isset($_FILES[$param])) {
            $files = $_FILES[$param];
        } elseif (is_array($param) && isset($param['name'], $param['error'], $param['tmp_name'])) {
            $files = $param;
        }

        $errors    = [];

        if (isset($files['name']) && is_array($files['name'])) {

            foreach ($files['name'] as $k => $v) {

                $max_size = $this->_getMaxUploadSize($fieldName);
                $allowed  = $this->_getAllowedFileTypes($fieldName);

                $_file  = $this->app->path('#tmp:').'/'.$files['name'][$k];
                $_isAllowed = $allowed === true ? true : preg_match("/\.({$allowed})$/i", $_file);
                $_sizeAllowed = $max_size ? filesize($files['tmp_name'][$k]) <= $max_size : true;

                if ($files['error'][$k] || !$_isAllowed || !$_sizeAllowed) {

                    $errors[$k] = [];

                    if ($files['error'][$k]) {
                        $errors[$k][] = $this->phpFileUploadErrors[$files['error'][$k]];
                    }

                    if (!$_isAllowed)   $errors[$k][] = 'file type is not allowed';
                    if (!$_sizeAllowed) $errors[$k][] = 'file size is too big';

                }

            }
        }

        return compact('files', 'errors');
    }

    public function _getMaxUploadSize($fieldName) {

        $field = null;
        foreach ($this->fields as $f) {
            if ($f['name'] == $fieldName) {
                $field = $f;
                break;
            }
        }

        if (!$field) return $this->maxUploadSizeSystem;

        $fieldUploadSize = $field['options']['max_upload_size'] ?? 0;

        return $fieldUploadSize ? $fieldUploadSize : $this->maxUploadSizeSystem;

    }

    public function _getAllowedFileTypes($fieldName) {

        $field = null;
        foreach ($this->fields as $f) {
            if ($f['name'] == $fieldName) {
                $field = $f;
                break;
            }
        }

        $allowed = $this->allowedUploadsSystem;

        if ($field) {
            $fieldAllowedUploads = $field['options']['allowed_uploads'] ?? '';
            if (!empty($fieldAllowedUploads)) {
                $allowed = $fieldAllowedUploads;
            }
        }

        if ($allowed == '*') $allowed = true;
        else {
            $allowed = str_replace([' ', ','], ['', '|'], preg_quote(is_array($allowed) ? implode(',', $allowed) : $allowed));
        }

        return $allowed;

    }

}
