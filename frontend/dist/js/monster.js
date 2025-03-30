

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
function findPeriodDimention(bells, position, dayStartUnix) {
    let top = null
    let height = null
    const pixelsPerHour = document.getElementsByClassName('time-line')[0].offsetHeight + 20
    const topError = document.getElementsByClassName('time-line')[0].offsetHeight / 2



    const periodStartUnix = bells[position]['start_unix']
    const periodEndUnix = bells[position]['end_unix']


    if (periodStartUnix !== null) {
        top = (pixelsPerHour * ((periodStartUnix - dayStartUnix) / 60 / 60)) + topError + 'px'
    }
    if (periodStartUnix !== null && periodEndUnix !== null) {
        height = pixelsPerHour * ((periodEndUnix - periodStartUnix) / 60 / 60) + 'px'
    }

    return {
        top: top,
        height: height,
    }
}

addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('bells-block')){
        const startUnix = startTime * 60 * 60

        let test = 1
        setInterval(() => {
            document.getElementById('current-time-runner').style.top = test + 'px'
            test ++
        }, 100)

        for (let i = 1; i <=  Object.keys(bells).length; i++) {
            const objectData = findPeriodDimention(bells, i, startUnix)
            document.getElementById('period-' + i).style.top = objectData.top
            document.getElementById('period-' + i).style.height = objectData.height
        }
    }
});
function getCookieValue(name) {
    const cookieValue = document.cookie
        .split("; ")
        .find((row) => row.startsWith(name + '='));

    // Ensure cookieValue exists before accessing it
    if (cookieValue) {
        const value = cookieValue.split("=")[1];
        if (!value) {
            return null;
        }
        return value;
    } else {
        return null;
    }
}
class countdownAnalyzer {
    constructor(table) {
        this.pageName = document.title;
        this.table = table
        this.seconds = -1
        this.periodPosition = 1
        this.periodStatus = 0
        this.updateInterval = 1000
    }
    secondsToPercentage(floor = false) {
        if (this.periodStatus === 0) {
            return 100
        }
        let percentage
        const totalSeconds =  this.table[this.periodPosition]['end_unix'] - this.table[this.periodPosition]['start_unix']
        percentage = 100 - ((100 / totalSeconds) * this.seconds)
        if (floor === true) {
            percentage = Math.floor(percentage)
        }
        return percentage
    }
    percentageToColor(){
        let degree
        if (this.periodStatus === 0){
            degree = 50;
        }
        else {
            degree = (120 / 100) * this.secondsToPercentage()
        }
        return `hsl(${degree} 95% 50%)`;
    }
    secondsToTime() {
        const minutes = Math.floor(this.seconds / 60);
        const secs = this.seconds % 60;

        // Format to always have two digits
        const formattedMinutes = minutes.toString().padStart(2, '0');
        const formattedSecs = secs.toString().padStart(2, '0');

        return `${formattedMinutes} : ${formattedSecs}`;
    }
    getCount() {
        const currentUnixTimestamp = Math.floor(Date.now() / 1000)
        const midNightUnixTimestamp = Math.floor(new Date().setHours(0, 0, 0, 0) / 1000)

        for (let i = this.periodPosition; i <= Object.keys(this.table).length; i++) {
            this.periodName = this.table[i]['name']
            if (this.table[i]['start_unix'] !== null){
                if (currentUnixTimestamp < midNightUnixTimestamp + this.table[i]['start_unix']){
                    this.periodStatus = 0
                    this.periodPosition = i
                    this.seconds = midNightUnixTimestamp + this.table[i]['start_unix'] - currentUnixTimestamp
                    return
                }
            }
            if (this.table[i]['end_unix'] !== null) {
                if (currentUnixTimestamp < midNightUnixTimestamp + this.table[i]['end_unix']){
                    this.periodStatus = 1
                    this.periodPosition = i
                    this.periodEndTime = this.table[i]['end_time']
                    this.seconds = midNightUnixTimestamp + this.table[i]['end_unix'] - currentUnixTimestamp
                    return
                }
            }
        }
        return true
    }
    runSecondsCountdown (){
        this.secondsCountdown = setInterval(() => {
            this.seconds -= 1;
            if (this.seconds < 0) {
                const dayEnd = this.getCount()
                if (dayEnd === true){
                    this.dayEnd()
                }
            }
        }, 1000)
    }

    runCountdownUpdate(){
        const intervalFunction = () => {
            if (window.getComputedStyle(document.getElementById('countdown-mask')).visibility === 'visible') {
                this.updateMask();
            } else {
                this.updateTopBarCountdown();
            }
            this.updateTitle();
        };
        intervalFunction();
        this.countdownUpdate = setInterval(intervalFunction, this.updateInterval);
    }
    updateTopBarCountdown(){
        document.getElementById('countdown-bar').style.backgroundColor = this.percentageToColor()
        document.getElementById('countdown-div').style.borderColor = this.percentageToColor()
        document.getElementById('countdown-bar').style.width = `calc(${this.secondsToPercentage()}% - 6px)`

        const time = this.secondsToTime();
        const countdownTime = document.getElementById('countdown-time')
        if (countdownTime.value !== time){
            countdownTime.innerText = time;
        }
    }
    updateMask(){
        const maskTitle = document.getElementById('title-mask')
        const time = this.secondsToTime();
        let percentage
        let endTime
        let title = this.periodName
        if (this.periodStatus === 0) {
            percentage = '';
            endTime = '';
            title = title + ' starts in'
        }
        else {
            title = title + ' ends in'
            endTime = this.periodEndTime
            percentage = this.secondsToPercentage(true);
        }
        const maskTime = document.getElementById('countdown-time-mask')
        const maskPercentage = document.getElementById('percentage-mask')
        const maskEndTime = document.getElementById('end-time-mask')


        if ((maskPercentage.innerText).replace('%', '') !== percentage){
            maskPercentage.innerText = percentage !== '' ? percentage + '%' : ''
        }
        if (maskTime.innerText !== time){
            maskTime.innerText = time;
        }
        if (maskEndTime.innerText !== endTime){
            maskEndTime.innerText = endTime;
        }
        if (maskTitle.innerText !== title){
            maskTitle.innerText = title
        }
    }
    updateTitle(){
        if (document.hidden === true){
            const time = this.secondsToTime()
            if(document.title !== time){
                document.title = time;
            }  //+ (this.periodStatus === 1 ? ' — ' + Math.floor(this.secondsToPercentage()) + '%' : '');
        }
        else {
            if (document.title !== this.pageName){
                document.title = this.pageName;
            }
        }
    }
    dayEnd(){
        document.getElementById('countdown-border').replaceWith(document.createElement('div'));
        clearInterval(this.secondsCountdown);
        clearInterval(this.countdownUpdate);
    }
}


document.addEventListener("DOMContentLoaded", function () {
    if (currentScheduleTable !== null) {
        // TODO special schedule or etc
        const countdownBorderObject = document.getElementById('countdown-border')
        const countdownDivObject = document.getElementById('countdown-div')
        if (currentScheduleTable !== 'special') {
            countdown = new countdownAnalyzer(currentScheduleTable)
            if (countdown.getCount() === true){
                countdown.dayEnd()
            }
            else {
                countdownDivObject.style.border = '3px solid'
                countdownBorderObject.style.border = '1px solid'

                countdown.runSecondsCountdown()
                countdown.runCountdownUpdate()
            }
        }
        else {
            countdownBorderObject.removeAttribute("onclick");
            countdownBorderObject.style.border = '1px solid'
            countdownBorderObject.style.cursor = 'not-allowed'
            countdownDivObject.style.cursor = 'not-allowed'

            const countdownTime = document.getElementById('countdown-time')
            countdownTime.innerText = 'Special'
        }
    }
})
















function removeTransition(element){
    element.style.transition = 'none';
    element.style.webkitTransition = 'none';
}

function openCountdownMask(){
    countdown.updateMask()

    const mask = document.getElementById("countdown-mask");

    mask.style.visibility = "visible";
    mask.style.transition = 'transform 0.2s, top 0.2s, left 0.2s, width 0.2s, height 0.2s';

    setTimeout(() => {
        mask.style.width = '100vw';
        mask.style.height = '100vh';
        mask.style.transform = 'translate(-50%, -50%)';
        mask.style.top = '50%';
        mask.style.left = '50%';
        mask.style.borderRadius = '0';
    }, 0);

    setTimeout(() => {
        removeTransition(mask)
    }, 200);
}

function closeCountdownMask(){
    countdown.updateTopBarCountdown()
    const mask = document.getElementById("countdown-mask");

    mask.style.transition = 'opacity 0.2s, transform 0.2s, top 0.2s, left 0.2s, width 0.2s, height 0.2s';
    mask.style.opacity = '0';

    setTimeout(() => {
        mask.style.visibility = "hidden";

        mask.style.top = '10px';
        mask.style.left = document.getElementById('website-logo-section').offsetWidth + 10 + 'px';
        mask.style.transform = 'none';

        mask.style.height = '50px';
        mask.style.width = '200px';

        mask.style.opacity = '1';
        removeTransition(mask)
    }, 200);
}
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('countdowns')) {

        function analyzeDateAndRecord(date1, date2, index, type){
            const title = document.getElementById('time-until-' + type + '-' + index)
            const string =
                daysDifferenceToString(
                    datesToDayDifference(
                        YmdDateToDate(date1), date2
                    )
                )
            title.innerHTML = highLightNumbers(string)

            if (string.match(/\d/) === null){
                if (string === 'Today'){
                    title.style.color = '#28b463'
                }
                else if (string === 'Tomorrow') {
                    title.style.color = '#d4ac0d'
                }
                title.style.fontWeight = '700'
            }
        }
        function setDate(index, dateYmd, type){
            const string = dateToString(YmdDateToDate(dateYmd))
            object = document.getElementById('date-' + type + '-' + index)
            object.innerText = string
            document.getElementById('link-' + index).href += YmdDateToUS(dateYmd)
        }
        function processData(dataArray, currentDate, index, type){
            analyzeDateAndRecord(dataArray[index].date, currentDate, index, type)
            setDate(index, dataArray[index].date, type)
        }

        let type
        type = 'dayoff'
        if (gradeCountdowns.freshmen !== null){
            processData(gradeCountdowns, currentDate, 'freshmen', type)
        }
        if (gradeCountdowns.sophomores !== null){
            processData(gradeCountdowns, currentDate, 'sophomores', type)

        }
        if (gradeCountdowns.juniors !== null){
            processData(gradeCountdowns, currentDate, 'juniors', type)

        }
        if (gradeCountdowns.seniors != null){
            processData(gradeCountdowns, currentDate, 'seniors', type)
        }


        type = 'coolSchedule'
        if (coolDayCountdowns.nonA !== null){
            processData(coolDayCountdowns, currentDate, 'nonA', type)
        }
        if (coolDayCountdowns.earlyDismissal !== null){
            processData(coolDayCountdowns, currentDate, 'earlyDismissal', type)
        }
    }
})
class DateBar {
    constructor(currentDate, localDate) {
        this.currentDate = structuredClone(currentDate);
        this.localDate = new Date(localDate.replace('-', '/'));
        const localDateArray = localDate.split('-');
        if (localDateArray.length === 3) {
            this.type = 'MDY';
        }
        else {
            this.type = 'MY';
        }

        this.dateInfoHTML = document.getElementById('date-local');
        this.nextButtonHTML = document.getElementById('date-bar-next');
        this.prevButtonHTML = document.getElementById('date-bar-previous');

        this.nextDateIndex = null;
        this.prevDateIndex = null;

        let pathArray = []
        window.location.pathname.split('/').forEach(function (item) {
            if (item !== '') {
                pathArray.push(item);
            }
        })
        if (/\d{1,2}-\d{4}/.test(pathArray[pathArray.length - 1])) {
            pathArray.pop()
        }
        const pathStr = pathArray.join('/')
        this.path = '/' + ((pathStr.length === 0) ? '' : pathStr + '/');

        this.dayWeek = {
            0: 'Sunday', 1: 'Monday', 2: 'Tuesday', 3: 'Wednesday', 4: 'Thursday', 5: 'Friday', 6: 'Saturday',
        }
        this.dayMonth = {
            0: 'January', 1: 'February', 2: 'March', 3: 'April', 4: 'May', 5: 'June', 6: 'July', 7: 'August', 8: 'September', 9: 'October', 10: 'November', 11: 'December',
        }
    }
    findDates() {
        let nextDate = structuredClone(this.localDate);
        let prevDate = structuredClone(this.localDate);

        if (this.type === 'MDY') {
            nextDate.setDate(nextDate.getDate() + 1);
            prevDate.setDate(prevDate.getDate() - 1);
        }
        else {
            nextDate.setMonth(nextDate.getMonth() + 1);
            prevDate.setMonth(prevDate.getMonth() - 1);
        }

        this.nextDateIndex = this.dateToStr(nextDate);
        this.prevDateIndex = this.dateToStr(prevDate);


    }
    updateButtons (){
        this.nextButtonHTML.href = this.path + this.nextDateIndex;
        this.prevButtonHTML.href = this.path + this.prevDateIndex;
    }
    updateDateInfo (){
        const difference = this.countDifference()
        this.dateInfoHTML.innerHTML = `<span>${this.localDateToStr()}</span> <span>-</span> <span ${(difference === 0) ? 'class="today"' : ''}>${this.explainDifference(difference)}</span>`;
    }





    dateToStr(dateObject){
        if (this.type === 'MDY') {
            return this.ifLessThan10Add0(dateObject.getMonth() + 1) + '-' + this.ifLessThan10Add0(dateObject.getDate()) + '-' + dateObject.getFullYear();
        }
        else {
            return this.ifLessThan10Add0(dateObject.getMonth() + 1) + '-' + dateObject.getFullYear();
        }
    }
    ifLessThan10Add0(int){
        if (int < 10) {
            return '0' + int;
        }
        return int;
    }
    countDifference() {
        if (this.type === 'MDY') {
            return datesToDayDifference(this.localDate, this.currentDate)
        }
        else {
            let months
            months = (this.localDate.getFullYear() - this.currentDate.getFullYear()) * 12;
            months -= this.currentDate.getMonth();
            months += this.localDate.getMonth();
            return months
        }
    }
    localDateToStr(){
        if (this.type === 'MDY') {
            return this.dayWeek[this.localDate.getDay()] + ', ' + this.dayMonth[this.localDate.getMonth()] + ' ' + this.localDate.getDate() + ', ' + this.localDate.getFullYear();
        }
        else {
            return this.dayMonth[this.localDate.getMonth()] + ' ' + this.localDate.getFullYear();
        }
    }
    explainDifference(int){
        if (this.type === 'MDY'){
            return daysDifferenceToString(int)
        }
        else {
            if (int === 0){
                return 'This month';
            }
            if (int === -1){
                return 'Last month';
            }
            if (int === 1){
                return 'Next month';
            }
            if (int > 0){
                return 'In ' + int + ' months';
            }
            else {
                return Math.abs(int) + ' moths ago';
            }
        }
    }
}


document.addEventListener("DOMContentLoaded", function() {
    const topBarDate = document.getElementById('date-bar')
    if (topBarDate) {
        const DateBarClass = new DateBar(currentDate, date);
        DateBarClass.findDates()
        DateBarClass.updateButtons()
        DateBarClass.updateDateInfo()
    }
})
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
const dayWeekArray = {
    0: 'Sunday', 1: 'Monday', 2: 'Tuesday', 3: 'Wednesday', 4: 'Thursday', 5: 'Friday', 6: 'Saturday',
}
const dayMonthArray = {
    0: 'January', 1: 'February', 2: 'March', 3: 'April', 4: 'May', 5: 'June', 6: 'July', 7: 'August', 8: 'September', 9: 'October', 10: 'November', 11: 'December',
}

function datesToDayDifference(date1, date2) {
    const millisecondsDifference = date1.getTime() - date2.getTime();
    return Math.ceil(millisecondsDifference / (1000 * 60 * 60 * 24))
}
function daysDifferenceToString(days){
    if (days === 0) {
        return 'Today';
    }
    if (days === -1){
        return 'Yesterday';
    }
    if (days === 1){
        return 'Tomorrow';
    }
    if (days > 0){
        return 'In ' + days + ' days';
    }
    else {
        return Math.abs(days) + ' days ago';
    }
}
function YmdDateToDate(date){
    date = date.replaceAll('-', '/')
    return new Date(date);
}
function dateToString(date){
    return dayWeekArray[date.getDay()] + ', ' +  dayMonthArray[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
}
function YmdDateToUS(date){
    const dateArray = date.split('-');
    return dateArray[1] + '-' + dateArray[2] + '-' + dateArray[0]
}

function popOutTextVisible(id) {
    document.getElementById(id).style.display = 'flex'

}
function popOutTextInVisible(id) {
    document.getElementById(id).style.display = 'none'
}



function highLightNumbers (str){
    return str.replace(/\d+/g, '<span class="make-text-more-visible">$&</span>');
}

onload = function() {
    var {
        OverlayScrollbars,
        ScrollbarsHidingPlugin,
        SizeObserverPlugin,
        ClickScrollPlugin
    } = OverlayScrollbarsGlobal;
    OverlayScrollbars(document.querySelector('#target'), {
        paddingAbsolute: false,
        showNativeOverlaidScrollbars: false,
        update: {
            elementEvents: [['img', 'load']],
            debounce: [0, 33],
            attributes: null,
            ignoreMutation: null,
        },
        overflow: {
            x: 'hidden',
            y: 'scroll',
        },
        scrollbars: {
            theme: 'os-theme-dark',
            visibility: 'auto',
            autoHide: 'leave',
            autoHideDelay: 700,
            autoHideSuspend: false,
            dragScroll: true,
            clickScroll: false,
            pointers: ['mouse', 'touch', 'pen'],
        },
    });
}

let options = {
            timeZone: 'America/New_York',
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
    },
    formatter = new Intl.DateTimeFormat([], options);
addEventListener("DOMContentLoaded", (event) => {
    // const sideBarStatus = getCookieValue('sideBar')
    // console.log(sideBarStatus);
    // toggleSideBar(sideBarStatus, false)
    const account_box_element = document.getElementById('account-box')
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
    console.log(status);
    document.cookie = "sideBar=";
    document.cookie = "sideBar=" + status;

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
        element.style.transitionDuration = duration
    }
    function changeStatus(object, className, status) {
        if (status === 0) {
            object.classList.remove('r-' + className)
            object.classList.add('s-' + className)
        }
        else {
            object.classList.remove('s-' + className)
            object.classList.add('r-' + className)
        }
    }

    const sideBarObject = document.getElementById('side-bar');
    const topBarObject = document.getElementById('top-bar');
    const collapse = document.getElementById('collapse-button');
    const collapseIcon = collapse.getElementsByClassName('icon')[0]

    const websiteLogo = document.getElementById('website-logo')
    const websiteLogoDivider = document.getElementById('website-logo-divider')
    const websiteLogoDividerLine = document.getElementById('website-logo-divider-line')
    const websiteTitle = document.getElementById('website-title')

    const content = document.getElementById('content');

    // if (animation) {
    //     transitionTo(topBarObject, transition.time)
    //     transitionTo(sideBarObject, transition.time)
    //     transitionTo(collapse, transition.time)
    //     transitionTo(collapseIcon, transition.time)
    //     transitionTo(collapseP, transition.time)
    //
    //     transitionTo(websiteLogo, transition.time)
    //     transitionTo(websiteLogoDivider, transition.time)
    //     transitionTo(websiteLogoDividerLine, transition.time)
    //     transitionTo(websiteTitle, transition.time)
    //
    //     transitionTo(content, transition.time)
    // }

    // const pagesTitlesObjects = sideBarObject.getElementsByClassName('pageTitle')

    changeStatus(sideBarObject, 'side-bar', status)
    changeStatus(topBarObject, 'top-bar', status)
    changeStatus(collapse, 'collapse-button', status)
    changeStatus(collapseIcon, 'icon', status)
    changeStatus(websiteLogo, 'icon-holder', status)
    changeStatus(websiteLogoDivider, 'website-divider-holder', status)
    changeStatus(websiteLogoDividerLine, 'website-divider', status)
    changeStatus(websiteTitle, 'logoTitle', status)
    changeStatus(content, 'content', status)


    if (status === 0) {
        collapse.onclick = () => {
            toggleSideBar(1)
        }
    } else {
        collapse.onclick = () => {
            toggleSideBar(0)
        }
    }



    // await removeTimeOut(sideBarObject, transition.milliseconds);
    // await removeTimeOut(collapseIcon, transition.milliseconds);
    // await removeTimeOut(collapseP, transition.milliseconds);
    // await removeTimeOut(collapse, transition.milliseconds);
    // await removeTimeOut(content, transition.milliseconds);
}

