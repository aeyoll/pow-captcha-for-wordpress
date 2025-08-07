=== Pow Captcha ===
Contributors: aeyoll
Tags: captcha, security, forms, contact-form-7, gravity-forms
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.16
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds Pow Captcha verification to forms to prevent spam and bot submissions.

== Description ==

Pow Captcha is a WordPress plugin that adds Proof of Work (PoW) captcha verification to your forms. This provides an effective way to prevent spam and bot submissions without requiring users to solve visual puzzles or complete complex challenges.

The plugin integrates seamlessly with popular form plugins including:

* Contact Form 7
* Gravity Forms
* And more coming soon

Features:

* Lightweight and fast
* No visual puzzles for users
* Effective spam protection
* Easy integration with existing forms
* Customizable difficulty levels
* Developer-friendly with hooks and filters

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/pow-captcha` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Pow Captcha screen to configure the plugin.
4. The plugin will automatically integrate with supported form plugins.

== Frequently Asked Questions ==

= How does Pow Captcha work? =

Pow Captcha uses a Proof of Work algorithm that requires browsers to perform a computational task before submitting forms. This is transparent to users but effectively blocks automated bot submissions.

= Is it compatible with my form plugin? =

Currently, the plugin supports Contact Form 7 and Gravity Forms. More integrations are planned for future releases.

== WP-CLI commands ==

```sh
# Configure the plugin
wp pow-captcha set-api-key "your-secret-api-key"
wp pow-captcha set-api-url "https://your-captcha-service.com"

# Check configuration
wp pow-captcha status

# Clear cache when needed
wp pow-captcha clear-cache
```
