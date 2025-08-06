// Prevent multiple ajax calls
window.isPowCaptchaLoading = false;

function powCaptchaLoad() {
    window.isPowCaptchaLoading = true;
    const url = powCaptchaAjax.ajax_url + '?action=pow_captcha_get_widget';
    const selector = '.pow-captcha-placeholder';
    let captchaHtml = '';

    window.myCaptchaCallback = (nonce) => {
        Array.from(document.querySelectorAll("input[name='nonce']")).forEach(e => e.value = nonce);
        Array.from(document.querySelectorAll("input[type='submit']")).forEach(e => e.disabled = false);
        Array.from(document.querySelectorAll("button[type='submit']")).forEach(e => e.disabled = false);
    };

    const captchas = Array.from(document.querySelectorAll(selector));

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
    if (typeof window.myCaptchaCallback === 'function') {
        return;
    }

    // Init captcha on document load
    powCaptchaLoad();
});

// On contact form 7 submit success
document.addEventListener('wpcf7submit', function(event) {
    window.sqrCaptchaReset();
    powCaptchaLoad();
});
