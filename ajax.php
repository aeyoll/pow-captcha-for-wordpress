<?php

require_once __DIR__ . '/../../../wp-config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Widget;

$pow_captcha_plugin_instance = new Core();
$pow_captcha_plugin_instance->init();

$cf7 = new Widget();
echo $cf7->pow_captcha_generate_widget_tag_from_plugin($pow_captcha_plugin_instance);
