// Prevent multiple ajax calls
window.isPowCaptchaLoading = false;

const powCaptchaSelector = '.pow-captcha-placeholder';

function powCaptchaDisableFormSubmits() {
    const captchaForms = Array.from(document.querySelectorAll(powCaptchaSelector))
        .map(placeholder => placeholder.closest('form'))
        .filter(form => form !== null);

    captchaForms.forEach(form => {
        const submitButtons = form.querySelectorAll('input[type="submit"], button[type="submit"]');
        submitButtons.forEach(button => {
            button.disabled = true;
        });
    });
}

function powCaptchaBindFormSubmitHandlers() {
    const captchaForms = Array.from(document.querySelectorAll(powCaptchaSelector))
        .map(placeholder => placeholder.closest('form'))
        .filter(form => form !== null);

    captchaForms.forEach(form => {
        const submitButtons = form.querySelectorAll('input[type="submit"], button[type="submit"]');
        submitButtons.forEach(button => {
            button.disabled = true;
        });

        form.addEventListener('submit', function() {
            submitButtons.forEach(button => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = button.name;
                hiddenInput.value = button.value;
                form.appendChild(hiddenInput);
                button.disabled = true;
            });
        });
    });
}

function powCaptchaLoad() {
    window.isPowCaptchaLoading = true;
    const url = powCaptchaAjax.ajax_url + '?action=pow_captcha_get_widget';
    let captchaHtml = '';

    window.myCaptchaCallback = (nonce) => {
        Array.from(document.querySelectorAll("input[name='nonce']")).forEach(e => e.value = nonce);
        Array.from(document.querySelectorAll("input[type='submit']")).forEach(e => e.disabled = false);
        Array.from(document.querySelectorAll("button[type='submit']")).forEach(e => e.disabled = false);
    };

    const captchas = Array.from(document.querySelectorAll(powCaptchaSelector));

    // If there's no captcha on the page, abort
    if (captchas.length <= 0) {
        return;
    }

    fetch(url)
        .then(response => response.text())
        .then(html => {
            captchaHtml = html;

            // Assign captcha content to each captcha on the page
            captchas.forEach((captcha) => {
                captcha.innerHTML = html;
            });

            // Init the captcha
            window.sqrCaptchaInit();

            // Reset loader
            window.isPowCaptchaLoading = false;
        })
    .catch(error => {
        console.error('Error:', error);

        // Reset loader
        window.isPowCaptchaLoading = false;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const captchaForms = Array.from(document.querySelectorAll(powCaptchaSelector))
        .map(placeholder => placeholder.closest('form'))
        .filter(form => form !== null);

    if (captchaForms.length > 0) {
        powCaptchaBindFormSubmitHandlers();
    }

    if (typeof window.myCaptchaCallback === 'function') {
        return;
    }

    powCaptchaLoad();
});

// On contact form 7 submit success
document.addEventListener('wpcf7submit', function(event) {
    window.sqrCaptchaReset();
    powCaptchaDisableFormSubmits();
    powCaptchaLoad();
});
