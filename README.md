# Pow Captcha for WordPress

This plugin allows you to validate Contact Form 7 forms using Pow Captcha.

Requirements
---

PHP 7.1+ is needed to use this module.

Installation
---

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
