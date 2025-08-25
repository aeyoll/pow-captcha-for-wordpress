# Pow Captcha

A WordPress plugin that adds Pow Captcha verification to forms.

## Description

This plugin allows you to validate Contact Form 7 forms and Gravity Forms using Pow Captcha.

- **Contributors:** aeyoll
- **Tags:** captcha, security, forms, contact-form-7, gravity-forms
- **Requires at least:** WordPress 5.0
- **Tested up to:** WordPress 6.8
- **Requires PHP:** 7.4+
- **Stable tag:** 1.0.18
- **License:** GPLv2 or later
- **License URI:** https://www.gnu.org/licenses/gpl-2.0.html

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

## Installation

1. Upload the plugin files to the `/wp-content/plugins/pow-captcha` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Pow Captcha screen to configure the plugin.
4. The plugin will automatically integrate with supported form plugins.

## WP-CLI Commands

```sh
# Configure the plugin
wp pow-captcha set_api_key "your-secret-api-key"
wp pow-captcha set_api_url "https://your-captcha-service.com"
wp pow-captcha set_enable_on_admin_login_form true

# Check configuration
wp pow-captcha status

# Clear cache when needed
wp pow-captcha clear_cache
```
