<?php

namespace Aeyoll\PowCaptchaForWordpress\Modules;

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Widget;
use GFFormsModel;

class GravityForms
{
    protected $widget;

    public function init()
    {
        $this->widget = new Widget();

        // Add POW Captcha field to Gravity Forms
        add_action('gform_editor_js', [$this, 'pow_captcha_editor_js']);
        add_filter('gform_add_field_buttons', [$this, 'pow_captcha_add_field_buttons']);
        add_filter('gform_field_type_title', [$this, 'pow_captcha_field_title']);

        // Enqueue necessary scripts
        add_action('gform_enqueue_scripts', [$this, 'pow_captcha_enqueue_scripts'], 10, 2);

        // Render the field
        add_action('gform_field_input', [$this, 'pow_captcha_field_input'], 10, 5);

        // Validate the submission
        add_filter('gform_field_validation', [$this, 'pow_captcha_validate'], 10, 4);
    }

    public function pow_captcha_editor_js()
    {
        ?>
        <script type='text/javascript'>
            fieldSettings.pow_captcha = '.label_setting, .description_setting, .admin_label_setting, .visibility_setting';

            jQuery(document).bind('gform_load_field_settings', function(event, field, form) {
                if (field.type == 'pow_captcha') {
                    // Any specific field settings can be loaded here
                }
            });
        </script>
        <?php
    }

    public function pow_captcha_add_field_buttons($field_groups)
    {
        $field_groups[] = array(
            'name'   => 'pow_captcha_fields',
            'label'  => __('POW Captcha Fields', 'pow-captcha-for-wordpress'),
            'fields' => array(
                array(
                    'class'     => 'button',
                    'data-type' => 'pow_captcha',
                    'value'     => __('POW Captcha', 'pow-captcha-for-wordpress'),
                    'onclick'   => "StartAddField('pow_captcha');"
                ),
            )
        );
        return $field_groups;
    }

    public function pow_captcha_field_title($title)
    {
        if ($title == 'pow_captcha') {
            return __('POW Captcha', 'pow-captcha-for-wordpress');
        }
        return $title;
    }

    public function pow_captcha_enqueue_scripts($form, $ajax)
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured()) {
            return;
        }

        $this->widget->pow_captcha_enqueue_widget_scripts();

        // Add Gravity Forms specific script
        wp_add_inline_script('pow-captcha', '
            // Reload captchas after form submission
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

    public function pow_captcha_field_input($input, $field, $value, $entry_id, $form_id)
    {
        if ($field->type !== 'pow_captcha') {
            return $input;
        }

        $plugin = Core::$instance;

        if (!$plugin->is_configured()) {
            return __('POW Captcha is not configured', 'pow-captcha-for-wordpress');
        }

        return sprintf(
            '<div class="ginput_container ginput_container_captcha">%s</div>',
            $this->widget->pow_captcha_placeholder()
        );
    }

    public function pow_captcha_validate($result, $value, $form, $field)
    {
        if ($field->type !== 'pow_captcha') {
            return $result;
        }

        $challenge = rgpost('challenge');
        $nonce = rgpost('nonce');

        if (empty($challenge) || empty($nonce)) {
            $result['is_valid'] = false;
            $result['message'] = __('Please complete the captcha', 'pow-captcha-for-wordpress');
            return $result;
        }

        $valid = $this->widget->validate_captcha($challenge, $nonce);

        if (!$valid) {
            $result['is_valid'] = false;
            $result['message'] = __('Captcha verification failed', 'pow-captcha-for-wordpress');
        }

        return $result;
    }
}
