<?php

/**
 * Plugin Name: Pow Captcha
 * Description: Adds Pow Captcha verification to forms.
 * Version: 1.0.14
 * Author: Jean-Philippe Bidegain
 * Author URI: https://github.com/aeyoll/pow-captcha-for-wordpress
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('POW_CAPTCHA_VERSION', '1.0.14');

require_once __DIR__ . '/vendor/autoload.php';

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Settings;
use Aeyoll\PowCaptchaForWordpress\Admin;
use Aeyoll\PowCaptchaForWordpress\CliCommands;
use Aeyoll\PowCaptchaForWordpress\Modules\ContactForm7;
use Aeyoll\PowCaptchaForWordpress\Modules\GravityForms;

// This creates the singleton instance
if (is_null(Core::$instance)) {
    $pow_captcha_plugin_instance = new Core();
    $pow_captcha_plugin_instance->init();

    $settings = new Settings();
    $settings->init();

    $admin = new Admin();
    $admin->init();

    if (class_exists('WPCF7_Submission')) {
        $cf7 = new ContactForm7();
        $cf7->init();
    }

    if (class_exists('GFCommon')) {
        $gf = new GravityForms();
        $gf->init();
    }

    // Register WP-CLI commands if WP-CLI is available
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::add_command('pow-captcha', CliCommands::class);
    }
}
