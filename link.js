document.addEventListener('DOMContentLoaded', function () {
    const checkBtn = document.querySelector('.check-btn');
    const startBtn = document.querySelector('.start-btn');
    const callbackBtn = document.querySelector('.callback-btn');

    // 🔗 Add your WhatsApp links here:
    const checkNowLink = '/html/visa-applicant.html';     // For CHECK NOW
    const applyNowLink = '/html/visa-applicant.html';     // For APPLY NOW — Replace with actual number
    const callbackLink = 'https://wa.me/919029027420';     // For CALLBACK

    if (checkBtn) {
        checkBtn.addEventListener('click', function () {
            window.open(checkNowLink, '_blank');
        });
    }

    if (startBtn) {
        startBtn.addEventListener('click', function () {
            window.open(applyNowLink, '_blank');
        });
    }

    if (callbackBtn) {
        callbackBtn.addEventListener('click', function () {
            window.open(callbackLink, '_blank');
        });
    }
});