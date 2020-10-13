<?php
/**
 * Form validator and form builder for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_FormValidation/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.2.11
 * @author    Raffael Jesche
 * @license   MIT
 * @note      work in progress
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

    // custom mailer settings
    if (!empty($frm['mailer'])) {

        // overwrite mailer service
        $this->service('mailer', function() use($frm){
            $mailer    = new \Mailer($frm['mailer']['transport'] ?? 'mail', $frm['mailer']);
            return $mailer;
        });

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

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include_once(__DIR__.'/admin.php');
}
