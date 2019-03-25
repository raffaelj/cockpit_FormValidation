<?php
/**
 * Form validator and form builder for Cockpit CMS
 * 
 * @see       https://github.com/raffaelj/cockpit_FormValidation/
 * @see       https://github.com/agentejo/cockpit/
 * 
 * @version   0.2.4
 * @author    Raffael Jesche
 * @license   MIT
 * @note      work in progress
 */

// controller autoload and path short codes need a folder naming pattern
// return if addon folder has wrong name, e. g. "cockpit_AddonName"
$name = 'FormValidation';

if (!isset($app['modules'][strtolower($name)])) {

    // display a warning on top of admin ui
    $app->on('app.layout.contentbefore', function() use ($name) {
        echo '<p><span class="uk-badge uk-badge-warning"><i class="uk-margin-small-right uk-icon-warning"></i>' . $name . '</span> You have to rename the addon folder <code>' . basename(__DIR__) . '</code> to <code>' . $name . '</code>.</p>';
    });

    return;
}

// init + load i18n
$app('i18n')->locale = $app->retrieve('i18n', 'en');
$locale = $app->module('cockpit')->getUser('i18n', $app('i18n')->locale);

if ($translationspath = $app->path("#config:formvalidation/i18n/{$locale}.php")) {
    $app('i18n')->locale = $locale;
    $app('i18n')->load($translationspath, $locale);
}

// validation
$app->on('forms.submit.before', function($form, &$data, $frm, &$options) {

    if (isset($frm['validate']) && $frm['validate']) {

        // load validation class
        require_once(__DIR__ . '/Controller/FormValidation.php');

        $validated = new Forms\Controller\FormValidation($this, $data, $frm);

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
    
    $options['subject'] = isset($frm['email_subject']) && !empty($frm['email_subject']) ? $this->module('formvalidation')->map($frm['email_subject'], $data) : "New form data for: {$formname}";
    
    // add reply_to
    if (isset($frm['reply_to']) && !empty($frm['reply_to']) && isset($data[$frm['reply_to']]) && filter_var(idn_to_ascii(trim($data[$frm['reply_to']])), FILTER_VALIDATE_EMAIL) ) {
        
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
          
            if( array_key_exists($key, $labels) ){

                $label = htmlspecialchars($labels[$key]);

            } else {

                $label = $key;

            }

            $out[$label] = $val;
          
        }
        
        return $out;
        
    },
    /*
    'createMailTemplate' => function($name) {
        
        // to do...
        
    }
    */
]);

// ADMIN
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
    include('admin.php');
}
