<?php

namespace Aeyoll\PowCaptchaForWordpress;

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

        $this->plugin_name = 'pow-captcha-for-wordpress';

        self::$instance = $this;
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

    public function get_contact_form_7_active()
    {
        return true;
    }
}
