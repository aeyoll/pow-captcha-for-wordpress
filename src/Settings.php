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
            PowCaptchaForWordpressCore::$option_captcha_api_token
        );
        register_setting(
            PowCaptchaForWordpressCore::$option_group,
            PowCaptchaForWordpressCore::$option_captcha_api_url
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
        <input autcomplete="none" type="<?php echo $type; ?>" name="<?php echo $option_name; ?>" id="<?php echo $option_name; ?>" value="<?php echo $value ?>" <?php echo $checked ?>>
        <label class="description" for="<?php echo $option_name; ?>"><?php echo $description ?></label>
        <?php
    }
}
