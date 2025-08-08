<?php

namespace Aeyoll\PowCaptchaForWordpress;

use Aeyoll\PowCaptchaForWordpress\Core as PowCaptchaForWordpressCore;

class Settings
{
    public function init()
    {
        if (is_admin()) {
            add_action('admin_init', [$this, 'pow_captcha_settings_init']);
        }
    }

    public function pow_captcha_settings_init()
    {
        register_setting(
            PowCaptchaForWordpressCore::$option_group,
            PowCaptchaForWordpressCore::$option_captcha_api_token,
            'sanitize_text_field'
        );
        register_setting(
            PowCaptchaForWordpressCore::$option_group,
            PowCaptchaForWordpressCore::$option_captcha_api_url,
            'esc_url_raw'
        );

        /* General section */

        // Section
        add_settings_section(
            'pow_captcha_general_settings_section',
            'Account Configuration',
            null,
            'pow_captcha_admin'
        );

        add_settings_field(
            'pow_captcha_settings_api_token_field',
            'API Token',
            [$this, 'pow_captcha_settings_field_callback'],
            'pow_captcha_admin',
            'pow_captcha_general_settings_section',
            array(
                'option_name' => PowCaptchaForWordpressCore::$option_captcha_api_token,
                'description' => '',
                'type' => 'text'
            )
        );

        add_settings_field(
            'pow_captcha_settings_api_url_field',
            'API Url',
            [$this, 'pow_captcha_settings_field_callback'],
            'pow_captcha_admin',
            'pow_captcha_general_settings_section',
            array(
                'option_name' => PowCaptchaForWordpressCore::$option_captcha_api_url,
                'description' => '',
                'type' => 'text'
            )
        );

        // Enable on admin login form
        add_settings_field(
            'pow_captcha_settings_enable_on_admin_login_form_field',
            'Enable on admin login form',
            [$this, 'pow_captcha_settings_field_callback'],
            'pow_captcha_admin',
            'pow_captcha_general_settings_section',
            array(
                'option_name' => PowCaptchaForWordpressCore::$option_enable_on_admin_login_form,
                'description' => '',
                'type' => 'checkbox'
            )
        );
    }

    // field content cb
    public function pow_captcha_settings_field_callback(array $args)
    {
        $type = $args['type'];
        $option_name  = $args['option_name'];
        $description = $args['description'];

        // Value of the option
        $setting = get_option($option_name);

        $value = isset($setting) ? esc_attr($setting) : '';
        $checked = "";

        if ($type == "checkbox") {
            $value = 1;
            $checked = checked(1, $setting, false);
        }
        ?>
        <input
            autcomplete="none"
            type="<?php echo esc_attr($type); ?>"
            name="<?php echo esc_attr($option_name); ?>"
            id="<?php echo esc_attr($option_name); ?>"
            value="<?php echo esc_attr($value) ?>" <?php echo esc_attr($checked) ?>>
        <label
            class="description"
            for="<?php echo esc_attr($option_name); ?>">
            <?php echo esc_html($description) ?>
        </label>
        <?php
    }
}
