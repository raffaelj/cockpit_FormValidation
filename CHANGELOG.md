# Changelog

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