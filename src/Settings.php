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
        register_setting(
            PowCaptchaForWordpressCore::$option_group,
            PowCaptchaForWordpressCore::$option_enable_on_login_form,
            'sanitize_text_field'
        );
        register_setting(
            PowCaptchaForWordpressCore::$option_group,
            PowCaptchaForWordpressCore::$option_difficulty_level,
            [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => PowCaptchaForWordpressCore::$default_difficulty_level,
            ]
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
            'pow_captcha_settings_enable_on_login_form_field',
            'Enable on admin login form',
            [$this, 'pow_captcha_settings_field_callback'],
            'pow_captcha_admin',
            'pow_captcha_general_settings_section',
            array(
                'option_name' => PowCaptchaForWordpressCore::$option_enable_on_login_form,
                'description' => '',
                'type' => 'checkbox'
            )
        );

        // Difficulty level
        add_settings_field(
            'pow_captcha_settings_difficulty_level_field',
            'Difficulty Level',
            [$this, 'pow_captcha_settings_field_callback'],
            'pow_captcha_admin',
            'pow_captcha_general_settings_section',
            array(
                'option_name' => PowCaptchaForWordpressCore::$option_difficulty_level,
                'description' => 'The difficulty level for the captcha challenge (1-10). Higher values require more computational work.',
                'type' => 'number',
                'min' => 1,
                'max' => 10,
                'default' => PowCaptchaForWordpressCore::$default_difficulty_level,
            )
        );
    }

    /**
     * Renders a settings field input.
     *
     * @param array $args Field configuration arguments.
     */
    public function pow_captcha_settings_field_callback(array $args)
    {
        $type = $args['type'];
        $option_name  = $args['option_name'];
        $description = $args['description'];
        $default = $args['default'] ?? '';

        // Value of the option
        $setting = get_option($option_name, $default);

        $value = isset($setting) ? esc_attr($setting) : '';
        $checked = '';
        $extra_attrs = '';

        if ($type === 'checkbox') {
            $value = 1;
            $checked = checked(1, $setting, false);
        }

        if ($type === 'number') {
            if (isset($args['min'])) {
                $extra_attrs .= ' min="' . esc_attr($args['min']) . '"';
            }

            if (isset($args['max'])) {
                $extra_attrs .= ' max="' . esc_attr($args['max']) . '"';
            }
        }
        ?>
        <input
            autocomplete="none"
            type="<?php echo esc_attr($type); ?>"
            name="<?php echo esc_attr($option_name); ?>"
            id="<?php echo esc_attr($option_name); ?>"
            value="<?php echo esc_attr($value); ?>"
            <?php echo esc_attr($checked); ?>
            <?php echo $extra_attrs; ?>>
        <label
            class="description"
            for="<?php echo esc_attr($option_name); ?>">
            <?php echo esc_html($description); ?>
        </label>
        <?php
    }
}
