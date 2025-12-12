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

        // Inject captcha into all forms
        add_filter('gform_form_tag', [$this, 'inject_pow_captcha'], 10, 2);

        // Enqueue necessary scripts
        add_action('gform_enqueue_scripts', [$this, 'pow_captcha_enqueue_scripts'], 10, 2);

        // Validate all form submissions
        add_filter('gform_validation', [$this, 'validate_form'], 10, 2);
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

        $is_excluded = apply_filters('pow_captcha_exclude_gravity_form', false, $form_id, $form);

        return $is_excluded;
    }
}
