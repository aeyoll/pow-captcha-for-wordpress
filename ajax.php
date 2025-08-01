<?php

require_once __DIR__ . '/../../../wp-config.php';
require_once ABSPATH . '/vendor/autoload.php';

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Widget;

$pow_captcha_plugin_instance = new Core();
$pow_captcha_plugin_instance->init();

$cf7 = new Widget();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

echo wp_kses($cf7->pow_captcha_generate_widget_tag_from_plugin($pow_captcha_plugin_instance), [
    'input' => [
        'type' => [],
        'name' => [],
        'value' => [],
    ],
    'div' => [
        'class' => [],
        'data-sqr-captcha-url' => [],
        'data-sqr-captcha-challenge' => [],
        'data-sqr-captcha-callback' => [],
    ],
]);
