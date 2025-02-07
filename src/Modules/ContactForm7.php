<?php

namespace Aeyoll\PowCaptchaForWordpress\Modules;

use Aeyoll\PowCaptchaForWordpress\Core;
use Aeyoll\PowCaptchaForWordpress\Widget;
use WPCF7_Submission;

class ContactForm7
{
    protected $widget;

    public function init()
    {
        $this->widget = new Widget();

        add_filter(
            'wpcf7_form_elements',
            [$this, 'pow_captcha_wpcf7_pow_captcha_add_placeholder_if_missing'],
            100,
            1
        );

        add_action('wp_enqueue_scripts', [$this, 'pow_captcha_wpcf7_pow_captcha_enqueue_scripts'], 10, 0);
        add_filter('wpcf7_spam', [$this, 'pow_captcha_wpcf7_pow_captcha_verify_response'], 9, 1);
        add_action('wpcf7_init', [$this, 'pow_captcha_wpcf7_pow_captcha_add_form_tag_pow_captcha'], 10, 0);
        wpcf7_add_form_tag('pow_captcha', [$this, 'pow_captcha_wpcf7_pow_captcha_widget_shortcode'], ['theme']);
    }
    public function pow_captcha_wpcf7_pow_captcha_add_placeholder_if_missing($elements)
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured() or !$plugin->get_contact_form_7_active()) {
            return $elements;
        }

        // Check if a widget is already present (probably through a shortcode)
        if (preg_match('/<div.*id=".*pow-captcha-placeholder.*".*<\/div>/', $elements)) {
            return $elements;
        }

        $elements .= $this->widget->pow_captcha_placeholder();

        return $elements;
    }

    public function pow_captcha_wpcf7_pow_captcha_add_widget_if_missing($elements)
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured() or !$plugin->get_contact_form_7_active()) {
            return $elements;
        }

        // Check if a widget is already present (probably through a shortcode)
        if (preg_match('/<div.*class=".*captcha-container.*".*<\/div>/', $elements)) {
            return $elements;
        }

        $elements .= $this->widget->pow_captcha_generate_widget_tag_from_plugin($plugin);

        return $elements;
    }

    public function pow_captcha_wpcf7_pow_captcha_enqueue_scripts()
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured() or !$plugin->get_contact_form_7_active()) {
            return;
        }

        // See if wpcf7 is even enabled
        if (!class_exists('WPCF7_Service')) {
            return;
        }

        $this->widget->pow_captcha_enqueue_widget_scripts();

        wp_add_inline_script('pow-captcha', '
            // Reload captchas after form submission
            document.addEventListener("wpcf7submit", function(event) {
                if (!window.isPowCaptchaLoading) {
                    window.sqrCaptchaInitDone = false;
                    powCaptchaLoad();
                }
            });
        ');
    }


    public function pow_captcha_wpcf7_pow_captcha_verify_response($spam)
    {
        if ($spam) {
            return $spam;
        }

        $submission = WPCF7_Submission::get_instance();
        $data = $submission->get_posted_data();
        $valid = $this->widget->validate_captcha($data['challenge'], $data['nonce']);

        if ($valid) {
            $spam = false;
        } else {
            $spam = true;
            $submission->add_spam_log(array(
                'agent' => 'pow-captcha',
                'reason' => __('Captcha verification failed', 'pow-captcha-for-wordpress'),
            ));
        }

        return $spam;
    }

    public function pow_captcha_wpcf7_pow_captcha_widget_shortcode($form_tag)
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured() or !$plugin->get_contact_form_7_active()) {
            return;
        }

        return $this->widget->pow_captcha_generate_widget_tag_from_plugin($plugin);
    }

    public function pow_captcha_wpcf7_pow_captcha_add_form_tag_pow_captcha()
    {
        $plugin = Core::$instance;

        if (!$plugin->is_configured() or !$plugin->get_contact_form_7_active()) {
            return;
        }

        wpcf7_add_form_tag('pow_captcha', [$this, 'pow_captcha_wpcf7_pow_captcha_widget_shortcode'], ['theme']);
    }
}
