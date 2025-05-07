import { auth, RecaptchaVerifier, signInWithPhoneNumber } from './firebase-config.js';

window.setupRecaptcha = () => {
    window.recaptchaVerifier = new RecaptchaVerifier('recaptcha-container', {
        'size': 'invisible',
        'callback': (response) => {
            console.log('Recaptcha resolved');
        }
    }, auth);
    recaptchaVerifier.render();
};

window.sendVerificationCode = (phoneNumber) => {
    setupRecaptcha();
    const appVerifier = window.recaptchaVerifier;

    signInWithPhoneNumber(auth, phoneNumber, appVerifier)
        .then((confirmationResult) => {
            window.confirmationResult = confirmationResult;
            document.getElementById('otpModal').style.display = 'block';
        })
        .catch((error) => {
            console.error(error);
            alert('Failed to send verification code');
        });
};

window.verifyCode = () => {
    const digits = document.querySelectorAll('.otp-digit');
    const code = Array.from(digits).map(input => input.value).join('');

    confirmationResult.confirm(code)
        .then((result) => {
            const user = result.user;
            console.log("OTP verified, user:", user);
            alert("Phone number verified!");
            document.getElementById('otpModal').style.display = 'none';
        })
        .catch((error) => {
            console.error("Invalid code", error);
            alert("Invalid verification code.");
        });
};

document.getElementById('verifyOtpBtn').addEventListener('click', verifyCode);