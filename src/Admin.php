<?php

namespace Aeyoll\PowCaptchaForWordpress;

use Aeyoll\PowCaptchaForWordpress\Core as PowCaptchaForWordpressCore;

class Admin
{
    public function init()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'pow_captcha_options_page']);
            add_filter(
                'plugin_action_links_pow-captcha-for-wordpress/pow-captcha-for-wordpress.php',
                [$this, 'pow_captcha_settings_link']
            );
            add_action('admin_notices', [$this, 'pow_captcha_admin_notice__not_configured']);
        }
    }

    // Add link to settings page in the navbar
    public function pow_captcha_options_page()
    {
        add_options_page(
            'POW Captcha',
            'POW Captcha',
            'manage_options',
            'pow_captcha_admin',
            [$this, 'pow_captcha_options_page_html'],
            30
        );
    }

    public function pow_captcha_settings_link($links)
    {
        $url = esc_url(add_query_arg(
            'page',
            'pow_captcha_admin',
            get_admin_url() . 'options-general.php'
        ));

        $settings_link = "<a href='$url'>" . __('Settings') . '</a>';

        array_push(
            $links,
            $settings_link
        );

        return $links;
    }

    public function pow_captcha_admin_notice__not_configured()
    {
        if (!PowCaptchaForWordpressCore::$instance->is_configured()) {
            $url = esc_url(add_query_arg(
                'page',
                'pow_captcha_admin',
                get_admin_url() . 'options-general.php'
            ));
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong>POW Captcha is not configured yet!</strong>
                    Visit the <a href="<?php echo $url ?>">POW Captcha settings</a>
                    and enter a valid API key and API url to complete the setup.
                </p>
            </div>
            <?php
        }
    }

    public function pow_captcha_options_page_html()
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <hr>
            <form action="options.php" method="post">
                <?php
                settings_errors();
                settings_fields(PowCaptchaForWordpressCore::$option_group);
                do_settings_sections('pow_captcha_admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
