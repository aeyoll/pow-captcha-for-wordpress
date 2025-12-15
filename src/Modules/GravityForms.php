<?php

namespace Aeyoll\PowCaptchaForWordpress\Modules;

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Widget;
use GFCommon;
use GFFormsModel;
use GGFormDisplay;

class GravityForms
{
    protected $widget;

    public function init()
    {
        $this->widget = new Widget();

        // Inject captcha into all forms
        add_filter('gform_form_tag', [$this, 'inject_pow_captcha'], 10, 2);

        // Enqueue necessary scripts
        add_action('gform_enqueue_scripts', [$this, 'pow_captcha_enqueue_scripts'], 10, 2);

        // Validate all form submissions
        add_filter('gform_validation', [$this, 'validate_form'], 10, 2);

        // Add form settings (gform_form_settings was deprecated in v2.5 and removed in v3.0)
        if ($this->is_gravity_forms_version_at_least('2.5')) {
            add_filter('gform_form_settings_fields', [$this, 'add_form_settings_fields'], 10, 2);
        } else {
            add_filter('gform_form_settings', [$this, 'add_form_settings_legacy'], 10, 2);
        }

        add_filter('gform_pre_form_settings_save', [$this, 'save_form_settings']);
    }

    protected function is_gravity_forms_version_at_least(string $version): bool
    {
        if (! class_exists('GFCommon')) {
            return false;
        }

        return version_compare(\GFCommon::$version, $version, '>=');
    }

    public function pow_captcha_enqueue_scripts($form, $ajax)
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured()) {
            return;
        }

        if ($this->is_form_excluded($form)) {
            return;
        }

        $this->widget->pow_captcha_enqueue_widget_assets();

        wp_add_inline_script('pow-captcha', '
            jQuery(document).on("gform_post_render", function() {
                if (!window.isPowCaptchaLoading) {
                    window.sqrCaptchaInitDone = false;
                    powCaptchaLoad();
                }
            });

            jQuery(document).on("gform_page_loaded", function() {
                if (!window.isPowCaptchaLoading) {
                    window.sqrCaptchaInitDone = false;
                    powCaptchaLoad();
                }
            });
        ');
    }

    public function inject_pow_captcha($form_tag, $form)
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured()) {
            return $form_tag;
        }

        if ($this->is_form_excluded($form)) {
            return $form_tag;
        }

        $captcha_html = sprintf(
            '<div class="ginput_container ginput_container_captcha">%s</div>',
            $this->widget->pow_captcha_placeholder()
        );

        return $form_tag . $captcha_html;
    }

    public function validate_form($validation_result)
    {
        $form = $validation_result['form'];

        if (!GGFormDisplay::is_last_page($form)) {
            return $validation_result;
        }

        if ($this->is_form_excluded($form)) {
            return $validation_result;
        }

        $challenge = rgpost('challenge');
        $nonce = rgpost('nonce');

        if (empty($challenge) || empty($nonce)) {
            $validation_result['is_valid'] = false;
            $form['validation_message'] = __('Please complete the captcha', 'pow-captcha');
        } else {
            $valid = $this->widget->validate_captcha($challenge, $nonce);

            if (!$valid) {
                $validation_result['is_valid'] = false;
                $form['validation_message'] = __('Captcha verification failed', 'pow-captcha');
            }
        }

        $validation_result['form'] = $form;

        return $validation_result;
    }

    protected function is_form_excluded($form)
    {
        $form_id = isset($form['id']) ? (int) $form['id'] : 0;

        $disable_captcha = isset($form['pow_captcha_disable']) ? (bool) $form['pow_captcha_disable'] : false;

        if ($disable_captcha) {
            return true;
        }

        $is_excluded = apply_filters('pow_captcha_exclude_gravity_form', false, $form_id, $form);

        return $is_excluded;
    }

    /**
     * Add form settings fields for Gravity Forms 2.5+.
     *
     * @param array $fields The form settings fields.
     * @param array $form   The form object.
     *
     * @return array
     */
    public function add_form_settings_fields(array $fields, array $form): array
    {
        $fields['pow_captcha_settings'] = [
            'title' => esc_html__('POW Captcha', 'pow-captcha'),
            'fields' => [
                [
                    'name' => 'pow_captcha_disable',
                    'label' => esc_html__('Disable POW Captcha', 'pow-captcha'),
                    'type' => 'toggle',
                    'tooltip' => esc_html__('Disable POW Captcha for this form', 'pow-captcha'),
                    'default_value' => false,
                ],
            ],
        ];

        return $fields;
    }

    /**
     * Add form settings for Gravity Forms < 2.5 (legacy).
     *
     * @param array $settings The form settings.
     * @param array $form     The form object.
     *
     * @return array
     */
    public function add_form_settings_legacy(array $settings, array $form): array
    {
        $checked = isset($form['pow_captcha_disable']) && $form['pow_captcha_disable'] ? 'checked="checked"' : '';

        $settings['Form Options']['pow_captcha_disable'] = '
            <tr>
                <th><label for="pow_captcha_disable">' . esc_html__('POW Captcha', 'pow-captcha') . '</label></th>
                <td>
                    <input type="checkbox"
                           id="pow_captcha_disable"
                           name="pow_captcha_disable"
                           value="1"
                           ' . $checked . ' />
                    <label for="pow_captcha_disable">' . esc_html__('Disable POW Captcha for this form', 'pow-captcha') . '</label>
                </td>
            </tr>';

        return $settings;
    }

    public function save_form_settings($form)
    {
        $form['pow_captcha_disable'] = ! empty($_POST['pow_captcha_disable']);

        return $form;
    }
}
