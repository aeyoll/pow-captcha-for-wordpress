<?php

/**
 * Plugin Name: POW CAPTCHA for WordPress
 * Description: Adds POW CAPTCHA verification to WordPress.
 * Version: 1.0.0
 * Author: Jean-Philippe Bidegain
 * Author URI: https://github.com/aeyoll/pow-captcha-for-wordpress
 * Domain Path: /languages
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

define('POW_CAPTCHA_VERSION', '1.O.0');

require_once __DIR__ . '/vendor/autoload.php';

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Settings;
use Aeyoll\PowCaptchaForWordpress\Admin;
use Aeyoll\PowCaptchaForWordpress\Modules\ContactForm7;

// This creates the singleton instance
if (!defined(Core::$instance)) {
    $pow_captcha_plugin_instance = new Core();
    $pow_captcha_plugin_instance->init();

    $settings = new Settings();
    $settings->init();

    $admin = new Admin();
    $admin->init();

    $cf7 = new ContactForm7();
    $cf7->init();
}
