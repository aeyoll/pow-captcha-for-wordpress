=== Pow Captcha ===
Contributors: aeyoll
Tags: captcha, security, forms, contact-form-7, gravity-forms
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.1
Stable tag: 1.0.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that adds Pow Captcha verification to forms.

== Description ==

This plugin allows you to validate Contact Form 7 forms and Gravity Forms using Pow Captcha.

== Requirements ==

PHP 7.1+ is needed to use this module.

== Installation ==

Require the plugin with Composer using the following command:

```sh
composer require aeyoll/pow-captcha-for-wordpress
```

Note, you must have "composer/installers" allowed in your `composer.json` order to property install the plugin:

```json
{
    "name": "your/project",
    "require": {
        "aeyoll/pow-captcha-for-wordpress": "^1.0",
        "composer/installers": "^2.0"
    },
    "config": {
        "allow-plugins": {
            "composer/installers": true
        }
    }
}
```
