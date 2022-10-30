<?php
/**
 * Form validator and form builder for Cockpit CMS
 *
 * @see       https://github.com/raffaelj/cockpit_FormValidation/
 * @see       https://github.com/agentejo/cockpit/
 *
 * @version   0.3.0
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
        if (false == $validated->response()) {
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

    // add altMessage
    // $options['altMessage'] = "...";

    // to do...

});

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

// overwrite default submit method of forms module to change email_forward via env variable
$this->module('forms')->extend([

    'submit' => function($form, $data, $options = []) {

        $frm = $this->form($form);

        if (!$frm) {
            return false;
        }

        // custom form validation
        if ($this->app->path("#config:forms/{$form}.php") && false===include($this->app->path("#config:forms/{$form}.php"))) {
            return false;
        }

        $this->app->trigger('forms.submit.before', [$form, &$data, $frm, &$options]);

        if (isset($frm['email_forward']) && $frm['email_forward']) {

            if (!empty(getenv('EMAIL_FORWARD'))) {
                $frm['email_forward'] = getenv('EMAIL_FORWARD');
            }

            $emails          = array_map('trim', explode(',', $frm['email_forward']));
            $filtered_emails = [];

            foreach ($emails as $to){

                // Validate each email address individually, push if valid
                if ($this->app->helper('utils')->isEmail($to)){
                    $filtered_emails[] = $to;
                }
            }

            if (count($filtered_emails)) {

                $frm['email_forward'] = implode(',', $filtered_emails);

                // There is an email template available
                if ($template = $this->app->path("#config:forms/emails/{$form}.php")) {

                    $body = $this->app->renderer->file($template, ['data' => $data, 'frm' => $frm], false);

                // Prepare template manually
                } else {

                    $body = [];

                    foreach ($data as $key => $value) {
                        $body[] = "<b>{$key}:</b>\n<br>";
                        $body[] = (is_string($value) ? $value:json_encode($value))."\n<br>";
                    }

                    $body = implode("\n<br>", $body);
                }

                $formname = isset($frm['label']) && trim($frm['label']) ? $frm['label'] : $form;

                try {
                    $response = $this->app->mailer->mail($frm['email_forward'], $options['subject'] ?? "New form data for: {$formname}", $body, $options);
                } catch (\Exception $e) {
                    $response = $e->getMessage();
                }
            }
        }

        if (isset($frm['save_entry']) && $frm['save_entry']) {
            $entry = ['data' => $data];
            $this->save($form, $entry);
        }

        $this->app->trigger('forms.submit.after', [$form, &$data, $frm]);

        return (isset($response) && $response !== true) ? ['error' => $response, 'data' => $data] : $data;
    }
]);

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
