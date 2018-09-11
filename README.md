# Form Validation Addon for Cockpit

A form validator and form builder for [Cockpit](https://github.com/agentejo/cockpit)

Work in progress! Feel free to contribute with code, bug reports or feature requests.

## Installation

Add files to addons/FormValidation.

<del>Requires:</del>

* <del>PECL intl extension (for punycode conversion of urls and mail adresses)</del>

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
* honeypot: the field name must match the option `honeypot.fieldname`

## To do

* [ ] allow mail addresses with special chars (punycode) - they are valid, but `filter_var($to, FILTER_VALIDATE_EMAIL)` returns false
  * --> overwrite original submit function again or
  * --> change the mail validation in cockpit core
* [ ] i18n of error responses
* [ ] friendly error responses
* [ ] add a view to include via PHP frontend

Setup Mailer correct:

* [x] change mail subject in settings
* [ ] <del>prevent `<br>` to uppercase in Html2text</del>

matches:

  * [x] required (!empty)
  * [x] honeypot (humans wouldn't fill this field)
  * [x] type (mail, phone, url)
  * [x] !type (inverse type)
  * [ ] equals (= string) (for simple captchas or something like "Are you really sure? Type 'Yes'")
  * [ ] contains
    * [ ] code
    * [ ] url(s)
    * [ ] string
  
types:

  * [x] mail
  * [x] phone
  * [x] url
  * [ ] number
  * [ ] bool
  * [ ] ascii
  * [ ] date --> must be i18n specific
  * ...

## Screenshots

![formbuilder](https://user-images.githubusercontent.com/13042193/45387246-cb872400-b615-11e8-975a-5964e4b8a08b.png)

![validation_01](https://user-images.githubusercontent.com/13042193/45387250-cc1fba80-b615-11e8-9b7c-e8e04308a0f9.png)

![honeypot](https://user-images.githubusercontent.com/13042193/45387248-cc1fba80-b615-11e8-9ce6-81fc2993078a.png)

![responses](https://user-images.githubusercontent.com/13042193/45387249-cc1fba80-b615-11e8-95ea-f1bd4d9f8b35.png)