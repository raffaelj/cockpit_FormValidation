# Form Validation Addon for Cockpit

work in progress

## Installation

Add files to addons/FormValidation.

Requires:

* PECL intl extension (for punycode conversion of urls and mail adresses)

## Features

### Form builder

I used the cp-fieldsmanager, where all field types are available. In my tests I only used these types:

* text
* textarea
* boolean

It's meant for strings and I don't know (yet), what happens if it should validate arrays.

In the frontend its possible to reuse some form options like "info", "label", "group", "lst", "width".

...

### Form Validator

* checks for required fields
* checks for content types
* sends response if validation fails

The idea is to add multiple checks on each field to trick spambots without using a captcha.

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

Create a boolean field and name it "confirm".

```json
{
  "honeypot": {
    "fieldname": "confirm",
    "expected_value": 0,
    "response": "Hello spambot. A human wouldn't fill this field."
  }
}
```

If `"response": "404"`, sender gets a 404 Path not found instead of a json response.

...



## defaults:

  * if form validation is active
    * key names must be alphanumeric (a-zA-Z0-9) or '-' or '_'
    * validate if required fields are present
  * if field validation is active
    * (trim)
    * 

## Notes

* validating to !phone could lead to false positives
* honeypot: the field name must match the option honeypot.fieldname

## To do

* [ ] i18n of error responses
* [ ] friendly error responses

Setup Mailer correct:

* [ ] change mail subject in settings
* [ ] prevent `<br>` to uppercase in Html2text

matches:

  * [x] required (!empty)
  * [x] honeypot (humans wouldn't fill this field)
  * [x] type (mail, phone, url)
  * [x] !type (inverse type)
  * [ ] = string (for simple captchas or something like "Are you really sure? Type 'Yes'")
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