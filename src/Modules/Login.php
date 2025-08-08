<?php

namespace Aeyoll\PowCaptchaForWordpress\Modules;

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Widget;

class Login
{
    protected $widget;

    public function init()
    {
        $this->widget = new Widget();

        add_action('login_form', [$this, 'render_captcha_placeholder']);
        add_action('login_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('authenticate', [$this, 'validate_captcha_on_authenticate'], 30, 3);
    }

    public function enqueue_scripts()
    {
        $plugin = Core::$instance;

        if (!$plugin || !$plugin->is_configured() || !$plugin->get_enable_on_admin_login_form()) {
            return;
        }

        $this->widget->pow_captcha_enqueue_widget_assets();
    }

    public function render_captcha_placeholder()
    {
        $plugin = Core::$instance;

        if (!$plugin || !$plugin->is_configured() || !$plugin->get_enable_on_admin_login_form()) {
            return;
        }

        echo $this->widget->pow_captcha_placeholder();
    }

    public function validate_captcha_on_authenticate($user, $username, $password)
    {
        if (is_wp_error($user)) {
            return $user;
        }

        $plugin = Core::$instance;

        if (!$plugin || !$plugin->is_configured() || !$plugin->get_enable_on_admin_login_form()) {
            return $user;
        }

        if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            return $user;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return $user;
        }

        if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
            return $user;
        }

        $challenge = isset($_POST['challenge']) ? sanitize_text_field(wp_unslash($_POST['challenge'])) : '';
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

        if ($challenge === '' || $nonce === '') {
            return new WP_Error('invalid_captcha', __('Please complete the captcha', 'pow-captcha'));
        }

        $is_valid = $this->widget->validate_captcha($challenge, $nonce);

        if (!$is_valid) {
            return new WP_Error('invalid_captcha', __('Captcha verification failed', 'pow-captcha'));
        }

        return $user;
    }
}
