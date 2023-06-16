<?php

namespace Aeyoll\PowCaptchaForWordpress;

use Aeyoll\PowCaptchaForWordpress\Core;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

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

        // Create a new filesystem cache adapter, caching the values for a month
        $cache = new FilesystemAdapter('pow_captcha_for_wordpress', 30 * 24 * 3600);

        // Retrieve the challenges from the cache using the cache key
        $challengesCache = $cache->getItem($cache_key);

        if (!$challengesCache->isHit()) {
            // If the challenges are not found in the cache, load them from the API
            $challenges = $this->load_challenges();
        } else {
            // If the challenges are found in the cache, retrieve them
            $challenges = $challengesCache->get();

            if (count($challenges) < 5) {
                // If the number of challenges is less than 5, reload them from the API
                $challenges = $this->load_challenges();
            }
        }

        // Get the first challenge from the array
        $challenge = array_shift($challenges);

        // Update the challenges cache with the remaining challenges
        $challengesCache->set($challenges);

        // Save the challenges cache
        $cache->save($challengesCache);

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
        $url = sprintf('Verify?challenge=%s&nonce=%s', $challenge, $nonce);

        try {
            $response = $this->get_client()->post($url);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Enqueues the necessary scripts for the POW Captcha widget.
     */
    public function pow_captcha_enqueue_widget_scripts()
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured()) {
            return;
        }

        wp_enqueue_script(
            'pow-captcha',
            $plugin->get_captcha_api_url() . '/static/captcha.js',
            array(),
            '1.0',
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
        return <<<EOD
            <div id="pow-captcha-placeholder"></div>

            <script>
                window.myCaptchaCallback = (nonce) => {
                    document.querySelector("form input[name='nonce']").value = nonce;
                    document.querySelector("form input[type='submit']").disabled = false;
                };

                const url = '/wp-content/plugins/pow-captcha-for-wordpress/ajax.php';
                const elementId = 'pow-captcha-placeholder';

                fetch(url)
                .then(response => response.text())
                .then(html => {
                    const element = document.getElementById(elementId);
                    element.innerHTML = html;
                    window.sqrCaptchaInit();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            </script>
        EOD;
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
        $form = <<<EOD
        <style>
        .sqr-captcha-hidden {
            display: none !important;
        }
        </style>
        <input type="hidden" name="challenge" value="$challenge" />
        <input type="hidden" name="nonce" />
        <div class="captcha-container"
             data-sqr-captcha-url="$api_url"
             data-sqr-captcha-challenge="$challenge"
             data-sqr-captcha-callback="myCaptchaCallback">
        </div>
        EOD;

        return $form;
    }
}
