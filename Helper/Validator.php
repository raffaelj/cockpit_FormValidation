<?php

namespace FormValidation\Helper;

/**
 * Validator for form inputs in Cockpit CMS v1
 *
 * @suggests PECL intl extension (for punycode conversion of urls and mail adresses)
 */
class Validator extends \Lime\Helper {

    public $data = [];

    protected $error  = [];
    protected $fields = [];
    protected $exit   = false;
    protected $allow_extra_fields = false;

    public $maxUploadSizeSystem = 0;
    public $allowedUploadsSystem = '*';

    public function init($data = [], $frm = []) {

        $this->data = $data;

        $this->maxUploadSizeSystem  = $this->app->retrieve('max_upload_size', 0);
        $this->allowedUploadsSystem = $this->app->retrieve('allowed_uploads', '*');

        if (isset($frm['fields']) && \is_array($frm['fields'])) {
            $this->fields = $frm['fields'];
        }

        if (isset($frm['allow_extra_fields'])) {
            $this->allow_extra_fields = $frm['allow_extra_fields'];
        }

        // touch original data if you don't want to do this step in your frontend
        if (isset($frm['validate_and_touch_data']) && $frm['validate_and_touch_data']) {
            foreach ($this->data as $key => &$val) {
                if (\is_string($val)) {
                    $this->data[$key] = \htmlspecialchars(\strip_tags(\trim($val)));
                }
                // TODO: handle arrays
            }
        }

        $this->validate();

        return $this;

    }

    public function validate() {

        $i18n = $this->app->helper('i18n');

        // check, if key names are alphanumeric
        $hasInvalidFieldNames = false;
        foreach (\array_keys($this->data) as $fieldName) {
            if (!$this->isValidFieldName($fieldName)) {
                $this->error['validator'][] = "{$fieldName}: " . $i18n->get('The field name contains invalid characters.');
                $hasInvalidFieldNames = true;
            }
        }
        if ($hasInvalidFieldNames) return;

        if (empty($this->fields)) return;

        // validations
        $required = [];
        $validate = [];
        $types    = [];
        $honeypot = false;
        $files    = [];

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
                && \array_key_exists($field['name'], $this->data)) {

                $types[$field['name']] = $field['options']['validate']['type'];
            }

            if (isset($field['options']['validate']['equals'])
                && \array_key_exists($field['name'], $this->data)) {

                $equals[$field['name']] = $field['options']['validate']['equals'];
                if (\is_string($equals[$field['name']])) {
                    $equals[$field['name']] = [$equals[$field['name']]];
                }
            }

            if (isset($field['options']['validate']['equalsi'])
                && \array_key_exists($field['name'], $this->data)) {

                $equalsi[$field['name']] = $field['options']['validate']['equalsi'];
                if (\is_string($equalsi[$field['name']])) {
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
                    $this->error['honeypot'] = $i18n->get('Hello spambot');
                }

                return;

            }

        }

        // 2. compare sent field names with names from the form builder
        if (!$this->allow_extra_fields) {

            $diff = \array_diff(\array_keys($this->data), \array_column($this->fields, 'name'));

            // honeypot might have a positive projection (will always be sent)
            // with a different name, then the field name
            if ($honeypot) {
                if (($key = \array_search($honeypotName, $diff)) !== false) {
                    unset($diff[$key]);
                }
            }

            if (!empty($diff)) {

                $this->error["validator"][] = 'These fields are not allowed: '. \implode(', ', $diff);
                return;

            }

        }

        // 3. required
        foreach ($required as $name) {

            if (!isset($this->data[$name]) || empty($this->data[$name])) {

                $this->error[$name][] = $i18n->get('This field is required.');

                // don't validate this field again
                if (($key = \array_search($name, $validate)) !== false) {
                    unset($validate[$key]);
                }

            }

        }

        // 3. contains
            // to do ...


        foreach ($validate as $name) {

            // 4. type
            if (isset($types[$name])) {

                foreach ($types[$name] as $type => $expectTrue) {

                    switch ($type) {

                        case 'mail':
                            $result = $this->isEmail($this->data[$name]);
                            if (($result && !$expectTrue) || (!$result && $expectTrue)) {
                                $this->error[$name][] = $expectTrue
                                    ? $i18n->get('Please enter a valid mail address.')
                                    : $i18n->get('This field must not be a mail address.');
                            }
                            break;

                        case 'phone':
                            if ($expectTrue) {
                                if (!$this->isPhoneNumber($this->data[$name])) {
                                    $this->error[$name][] = $i18n->get('Please enter a valid phone number.');
                                }
                            }
                            break;

                        case 'url':
                            $result = $this->isUrl($this->data[$name]);
                            if (($result && !$expectTrue) || (!$result && $expectTrue)) {
                                $this->error[$name][] = $expectTrue
                                    ? $i18n->get('Please enter a url.')
                                    : $i18n->get('This field must not be a url.');
                            }
                            break;

                        case 'number':
                            $result = $this->isNumeric($this->data[$name]);
                            if (($result && !$expectTrue) || (!$result && $expectTrue)) {
                                $this->error[$name][] = $expectTrue
                                    ? $i18n->get('Please enter a number.')
                                    : $i18n->get('This field must not be a number.');
                            }
                            break;

                    }

                }

            }

            // 5. equals
            if (isset($equals[$name])) {

                if (!$this->equals($this->data[$name], $eqals[$name])) {
                    $this->error[$name][] = $i18n->get("This field doesn't match.");
                }
            }
            if (isset($equalsi[$name])) {

                if (!$this->equals($this->data[$name], $equalsi[$name], false)) {
                    $this->error[$name][] = $i18n->get("This field doesn't match.");
                }
            }

        }

        // file uploads
        foreach ($files as $name) {

            if (!isset($this->data[$name]) || empty($this->data[$name]) || !\is_array($this->data[$name])) {
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

    }

    public function response() {

        if ($this->exit) return false;

        if (!empty($this->error)) return $this->error;

        return true;

    }

    public function isAlphaNumeric($str) {
        return \ctype_alnum($str);
    }

    /**
     * Validate field name
     * Allowed characters: `a-zA-Z0-9`, `-`, `_`
     *
     * @param string $str
     * */
    public function isValidFieldName($str) {
        $allowed = ['-', '_'];
        return $this->isAlphaNumeric(\str_replace($allowed, '', $str));
    }

    public function isEmail($str) {

        if (\function_exists('idn_to_ascii')) {
            return \filter_var(\idn_to_ascii($str, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_EMAIL);
        } else {
            return \filter_var($str, FILTER_VALIDATE_EMAIL);
        }

    }

    public function isUrl($str) {

        if (\function_exists('idn_to_ascii')) {
            return \filter_var(\idn_to_ascii($str, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_URL);
        } else {
            return \filter_var($str, FILTER_VALIDATE_URL);
        }

    }

    /*
     * Match valid characters in phone numbers,
     * e. g.: `01234567890`, `(0) 123 - 4567890`, `+49 - 123 4567890`
     * This method can only be used for positive matches, invalid phone numbers
     * like `000` or `+1` will also validate to true
     */
    public function isPhoneNumber($str) {
        return !\preg_match('~[^-\s\d./()+]~', $str);
    }

    public function isNumeric($val) {
        return \is_numeric($val);
    }

    public function equals($str, $references, $caseSensitive = true) {
        if (!\is_array($references)) $references = [$references];
        if (!$caseSensitive) $str = \strtolower($str);
        foreach ($references as $reference) {
            if ($caseSensitive && $str == $reference) return true;
            if (!$caseSensitive && $str == \strtolower($reference)) return true;
        }
        return false;
    }

    public function validateUploadedAssets($param = 'files', $fieldName = '') {

        $files  = [];
        $errors = [];

        if (\is_string($param) && isset($_FILES[$param])) {
            $files = $_FILES[$param];
        } elseif (\is_array($param) && isset($param['name'], $param['error'], $param['tmp_name'])) {
            $files = $param;
        }

        $i18n = $this->app->helper('i18n');

        $phpFileUploadErrors = [
            0 => $i18n->get('There is no error, the file uploaded with success'),
            1 => $i18n->get('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
            2 => $i18n->get('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
            3 => $i18n->get('The uploaded file was only partially uploaded'),
            4 => $i18n->get('No file was uploaded'),
            6 => $i18n->get('Missing a temporary folder'),
            7 => $i18n->get('Failed to write file to disk.'),
            8 => $i18n->get('A PHP extension stopped the file upload.'),
        ];

        if (isset($files['name']) && \is_array($files['name'])) {

            foreach ($files['name'] as $k => $v) {

                $max_size = $this->_getMaxUploadSize($fieldName);
                $allowed  = $this->_getAllowedFileTypes($fieldName);

                $_file  = $this->app->path('#tmp:').'/'.$files['name'][$k];
                $_isAllowed = $allowed === true ? true : \preg_match("/\.({$allowed})$/i", $_file);
                $_sizeAllowed = $max_size ? \filesize($files['tmp_name'][$k]) <= $max_size : true;

                if ($files['error'][$k] || !$_isAllowed || !$_sizeAllowed) {

                    $errors[$k] = [];

                    if ($files['error'][$k]) {
                        $errors[$k][] = $phpFileUploadErrors[$files['error'][$k]];
                    }

                    if (!$_isAllowed)   $errors[$k][] = $i18n->get('File type is not allowed.');
                    if (!$_sizeAllowed) $errors[$k][] = $i18n->get('File size is too big.');

                }

            }
        }

        return \compact('files', 'errors');
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
            $allowed = \str_replace([' ', ','], ['', '|'], \preg_quote(\is_array($allowed) ? \implode(',', $allowed) : $allowed));
        }

        return $allowed;

    }

}
