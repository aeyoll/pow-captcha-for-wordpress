<?php

namespace Aeyoll\PowCaptchaForWordpress;

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\FileCache;
use WP_CLI;

/**
 * POW Captcha WP-CLI Commands
 *
 * Provides WP-CLI commands for managing POW Captcha plugin settings and cache.
 */
class CliCommands
{
    /**
     * Sets the API key for POW Captcha.
     *
     * ## OPTIONS
     *
     * <api_key>
     * : The new API key to set
     *
     * ## EXAMPLES
     *
     *     wp pow-captcha set-api-key "your-new-api-key-here"
     *
     * @param array $args The command arguments
     * @param array $assoc_args The associative arguments
     */
    public function set_api_key($args, $assoc_args)
    {
        if (empty($args[0])) {
            WP_CLI::error('API key is required.');
            return;
        }

        $api_key = sanitize_text_field($args[0]);

        if (empty($api_key)) {
            WP_CLI::error('Invalid API key provided.');
            return;
        }

        $option_name = Core::$option_captcha_api_token;
        $updated = update_option($option_name, $api_key);

        if ($updated) {
            WP_CLI::success("API key has been updated successfully.");
        } else {
            WP_CLI::warning("API key was not changed (same value or update failed).");
        }
    }

    /**
     * Sets the API URL for POW Captcha.
     *
     * ## OPTIONS
     *
     * <api_url>
     * : The new API URL to set
     *
     * ## EXAMPLES
     *
     *     wp pow-captcha set-api-url "https://your-captcha-api.example.com"
     *
     * @param array $args The command arguments
     * @param array $assoc_args The associative arguments
     */
    public function set_api_url($args, $assoc_args)
    {
        if (empty($args[0])) {
            WP_CLI::error('API URL is required.');
            return;
        }

        $api_url = esc_url_raw($args[0]);

        if (empty($api_url) || !filter_var($api_url, FILTER_VALIDATE_URL)) {
            WP_CLI::error('Invalid API URL provided.');
            return;
        }

        $option_name = Core::$option_captcha_api_url;
        $updated = update_option($option_name, $api_url);

        if ($updated) {
            WP_CLI::success("API URL has been updated successfully.");
        } else {
            WP_CLI::warning("API URL was not changed (same value or update failed).");
        }
    }

    /**
     * Clears the POW Captcha token cache.
     *
     * ## EXAMPLES
     *
     *     wp pow-captcha clear-cache
     *
     * @param array $args The command arguments
     * @param array $assoc_args The associative arguments
     */
    public function clear_cache($args, $assoc_args)
    {
        $cache_key = 'pow_captcha_for_wordpress_challenges';
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pow_captcha_for_wordpress-' . md5(ABSPATH);

        $cache = new FileCache(['cache_dir' => $directory]);

        // Delete the specific cache entry
        $deleted = $cache->delete($cache_key);

        if ($deleted) {
            WP_CLI::success("Cache cleared successfully.");
        } else {
            WP_CLI::warning("No cache files found to clear.");
        }
    }

    /**
     * Shows the current POW Captcha configuration.
     *
     * ## EXAMPLES
     *
     *     wp pow-captcha status
     *
     * @param array $args The command arguments
     * @param array $assoc_args The associative arguments
     */
    public function status($args, $assoc_args)
    {
        $plugin = Core::$instance;

        if (!$plugin) {
            WP_CLI::error('POW Captcha plugin is not initialized.');
            return;
        }

        $api_key = $plugin->get_captcha_api_token();
        $api_url = $plugin->get_captcha_api_url();
        $is_configured = $plugin->is_configured();

        WP_CLI::log('POW Captcha Status:');
        WP_CLI::log('------------------');
        WP_CLI::log(sprintf('API Key: %s', $api_key ? '***' . substr($api_key, -4) : 'Not set'));
        WP_CLI::log(sprintf('API URL: %s', $api_url ?: 'Not set'));
        WP_CLI::log(sprintf('Configured: %s', $is_configured ? 'Yes' : 'No'));

        // Check cache directory
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pow_captcha_for_wordpress-' . md5(__DIR__);
        $cache_exists = is_dir($directory);
        WP_CLI::log(sprintf('Cache Directory: %s', $cache_exists ? 'Exists' : 'Not found'));

        if ($cache_exists) {
            $cache_files = glob($directory . '/*/*/*.cache');
            WP_CLI::log(sprintf('Cache Files: %d', count($cache_files ?: [])));
        }
    }
}
