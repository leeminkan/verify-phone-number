window.onload = function() {
    // set-up an invisible recaptcha. 'sendCode` is button element id
    // <button id="sendCode">Send Code</button>
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('sendCode', {
        'size': 'invisible',
        'callback': function (recapchaToken) {
            // reCAPTCHA solved, send recapchaToken and phone number to backend.
            
            // a REST call to your backend
            axios.post('http://localhost:8000/api/send-code', {
                phoneNumber: document.getElementById('phoneNumber').value,
                recapchaToken,
            })
            .then(function (response) {
                console.log(response);
            })
            .catch(function (error) {
                console.log(error);
            });
        }
    });
    
    // render the rapchaVerifier. 
    window.recaptchaVerifier.render().then(function (widgetId) {
        window.recaptchaWidgetId = widgetId;
    });

    buttonVerify = document.getElementById('vefiryCode');
    buttonVerify.addEventListener('click', function() {
        code = document.getElementById('code').value;
        axios.post('http://localhost:8000/api/verify-code', {
            code: document.getElementById('code').value,
            phoneNumber: document.getElementById('phone2').value
        })
        .then(function (response) {
            console.log(response);
        })
        .catch(function (error) {
            console.log(error);
        });
    });
};