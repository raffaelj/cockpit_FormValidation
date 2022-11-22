<?php
/**
 * Form validator and form builder for Cockpit CMS
 *
 * @see       https://github.com/raffaelj/cockpit_FormValidation/
 * @see       https://github.com/agentejo/cockpit/
 *
 * @version   0.3.1
 * @author    Raffael Jesche
 * @license   MIT
 */

$this->helpers['validator'] = 'FormValidation\\Helper\\Validator';

// init + load i18n
$locale = $app->module('cockpit')->getUser('i18n', $app('i18n')->locale);

if ($translationspath = $app->path("#config:formvalidation/i18n/{$locale}.php")) {
    $app('i18n')->load($translationspath, $locale);
}

// validation
$app->on('forms.submit.before', function($form, &$data, $frm, &$options) {

    if (isset($frm['validate']) && $frm['validate']) {

        $validated = $this('validator')->init($data, $frm);

        // send 404 to sender
        if (false === $validated->response()) {
            $this->stop(404);
        }

        // continue if true or send error messages to sender
        if (true !== $validated->response()) {

            $return = ['error' => $validated->response(), 'data' => $validated->data];

            // throw Exception when using cockpit as library
            if (!COCKPIT_API_REQUEST && !COCKPIT_ADMIN) {
                throw new Exception(json_encode($return));
            }

            $this->stop($return, 412);

        }

    }

    // mail subject
    $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;

    if (isset($frm['email_subject']) && !empty($frm['email_subject'])) {
        $options['subject'] = $this->module('formvalidation')->map($frm['email_subject'], $data);
    } else {
        $options['subject'] = "New form data for: {$formname}";
    }

    // add reply_to
    if (isset($frm['reply_to'])
        && !empty($frm['reply_to'])
        && isset($data[$frm['reply_to']])
        && \filter_var(
            \idn_to_ascii(
                \trim($data[$frm['reply_to']])
                , IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46
            ), FILTER_VALIDATE_EMAIL)
        ) {

        $options['reply_to'] = trim($data[$frm['reply_to']]);

    }

    // TOOD: add altMessage
    // $options['altMessage'] = "...";

}, 100);

$app->module('formvalidation')->extend([

    'map' => function($str = null, $datamap = []) {

        if (!is_string($str)) return;

        $pattern = '{{%s}}';

        $datamap['app.name'] = $this->app['app.name'];
        $datamap['site_url'] = $this->app['site_url'];

        $map = [];
        foreach($datamap as $var => $value){
            $map[sprintf($pattern, $var)] = $value;
        }

        $out = strtr($str, $map);

        return $out;

    },

    'nameToLabel' => function($data = [], $frm = []) {

        if (!isset($frm['fields']))
            return $data;

        $labels = array_column($frm['fields'], 'label', 'name');

        $out = [];

        foreach($data as $key => $val){

            if( array_key_exists($key, $labels) && !empty($labels[$key]) ){

                $label = htmlspecialchars($labels[$key]);

            } else {

                $label = $key;

            }

            $out[$label] = $val;

        }

        return $out;

    },

]);


/**
 * Extend core "Forms" module behavior.
 */
$this->module('forms')->extend([

    /**
     * Override core "Forms::submit" behavior.
     *
     * Implemented proposed changes from Raruto,
     * added option to overwrite email_forward
     *
     * @see https://github.com/agentejo/cockpit/pull/1399
     * @see https://github.com/Raruto/cockpit-extended-forms
     */
    'submit' => function($form, $data, $options = []) {

        $frm = $this->form($form);

        // Invalid form name
        if (!$frm) {
            return false;
        }

        // Load custom form validator
        if ($this->app->path("#config:forms/{$form}.php") && false === include($this->app->path("#config:forms/{$form}.php"))) {
            return false;
        }

        // Filter submitted data
        $this->app->trigger('forms.submit.before', [$form, &$data, $frm, &$options]);

        // Invalid form data
        if (empty($data)) {
            return false;
        }

        // Send email
        if (isset($frm['email_forward']) && $frm['email_forward']) {

            // overwrite email_forward
            if (!empty(getenv('EMAIL_FORWARD'))) {
                $frm['email_forward'] = getenv('EMAIL_FORWARD');
            }

            $emails          = array_map('trim', explode(',', $frm['email_forward']));
            $filtered_emails = [];

            // Validate each email address individually, push if valid
            foreach ($emails as $to){
                if ($this->app->helper('utils')->isEmail($to)){
                    $filtered_emails[] = $to;
                }
            }

            if (count($filtered_emails)) {

                $frm['email_forward'] = implode(',', $filtered_emails);

                $body = null;

                // Load custom email template
                if ($template = $this->app->path("#config:forms/emails/{$form}.php")) {
                    $originalLayout = $this->app->layout;
                    $this->app->layout = false;
                    $body = $this->app->view($template, ['data' => $data, 'frm' => $frm]);
                    $this->app->layout = $originalLayout;
                }

                // Filter email content
                $this->app->trigger('forms.submit.email', [$form, &$data, $frm, &$body, &$options]);

                // Fallback to default email template
                if (empty($body)) {
                    $originalLayout = $this->app->layout;
                    $this->app->layout = false;
                    $body = $this->app->view("formvalidation:templates/emails/contactform.php", ['data' => $data, 'frm' => $frm]);
                    $this->app->layout = $originalLayout;
                }

                $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;
                $to       = $frm['email_forward'];
                $subject  = $options['subject'] ?? $this->app->helper('i18n')->getstr("New form data for: %s", [$formname]);

                try {
                    $response = $this->app->mailer->mail($to, $subject, $body, $options);
                } catch (\Exception $e) {
                    $response = $e->getMessage();
                }
            }
        }

        // Push entry to database
        if (isset($frm['save_entry']) && $frm['save_entry']) {
            $entry = ['data' => $data];
            $this->save($form, $entry);
        }

        // Generate response array
        $response = (isset($response) && $response !== true) ? ['error' => $response, 'data' => $data] : $data;

        // Filter submission response
        $this->app->trigger('forms.submit.after', [$form, &$data, $frm, &$response]);

        return $response;
    }
]);

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
