<?php

namespace Aeyoll\PowCaptchaForWordpress;

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\FileCache;
use GuzzleHttp\Client;

/**
 * Class Widget
 * Represents a widget for the POW Captcha plugin in WordPress.
 */
final class Widget
{
    protected $client;

    /**
     * Retrieves the HTTP client for making API requests.
     *
     * @return Client The GuzzleHttp client instance.
     */
    public function get_client()
    {
        if (!$this->client) {
            $plugin = Core::$instance;
            $api_url = $plugin->get_captcha_api_url();
            $api_token = $plugin->get_captcha_api_token();

            $this->client = new Client([
                'base_uri' => $api_url,
                'timeout'  => 5.0,
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $api_token),
                ],
            ]);
        }

        return $this->client;
    }

    /**
     * Loads the challenges from the API.
     *
     * @return array|null The challenges data as an associative array.
     */
    public function load_challenges()
    {
        $response = $this->get_client()->post('GetChallenges?difficultyLevel=5');
        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        return $data;
    }

    /**
     * Retrieves a challenge from the cache or loads it from the API.
     *
     * @return string|null The challenge string.
     */
    public function get_challenge()
    {
        // Define the cache key for storing the challenges
        $cache_key = 'pow_captcha_for_wordpress_challenges';

        // Generate a directory path to store the cache
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pow_captcha_for_wordpress-' . md5(__DIR__);

        // Create a new filesystem cache
        $cache = new FileCache(['cache_dir' => $directory]);

        // Retrieve the challenges from the cache using the cache key
        $challenges = $cache->get($cache_key);

        if ($challenges === false) {
            // If the challenges are not found in the cache, load them from the API
            $challenges = $this->load_challenges();
        } else {
            if (count($challenges) < 5) {
                // If the number of challenges is less than 5, reload them from the API
                $challenges = $this->load_challenges();
            }
        }

        // Get the first challenge from the array
        $challenge = array_shift($challenges);

        // Save the remaining challenges back to cache (expires in 1 hour)
        $cache->save($cache_key, $challenges, 3600);

        // Return the retrieved challenge
        return $challenge;
    }


    /**
     * Validates the CAPTCHA challenge and nonce.
     *
     * @param string $challenge The CAPTCHA challenge.
     * @param string $nonce The nonce value.
     *
     * @return bool Indicates whether the CAPTCHA is valid or not.
     */
    public function validate_captcha($challenge, $nonce): bool
    {
        // If the challenge or the nonce is empty, captcha will never be valid
        if (!$challenge || !$nonce) {
            return false;
        }

        $url = sprintf('Verify?challenge=%s&nonce=%s', $challenge, $nonce);

        try {
            $response = $this->get_client()->post($url);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Enqueues the necessary scripts and styles for the POW Captcha widget.
     */
    public function pow_captcha_enqueue_widget_assets()
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured()) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'pow-captcha-css',
            plugin_dir_url(__FILE__) . '../assets/css/pow-captcha.css',
            array(),
            '1.0.6'
        );

        // Enqueue JS
        wp_enqueue_script(
            'pow-captcha-lib-js',
            $plugin->get_captcha_api_url() . '/static/captcha.js',
            array(),
            '1.0.6',
            true
        );

        wp_enqueue_script(
            'pow-captcha-js',
            plugin_dir_url(__FILE__) . '../assets/js/pow-captcha.js',
            array(),
            '1.0.6',
            true
        );
    }

    /**
     * Generates the HTML placeholder for the POW Captcha widget.
     *
     * @return string The HTML code for the placeholder.
     */
    public function pow_captcha_placeholder()
    {
        return '<div class="pow-captcha-placeholder"></div>';
    }

    /**
     * Generates the widget tag using the plugin's configuration.
     *
     * @param Core $plugin The plugin instance.
     *
     * @return string|null The generated widget tag.
     */
    public function pow_captcha_generate_widget_tag_from_plugin(Core $plugin)
    {
        if (!$plugin->is_configured()) {
            return;
        }

        $api_url = $plugin->get_captcha_api_url();
        $challenge = $this->get_challenge();

        return $this->pow_captcha_generate_widget_tag($api_url, $challenge);
    }

    /**
     * Generates the widget tag using the provided API URL and challenge.
     *
     * @param string $api_url The API URL for the POW Captcha.
     * @param string $challenge The CAPTCHA challenge.
     *
     * @return string The generated widget tag.
     */
    public function pow_captcha_generate_widget_tag(string $api_url, string $challenge)
    {
        $form = '<input type="hidden" name="challenge" value="' . esc_attr($challenge) . '" />
        <input type="hidden" name="nonce" />
        <div class="captcha-container"
             data-sqr-captcha-url="' . esc_url($api_url) . '"
             data-sqr-captcha-challenge="' . esc_attr($challenge) . '"
             data-sqr-captcha-callback="myCaptchaCallback">
        </div>';

        return $form;
    }
}
