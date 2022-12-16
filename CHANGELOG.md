# Changelog

## 0.4.1

* UI cleanup
* messages and i18n cleanup
* code refactoring (`Helper/Validator.php`)
* removed loading custom i18n files - Merge your existing formvalidation i18n files with cockpit's default i18n file and/or use the [Babel addon](https://github.com/raffaelj/cockpit_Babel).

## 0.4.0

* changed priority of validation event on `forms.submit.before` to 100 (before it used the default: 0)
* added event `forms.validate.after`
* changed core `Forms::submit()` method
  * implemented proposals from @raruto - inspired by ExtendedForms addon
    * use default mail template from addon
    * added `forms.submit.email` event
    * changed `forms.submit.after` event (now with `$response` argument)
    * return `false` on empty data after `forms.submit.before` event
  * added event `forms.submit.save`
* fixed possible array to string conversion if `validate_and_touch_data` is activated
* added notes, how to install addon via composer/git/cp cli
* added ability to upload files
  * added file field to GUI
  * added methods to validate and store uploaded files
  * changed core Mailer service to enable attachments with filenames from `$_FILES` with `tmp_name` and `name`
* fixed updating issues from modal (quick edit tab, advanced tab in modal, object field, key-valu-pair field)
* improved default form template

## 0.3.1

* added quick edit tab

## 0.3.0

* equals and equalsi filter can now contain arrays
* dropped experimental mailer settings
* improved UI
* changed honeypot error key to `honeypot` instead of custom field name
* added ability to change `email_forward` via env variable (had to overwrite core `Forms::submit()` method)
* added icons (from Cockpit v2)
* added multipleselect field
* added "contentblock" wysiwyg field
* removed `group` option (may be reimplemented again)
* added json inspectobject
* added options for custom error messages (used in CpMultiplane)

## 0.2.11

* intl extension is now optional --> doesn't throw errors anymore if `idn_to_ascii` is not available, but still required to validate punycode urls correctly

## 0.2.10

* added equalsi filter (case insensitive equals filter)
* fixed idn_to_ascii deprecation notice in PHP 7.3
* fixed missing `__DIR__` in admin include
* added composer.json

## 0.2.9

* added form template functionality
* added filters: equal, number
* replaced `in_menu` toggle with event `forms.settings.aside`
* minor fixes and ui changes

## 0.2.8

* added option to link collection item (intended use: link a page with privacy notice)
* minor changes

## 0.2.7

* added option to copy email template into `/config/forms/email/formname.php`
* changed honeypot detection - should be backwards compatible
* improved GUI
  * added tab for attributes
  * added field type honeypot with predefined options
  * added select field to reply-to
* cleanup
* moved changelog from README.md to CHANGELOG.md

## 0.2.6

* fixed overwriting current locale (broke multilingual setups in the past)

## 0.2.5

* added custom fieldsmanager to prevent choosing unsupported field types
* fixed route without name - now the form manager is active when creating a new form, too
* minor cleanup

## 0.2.4

* moved addon files to root
* minor changes

## 2018-11-16

* improved Admin.php overwrite
* added version numbers and git tags
* fixed empty forms index page in upcoming Cockpit v0.8.4 [original changed](https://github.com/agentejo/cockpit/commit/fd3dbe69247f62db033fa7eeae69c5c098e29e44#diff-043b1f3bccf6ef55f3cda2918e79daae)

## 2018-11-02

* added i18n
* now throws response as an Exception if using Cockpit as a library

## 2018-10-22

* moved addon to subfolder
* disabled experimental custom #config path
* added warning if global mailer settings aren't defined
* added custom mailer settings to define individual mailers per form
