<?php

namespace Aeyoll\PowCaptchaForWordpress;

use Aeyoll\PowCaptchaForWordpress\Core;
use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class Widget
{
    protected $client;

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

    public function load_challenges()
    {
        $response = $this->get_client()->post('GetChallenges?difficultyLevel=5');
        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        return $data;
    }

    public function get_challenge()
    {
        $cache_key = 'pow_captcha_for_wordpress_challenges';
        $cache = new FilesystemAdapter('pow_captcha_for_wordpress', 24 * 3600);

        $challengesCache = $cache->getItem($cache_key);

        if (!$challengesCache->isHit()) {
            $challenges = $this->load_challenges();
        } else {
            $challenges = $challengesCache->get();

            if (count($challenges) < 5) {
                $challenges = $this->load_challenges();
            }
        }

        $challenge = array_shift($challenges);
        $challengesCache->set($challenges);
        $cache->save($challengesCache);

        return $challenge;
    }

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

    public function pow_captcha_generate_widget_tag_from_plugin($plugin)
    {
        if (!$plugin->is_configured()) {
            return ;
        }

        $api_url = $plugin->get_captcha_api_url();
        $challenge = $this->get_challenge();

        return $this->pow_captcha_generate_widget_tag($api_url, $challenge);
    }

    public function pow_captcha_generate_widget_tag($api_url, $challenge)
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
