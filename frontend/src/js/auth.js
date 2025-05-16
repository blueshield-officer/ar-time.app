document.addEventListener("DOMContentLoaded", (event) => {
    if (document.getElementById('auth-form') !== null) {

    }
    else if (document.getElementById('auth-form-code')) {
        const codeInputFields = document.querySelectorAll('.verificationCodeElement')
        codeInputFields.forEach(element => {
            element.addEventListener('input', () => {
                const value = element.value
                const int = parseInt(value)
                console.log(int)
                if (isNaN(int)){
                    element.value = ''
                }
                if (value.length > 1){
                    element.value = ''
                }
            })
        })
        for (let i = 0; i < codeInputFields.length; i++) {
            codeInputFields[i].addEventListener('keydown', (event) => {
                if (event.key === 'Backspace') {
                    if (!codeInputFields[i].value) {
                        if (codeInputFields[i - 1]){
                            codeInputFields[i].blur();
                            codeInputFields[i - 1].focus();
                            codeInputFields[i - 1].value = '';
                        }
                    }
                }
            });

            codeInputFields[i].addEventListener('input', () => {
                if (codeInputFields[i].value) {
                    codeInputFields[i].blur();
                    if (codeInputFields[i + 1]){
                        codeInputFields[i + 1].focus();
                    }
                }
            });
        }

        const form = document.getElementById('auth-form-code');
        const fields = form.querySelectorAll('.verificationCodeElement');
        const submitBtn = document.getElementById('verify-code-btn');

        function checkFields() {
            let allFilled = true;
            fields.forEach(field => {
                if (field.value.trim() === '') {
                    allFilled = false;
                }
            });
            allFilled ? submitBtn.classList.remove('btn-turned-off') : submitBtn.classList.add('btn-turned-off');
            submitBtn.disabled = !allFilled;
        }

        fields.forEach(field => {
            field.addEventListener('input', checkFields);
        });
    }
})


function checkInput(input){
    return input ? input.value : '';
}




function checkEmail(email) {
    const validDomains = ['@ryanhs.org', '@arhs.org'];
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (email === '') {
        return 'Please enter an email.';
    }
    if (!emailRegex.test(email)) {
        return 'Invalid email format.';
    }
    if (!validDomains.some(domain => email.endsWith(domain))) {
        return 'Email must be @ryanhs.org or @arhs.org type';
    }
    return true;
}
function checkPassword(password){
    let email = null
    if (document.getElementById('email_input')){
        email = checkInput(document.getElementById('email_input'));
    }

    if (password === '') {
        return 'Please enter a password.';
    }
    if (password.includes(' ')) {
        return 'Password cannot have space characters.';
    }
    if (password.length < 8){
        return 'Password must be at least 8 characters';
    }
    if (email !== null){
        if (checkEmail(email) && password.includes(email)) {
            return 'Password cannot contain your email.';
        }
    }
    else if (document.getElementById('reset-password-email-target')){
        if (password.includes(document.getElementById('reset-password-email-target').innerText)) {
            return 'Password cannot contain your email.';
        }
    }
    return true;
}
function checkPasswordRepeat(password, password2){
    if (password !== password2) {
        return 'Password does not match.';
    }
    if (password2 === '') {
        return 'Please repeat your password.';
    }
    if (password !== password2) {
        return 'Password does not match.';
    }
    return true;
}



function clearError(object, box, comment) {
    comment.innerText = '';
    object.classList.remove('is-invalid');
    box.classList.remove('is-invalid');
}
function addError(object, box, comment, text) {
    comment.innerText = text;
    object.classList.add('is-invalid');
    box.classList.add('is-invalid');
}





function validateEmail() {
    const object = document.getElementById('email_input')
    const emailInput = checkInput(object);
    const box = document.getElementById('email_box');
    const comment = document.getElementById('invalid-email-comment');

    const validity = checkEmail(emailInput);
    if (validity === true) {
        if (wasWrongEmail !== 'static'){
            clearError(object, box, comment);
        }
    }
    else {
        if (wasWrongEmail === true) {
            addError(object, box, comment, validity);
        }
    }

}
function validatePassword() {
    const object = document.getElementById('password_input')
    const passwordInput = checkInput(object);
    const box = document.getElementById('password_box');
    const comment = document.getElementById('invalid-password-comment');

    const validity = checkPassword(passwordInput);
    if (validity === true) {
        if (wasWrongPassword !== 'static'){
            clearError(object, box, comment);
        }
    }
    else {
        if (wasWrongPassword === true) {
            addError(object, box, comment, validity);
        }
    }
    if (document.getElementById('method_input').value === 'signup') {
        validatePasswordRepeat()
    }
}
function validatePasswordRepeat() {
    const object = document.getElementById('passwordRepeat_input')
    const passwordInput = checkInput(object);
    const box = document.getElementById('passwordRepeat_box');
    const comment = document.getElementById('invalid-passwordRepeat-comment');

    const validity = checkPasswordRepeat(checkInput(document.getElementById('password_input')), passwordInput);
    if (validity === true) {
        if (wasWrongPasswordRepeat !== 'static'){
            clearError(object, box, comment);
        }
    }
    else {
        if (wasWrongPasswordRepeat === true) {
            addError(object, box, comment, validity);
        }
    }
}



function checkEmailField(emailObject, emailValue){
    const emailValidity = checkEmail(emailValue)
    if (emailValidity !== true) {
        addError(
            emailObject,
            document.getElementById('email_box'),
            document.getElementById('invalid-email-comment'),
            emailValidity
        );
    }
    return emailValidity
}

function checkAuthFields() {
    let objects = {
        email: document.getElementById('email_input'),
        password: document.getElementById('password_input'),
        passwordRepeat: document.getElementById('passwordRepeat_input'),
        method: document.getElementById('method_input')
    }
    let values = {};
    Object.entries(objects).forEach(([key, input]) => {
        values[key] = input !== undefined ? (input ? input.value : '') : null;
    });

    let emailValidity
    if (values.method !== 'password-change'){
        emailValidity = checkEmailField(objects.email, values.email)
        if (emailValidity !== true) {
            wasWrongEmail = true;
        }
    }

    const passwordValidity = checkPassword(values.password)
    if (passwordValidity !== true) {
        addError(
            objects.password,
            document.getElementById('password_box'),
            document.getElementById('invalid-password-comment'),
            passwordValidity
        );
        wasWrongPassword = true;
    }


    let passwordRepeatValidity
    if (values.method === 'signup' || values.method === 'password-change') {
        passwordRepeatValidity = checkPasswordRepeat(values.password, values.passwordRepeat);
        if (passwordRepeatValidity !== true) {
            addError(
                objects.passwordRepeat,
                document.getElementById('passwordRepeat_box'),
                document.getElementById('invalid-passwordRepeat-comment'),
                passwordRepeatValidity
            );
            wasWrongPasswordRepeat = true;
        }
    }


    let allTrue
    if (values.method === 'login') {
         allTrue = [emailValidity, passwordValidity].every(element => element === true);
    }
    else if (values.method === 'password-change') {
        allTrue = [passwordValidity, passwordRepeatValidity].every(element => element === true);
    }
    else {
        allTrue = [emailValidity, passwordValidity, passwordRepeatValidity].every(element => element === true);
    }

    if (allTrue === true) {
        let form
        if (values.method === 'password-change') {
            form = document.getElementById('password-change-form');
        }
        else {
            form = document.getElementById('auth-form')
        }
        form.submit();
    }

}

async function sendChangePasswordRequest() {
    const emailInputObject = document.getElementById('email_input')
    if (checkEmailField(emailInputObject, emailInputObject.value) === true){
        const response = await fetchServer('requestChangePassword', document.getElementById('email_input').value, 'request');
        console.log(response)

        const requestResponseField = document.getElementById('forgot-password-response')
        requestResponseField.classList.remove('success', 'error');
        if (response.success === true){
            requestResponseField.classList.add('success');
        }
        else {
            requestResponseField.classList.add('error');
        }
        requestResponseField.innerText = response.message;
    }
}

// TODO зависимость между паролем и паролем повторения