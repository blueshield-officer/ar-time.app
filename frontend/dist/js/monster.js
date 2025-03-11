

function checkInput(input){
    return input ? input.value : '';
}




function checkEmail(email) {
    const validDomain = "@ryanhs.org";
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (email === '') {
        return 'Please enter an email.';
    }
    if (!emailRegex.test(email)) {
        return 'Invalid email format.';
    }
    if (!email.endsWith(validDomain)) {
        return 'It must be @ryanhs.org email.';
    }
    return true;
}
function checkPassword(password){
    const email = checkInput(document.getElementById('email_input'));

    if (password === '') {
        return 'Please enter a password.';
    }
    if (password.includes(' ')) {
        return 'Password cannot have space characters.';
    }
    if (password.length < 8){
        return 'Password must be at least 8 characters';
    }
    if (checkEmail(email) && password.includes(email)) {
        return 'Password cannot contain yours email.';
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



document.addEventListener("DOMContentLoaded", (event) => {
    if (document.getElementById('auth-card') !== null ) {
    }
});

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
    console.log(validity)
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




function checkAuthFields() {
    let objects = {
        email: document.getElementById('email_input'),
        password: document.getElementById('password_input'),
        passwordRepeat: document.getElementById('passwordRepeat_input'),
        method: document.getElementById('method_input')
    }
    let values = {};
    Object.entries(objects).forEach(([key, input]) => {
        values[key] = input ? input.value : '';
    });

    const emailValidity = checkEmail(values.email)
    if (emailValidity !== true) {
        addError(
            objects.email,
            document.getElementById('email_box'),
            document.getElementById('invalid-email-comment'),
            emailValidity
        );
        wasWrongEmail = true;
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
    if (values.method === 'signup') {
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
    else {
        allTrue = [emailValidity, passwordValidity, passwordRepeatValidity].every(element => element === true);
    }

    if (allTrue === true) {
        document.getElementById('auth-form').submit();
    }

}

// TODO зависимость между паролем и паролем повторения
/*
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("emailInput").addEventListener("change", function () {})
})
 */

function timerToPercentage() {
    document.getElementById("countdown-time").innerText = '20%'
}
function timerOpenWindow(){
    document.getElementById("countdown-mask").style.display = 'block'
}
document.addEventListener("DOMContentLoaded", function () {
    const fireContainers = document.getElementsByClassName('fire');
    if (!fireContainers) return; // Ensure the element exists
    Array.from(fireContainers).forEach((fireContainer) => {
        const parts = 50;

        for (let i = 0; i < parts; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            fireContainer.appendChild(particle);
        }
    })
});
addEventListener("DOMContentLoaded", (event) => {
    const account_box_element = document.getElementById('account-box')
    if (account_box_element !== null) {

    }
});

function toggleThemes(){
    let theme = document.getElementById('theme');
    const currentTheme = theme.getAttribute('href')
    if (currentTheme.includes('light')) {
        theme.setAttribute('href', currentTheme.replace('light', 'dark'));
    }
    else {
        theme.setAttribute('href', currentTheme.replace('dark', 'light'));
    }
}

async function toggleSideBar(status) {
    const transition = {
        time: '0.3s',
        milliseconds: 300
    }

    async function removeTimeOut(element, timeout) {
        setTimeout(() => {
            element.style.transitionDuration = 'none'
        }, timeout)
    }

    function transitionTo(element, duration) {
        console.log(duration)
        element.style.transitionDuration = duration
    }

    const sideBarObject = document.getElementById('side-bar');
    const collapse = document.getElementById('collapse');
    const collapseIcon = collapse.getElementsByClassName('icon')[0]
    const collapseP = collapse.getElementsByTagName('p')[0]

    const websiteLogoSection = document.getElementById('website-logo-section');
    const websiteLogo = document.getElementById('website-logo')
    const websiteLogoDivider = document.getElementById('website-logo-divider')
    const websiteTitle = document.getElementById('website-title')

    const screen = document.getElementById('screen');

    transitionTo(sideBarObject, transition.time)
    transitionTo(collapse, transition.time)
    transitionTo(collapseIcon, transition.time)
    transitionTo(collapseP, transition.time)

    transitionTo(websiteLogoSection, transition.time)
    transitionTo(websiteLogo, transition.time)
    transitionTo(websiteLogoDivider, transition.time)
    transitionTo(websiteTitle, transition.time)

    transitionTo(screen, transition.time)

    const pagesTitlesObjects = sideBarObject.getElementsByClassName('pageTitle')


    if (status === 0) {
        // left-bar
        sideBarObject.style.width = '210px';
        collapseP.style.marginLeft = '10px'
        collapseIcon.style.rotate = '0deg';
        for (const element of Array.from(pagesTitlesObjects)) {
            element.style.transition = transition.time
            element.style.marginLeft = '10px';
            await removeTimeOut(element, transition.milliseconds);
        }
        collapse.onclick = () => {
            toggleSideBar(1)
        }

        // logo
        websiteLogoSection.style.width = '210px';
        websiteTitle.style.marginLeft = '0px'
        websiteLogoDivider.style.opacity = '1'
        websiteLogoDivider.style.height = '60px'
        websiteLogo.style.borderRadius = '10px 0 0 10px';
        websiteTitle.style.borderRadius = '0px 10px 10px 0px'

        //screen
        screen.style.left = '210px'
        screen.style.width = 'calc(100% - 210px)'

    } else {
        // left-bar
        sideBarObject.style.width = '75px';
        collapseP.style.marginLeft = '40px'
        collapseIcon.style.rotate = '180deg';
        for (const element of Array.from(pagesTitlesObjects)) {
            element.style.transition = transition.time
            element.style.marginLeft = '40px';
            await removeTimeOut(element, transition.milliseconds);
        }
        collapse.onclick = () => {
            toggleSideBar(0)
        }

        // logo
        websiteLogoSection.style.width = '75px';
        websiteTitle.style.marginLeft = '40px'
        websiteLogoDivider.style.opacity = '0'
        websiteLogoDivider.style.height = '0px'
        // websiteTitle.style.textWrap = 'nowrap';
        websiteLogo.style.borderRadius = '10px';
        websiteTitle.style.borderRadius = '10px'

        // screen
        screen.style.left = '75px'
        screen.style.width = 'calc(100% - 75px)'
    }

    // await removeTimeOut(sideBarObject, transition.milliseconds);
    // await removeTimeOut(collapseIcon, transition.milliseconds);
    // await removeTimeOut(collapseP, transition.milliseconds);
    // await removeTimeOut(collapse, transition.milliseconds);
    // await removeTimeOut(screen, transition.milliseconds);
}