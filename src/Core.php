<?php

namespace Aeyoll\PowCaptchaForWordpress;

use Aeyoll\PowCaptchaForWordpress\Widget;

final class Core
{
    /**
     * Singleton global instance
     */
    public static $instance;
    public $plugin_name;


    // Global constants
    public static $version;
    public static $option_group = 'pow_captcha_options';
    public static $option_captcha_api_token = 'captcha_api_token';
    public static $option_captcha_api_url = 'captcha_api_url';
    public static $option_enable_on_login_form = 'enable_on_login_form';
    public static $option_difficulty_level = 'pow_captcha_difficulty_level';
    public static $default_difficulty_level = 5;

    /**
     * Initializes the Core class.
     */
    public function init()
    {
        if (defined('POW_CAPTCHA_VERSION')) {
            self::$version = POW_CAPTCHA_VERSION;
        } else {
            self::$version = '0.0.0';
        }

        $this->plugin_name = 'pow-captcha';

        self::$instance = $this;

        // Register AJAX handlers
        add_action('wp_ajax_pow_captcha_get_widget', [$this, 'ajax_get_widget']);
        add_action('wp_ajax_nopriv_pow_captcha_get_widget', [$this, 'ajax_get_widget']);
    }

    /**
     * Checks if the plugin is configured.
     *
     * @return bool Returns true if the plugin is configured, false otherwise.
     */
    public function is_configured()
    {
        return (
            $this->get_captcha_api_token() !== null && $this->get_captcha_api_token() !== '' &&
            $this->get_captcha_api_url() !== null && $this->get_captcha_api_url() !== ''
        );
    }

    /**
     * Retrieves the captcha API token.
     *
     * @return string|null Returns the captcha API token if set, null otherwise.
     */
    public function get_captcha_api_token()
    {
        return trim(get_option(self::$option_captcha_api_token));
    }

    /**
     * Retrieves the captcha API URL.
     *
     * @return string|null Returns the captcha API URL if set, null otherwise.
     */
    public function get_captcha_api_url()
    {
        return trim(get_option(self::$option_captcha_api_url));
    }

    /**
     * Retrieves the enable on admin login form option.
     *
     * @return bool|null Returns the enable on admin login form option if set, null otherwise.
     */
    public function get_enable_on_login_form()
    {
        return get_option(self::$option_enable_on_login_form);
    }

    /**
     * Retrieves the difficulty level for the captcha.
     *
     * @return int Returns the difficulty level (defaults to 5).
     */
    public function get_difficulty_level(): int
    {
        $level = get_option(self::$option_difficulty_level, self::$default_difficulty_level);

        return (int) $level;
    }

    /**
     * Retrieves the contact form 7 active option.
     *
     * @return bool|null Returns the contact form 7 active option if set, null otherwise.
     */
    public function get_contact_form_7_active()
    {
        return true;
    }

    /**
     * AJAX handler for getting the captcha widget.
     */
    public function ajax_get_widget()
    {
        // Set cache headers
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $widget = new Widget();
        $html = $widget->pow_captcha_generate_widget_tag_from_plugin($this);

        if ($html) {
            echo wp_kses($html, [
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
        }

        wp_die();
    }
}
