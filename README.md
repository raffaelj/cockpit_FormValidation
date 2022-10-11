# Form Validation Addon for Cockpit

**This addon is not compatible with Cockpit CMS v2.**

See also [Cockpit CMS v1 docs](https://v1.getcockpit.com/documentation), [Cockpit CMS v1 repo](https://github.com/agentejo/cockpit) and [Cockpit CMS v2 docs](https://getcockpit.com/documentation/), [Cockpit CMS v2 repo](https://github.com/Cockpit-HQ/Cockpit).

---

A form validator and form builder for [Cockpit CMS](https://github.com/agentejo/cockpit)

Work in progress! Feel free to contribute with code, bug reports or feature requests.

## Installation

Copy this repository into `/addons` and name it `FormValidation` or

```bash
cd path/to/cockpit
git clone https://github.com/raffaelj/cockpit_FormValidation.git addons/FormValidation
```

## Requirements:

* Cockpit version >= 0.7.2
* PECL intl extension (for punycode conversion of urls and mail adresses)

## Features

### Form builder

I used the cp-fieldsmanager, where all field types are available. In my tests I only used these types:

* text
* textarea
* boolean

It's meant for strings and I don't know (yet), what happens if it should validate arrays.

In the frontend it's possible to reuse some form options like "info", "label", "group", "lst", "width".

...

### Form Validator

* checks for required fields
* checks for content types
* sends response if validation fails

The idea is to add multiple checks on each field to trick spambots without using a captcha.

## some templating

* custom mail subject
* field for reply to

see screenshots below

## How to use

Create a field and click on "Validate" tab. Click on "Validate form data" to activate the validator.

When the validator is active, it checks, if required fields are present. If you want to allow sending fields, that aren't present in the form builder, you have to click "Allow extra fields".

To activate more validations, click on "Validate" for each field and add some json.

### mail field

Create a text field.

```json
{
  "type": {
    "mail": true,
    "url": false
  }
}
```

### Honeypot

Create a boolean field and name it "confirm". Spambots will love it :-D

```json
{
  "honeypot": {
    "fieldname": "confirm",
    "expected_value": "0",
    "response": "Hello spambot. A human wouldn't fill this field."
  }
}
```

If `"response": "404"`, sender gets a `404 Path not found` instead of a json response.

...



## defaults:

* if form validation is active
  * key names must be alphanumeric (a-zA-Z0-9) or '-' or '_'
  * check, if required fields are present
  * sending data with unknown field names is not allowed
* if field validation is active
  * no defaults, only specified validations

## Notes

* Validating to `type:{"phone":false}` could lead to false positives. The regex is meant to allow inputs like "0123 45678" or "+49 123-456-78", but "123" returns true, too.

## i18n

Add a lang file in `path/to/cockpit/config/formvalidation/i18n/de.php`

Sample for German translation:

```php
<?php

return [
    'is required' => 'ist ein Pflichtfeld',
    'does not exist' => 'existiert nicht',
    'Hello spambot' => 'Hallo Spambot',
    'must be mail' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
    'must be phone' => 'Bitte geben Sie eine Telefonnummer ein.',
    'must be url' => 'muss eine Url sein',
    'must not be mail' => 'In diesem Feld darf keine E-Mail-Adresse stehen (um Spambots zu verwirren).',
    'must not be phone' => 'In diesem Feld darf keine Telefonnummer stehen (um Spambots zu verwirren).',
    'must not be url' => 'In diesem Feld darf keine Url stehen (um Spambots zu verwirren).'
];
```

## To do

* [x] allow mail addresses with special chars (punycode) - they are valid, but `filter_var($to, FILTER_VALIDATE_EMAIL)` returns false
  * --> overwrite original submit function again or
  * --> change the mail validation in cockpit core --> [now in core](https://github.com/agentejo/cockpit/commit/745df212d02be2609b5d13ff81aaa4226f68fb32)
* [x] i18n of error responses
* [x] friendly error responses --> use i18n
* [ ] add a view to include via PHP frontend

matches:

  * [x] required (!empty)
  * [x] honeypot (humans wouldn't fill this field)
  * [x] type (mail, phone, url)
  * [x] !type (inverse type)
  * [x] equals (= string) (for simple captchas or something like "Are you really sure? Type 'Yes'")
  * [ ] contains
    * [ ] code
    * [ ] url(s)
    * [ ] string
  
types:

  * [x] mail
  * [x] phone
  * [x] url
  * [x] number
  * [ ] bool
  * [ ] ascii
  * [ ] date --> must be i18n specific
  * ...

## Form Mail Template Example

Create a custom mail template in `config/forms/emails/formname.php` to use the settings `email_text_before` and `email_text_after`.

[Example](/templates/emails/contactform.php)

## Screenshots

![formbuilder](https://user-images.githubusercontent.com/13042193/45387246-cb872400-b615-11e8-975a-5964e4b8a08b.png)

![validation_01](https://user-images.githubusercontent.com/13042193/45387250-cc1fba80-b615-11e8-9b7c-e8e04308a0f9.png)

![honeypot](https://user-images.githubusercontent.com/13042193/45387248-cc1fba80-b615-11e8-9ce6-81fc2993078a.png)

![responses](https://user-images.githubusercontent.com/13042193/45387249-cc1fba80-b615-11e8-95ea-f1bd4d9f8b35.png)

## Credits and third party libraries

Icons are from Cockpit CMS v2, (c) Artur Heinze, MIT License
