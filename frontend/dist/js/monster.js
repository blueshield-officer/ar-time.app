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
function findPeriodDimention(bells, position, dayStartUnix) {
    const gap = parseInt(getComputedStyle(document.querySelector('#timestamp-block')).getPropertyValue('gap'))

    let top = null
    let height = null
    const pixelsPerHour = document.getElementsByClassName('time-line')[0].offsetHeight + gap
    const pixelsPerMinute = pixelsPerHour / 60
    const topError = document.getElementsByClassName('time-line')[0].offsetHeight / 2
    let type

    const periodName = bells[position]['name'].toLowerCase()
    if (['announcements', 'warning bell'].includes(periodName)) {
        type = 'label'
    }
    else {
        type = 'default'
    }

    const periodStartUnix = bells[position]['start_unix']
    const periodEndUnix = bells[position]['end_unix']


    if (periodStartUnix !== null) {
        top = (pixelsPerHour * ((periodStartUnix - dayStartUnix) / 60 / 60)) + topError
        if (type === 'label') {
            top = top - ((10 / pixelsPerMinute) / 2) * pixelsPerMinute + 1
        }
    }
    if (periodStartUnix !== null && periodEndUnix !== null) {
        height = pixelsPerHour * ((periodEndUnix - periodStartUnix) / 60 / 60)
    }

    return dimensions = {
        top: top,
        height: height,
        type: type,
    }
}

function setMinWidthOfTitle(){
    const periodBoxArray = document.querySelectorAll('.period-box')
    periodBoxArray.forEach(periodBox => {
        const height = periodBox.offsetHeight
        periodBox.querySelector('.name').style.minWidth = height + 'px'
    })
}

addEventListener("DOMContentLoaded", () => {

    if (document.getElementById('bells-block')){
        const startUnix = startTime * 60 * 60

        // let test = 1
        // setInterval(() => {
        //     document.getElementById('current-time-runner').style.top = test + 'px'
        //     test ++
        // }, 100)


        for (let i = 0; i <  Object.keys(bells).length; i++) {
            const objectData = findPeriodDimention(bells, i, startUnix)
            const periodObject = document.getElementById('period-' + i)
            periodObject.style.top = objectData.top + 'px'
            periodObject.style.height = objectData.height + 'px'
            if (objectData.type === 'label') {
                periodObject.classList.remove('default-box')
                periodObject.classList.add('label-box')
            }
            else if (objectData.height < 30){
                periodObject.classList.add('period-info-hidden')
                periodObject.addEventListener('mouseover', function () {
                    periodObject.style.height = '50px'
                })
                periodObject.addEventListener('mouseout', function (){
                    periodObject.style.height = objectData.height + 'px'
                })
            }
        }

        setMinWidthOfTitle()
    }
});
class CalendarBig {
    constructor(currentDate, localDate, localDateGlobal, dayInfo, events, sports, dayOffs, retreats) {
        this.currentDate = clone(currentDate)
        this.localDate = clone(localDate)
        this.localDateGlobal = clone(localDateGlobal)
        this.prevMonthObject = clone(this.localDate)
        this.nextMonthObject = clone(this.localDate)

        this.prevMonthObject.setMonth(this.prevMonthObject.getMonth() - 1)
        this.nextMonthObject.setMonth(this.nextMonthObject.getMonth() + 1)


        this.dayInfo = dayInfo
        this.events = events
        this.sports = sports
        this.dayOffs = dayOffs
        this.retreats = retreats

        this.dayInfoPart = document.getElementById('calendar-day-info')
        this.dayInfoObject = document.getElementById('day-info')

        this.calendarFieldsBox = document.getElementById('calendar-big-days')
        this.localDateStr = document.getElementById('local-date-str')
    }

    loadDaysCalendar() {
        let leftDays = 42;

        const currentMonthDays = getMonthDays(null, null, this.localDate);
        const firstDayOfWeek = getFirstWeekDay(null, null, this.localDate);

        const startOffset = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

        const prevMonthDays = getMonthDays(null, null, this.prevMonthObject);

        for (let i = prevMonthDays - startOffset + 1; i <= prevMonthDays; i++) {
            leftDays -= 1;
            this.insertDay(this.prevMonthObject.getFullYear(), this.prevMonthObject.getMonth(), i);
        }

        for (let i = 1; i <= currentMonthDays; i++) {
            leftDays -= 1;
            this.insertDay(this.localDate.getFullYear(), this.localDate.getMonth(), i);
        }

        for (let i = 1; i <= leftDays; i++) {
            this.insertDay(this.nextMonthObject.getFullYear(), this.nextMonthObject.getMonth(), i);
        }
    }

    analyzeDataAmount(object){
        if (object !== null){
            return object.length
        }
        else {
            return 0;
        }
    }

    insertDay(year, month, day){
        month ++
        const dateIndex = year + '-' + ifLessThan10Add0(month) + '-' + ifLessThan10Add0(day)

        const dayObject = document.createElement('li')
        dayObject.classList.add('day', 'border-color-theme', 'background-theme', 'selectable')
        if (this.currentDate.getFullYear() === year &&  this.currentDate.getMonth() + 1 === month &&this.currentDate.getDate() === day){
            dayObject.classList.add('current-day')
        }
        if (this.localDateGlobal.getFullYear() === year &&  this.localDateGlobal.getMonth() + 1 === month && this.localDateGlobal.getDate() === day){
            this.displayDayInfo(dateIndex, false)
        }

        if (isWeekEnd(year, month, day)) dayObject.classList.add('weekend')


        // main day info
        const dayInfo = this.dayInfo[dateIndex]
        let scheduleStr = ''
        let testDayStr = ''
        if (dayInfo !== null){
            if (dayInfo['schedule'] === 'Special'){
                dayInfo['schedule'] = 'S'
            }
            scheduleStr = dayInfo['schedule']
            if (dayInfo['testDay'] !== null){
                testDayStr += '(' + dayInfo['testDay'] + ')'
            }
        }
        // day info
        const dayDateAndInfo = document.createElement('p')
        if (this.localDate.getMonth() + 1 !== month){
            dayDateAndInfo.classList.add('secondary-day-info')
        }
        dayDateAndInfo.innerHTML = `
        <div class="day-title">
            <p class="day-number">${day}</p> 
            <p class="academic-info">
                <span class="testday">${testDayStr}</span>
                <span class="schedule">${scheduleStr}</span>
            </p>
        </div>
        `


        const dayAdditional = document.createElement('div')
        dayAdditional.classList.add('day-additional')
        if (this.localDate.getMonth() + 1 !== month){
            dayAdditional.classList.add('secondary-day-info')
        }

        // day events (important, events, sports, etc)
        const dayEvents = document.createElement('div')
        dayEvents.classList.add('day-events')


        let importantEventsAmount = 0
        if (this.retreats[dateIndex] !== null){
            importantEventsAmount += 1
        }
        let eventsAmount = this.analyzeDataAmount(this.events[dateIndex]) // events
        if (eventsAmount !== 0){
            Array.from(this.events[dateIndex]).forEach((event) => {
                if (event['important'] === 1){
                    importantEventsAmount += 1
                    eventsAmount --
                }
            })
        }
        if (importantEventsAmount > 0){
            const importantEventsSection = document.createElement('p')
            importantEventsSection.innerText = importantEventsAmount
            importantEventsSection.classList.add('events-important', 'number-holder', 'golden-5', 'quantity-number-holder')
            dayEvents.appendChild(importantEventsSection)
        }
        if (eventsAmount > 0){
            const eventsSection = document.createElement('p')
            eventsSection.innerText = eventsAmount
            eventsSection.classList.add('events', 'number-holder', 'golden-5', 'quantity-number-holder')
            dayEvents.appendChild(eventsSection)
        }
        const sportsAmount = this.analyzeDataAmount(this.sports[dateIndex]) // sports
        if (sportsAmount !== 0){
            const sportsSection = document.createElement('p')
            sportsSection.innerText = sportsAmount
            sportsSection.classList.add('sports', 'number-holder', 'golden-5', 'quantity-number-holder')
            dayEvents.appendChild(sportsSection)
        }
        const dayOffsObject = document.createElement('div')
        dayOffsObject.classList.add('day-offs')
        let dayOff = this.dayOffs[dateIndex] !== null
        if (dayOff === true){
            dayOff = this.dayOffs[dateIndex]

            let allGradesOut = true
            for (let i = 9; i <= 12; i++){
                if (dayOff[i] !== 1) allGradesOut = false
            }
            if (!allGradesOut){
                for (let i = 9; i <= 12; i++) {
                    if (dayOff[i] === 1) {
                        const gradeName = gradeToName(i)
                        const dayOffGradeObject = document.createElement('p')
                        dayOffGradeObject.classList.add(gradeName, 'day-off', 'golden-5')
                        dayOffGradeObject.innerText = getCapitalizedFirstLetter(gradeName)
                        dayOffsObject.appendChild(dayOffGradeObject)
                    }
                }
            }
            else {
                dayObject.classList.add('day-day-off')
                const dayOffGradeObject = document.createElement('p')
                dayOffGradeObject.classList.add('all', 'day-off', 'golden-5')
                dayOffGradeObject.innerText = 'All'
                dayOffsObject.appendChild(dayOffGradeObject)
            }
        }



        // last
        dayObject.appendChild(dayDateAndInfo)
        dayAdditional.appendChild(dayEvents)
        dayAdditional.appendChild(dayOffsObject)
        dayObject.appendChild(dayAdditional)

        dayObject.addEventListener("click", () => {
            this.displayDayInfo(dateIndex)
        });

        this.calendarFieldsBox.appendChild(dayObject)
    }


    displayDayInfo(index, changeLocalDate = true){
        if (changeLocalDate){
            fetchServer('localDate', index, 'request')
        }

        this.dayInfoObject.innerHTML = ''

        this.createLocalDateStr(index)
        const mainDayInfo = this.createMainInfoObject(index)
        const events = this.createEventsObject(index)
        const sports = this.createSportsObject(index)

        if (mainDayInfo !== null){
            this.dayInfoObject.appendChild(mainDayInfo)
        }
        if (events !== null){
            this.dayInfoObject.appendChild(events)
        }
        if (sports !== null){
            this.dayInfoObject.appendChild(sports)
        }

    }

    createLocalDateStr(index){
        this.localDateStr.innerHTML = `
            <p>${dateToFullString(YmdDateToDate(index))}</p>
        `
    }

    createMainInfoObject(index) {
        if (this.dayInfo[index] || this.dayOffs[index] || this.retreats[index]) {
            const events_holder = document.createElement('div')
            events_holder.classList.add('event-holder')

            let html = ''

            if (this.dayInfo[index]) {
                let dayInfo = this.dayInfo[index]
                if (dayInfo.schedule === 'S') {
                    dayInfo.schedule = 'Special'
                }

                html += `
                        <div class="main-day-info">
                            <div class="event important default-border golden-5">
                                <p class="main-day-title"><span class="very-important-text">${dayInfo.schedule}</span> Schedule</p>
                                <p class="description secondary-text">${dayInfo.description}</p>
                            </div>
                `;

                if (dayInfo['testDay']) {
                    html += `
                            <div class="event important default-border golden-5">
                                <p class="main-day-title">Day <span class="very-important-text">${dayInfo['testDay']}</span></p>
                            </div>
                         </div>
                `;
                }

                html += '</div>'
            }

            if (this.dayOffs[index]) {
                let dayOffs = this.dayOffs[index]
                let dayOff_str
                if (dayOffs['type'] === 'f') {
                    dayOff_str = 'No school'
                } else {
                    dayOff_str = 'There are online classes'
                }

                let dayOffArray = []
                for (let i = 9; i <= 12; i++) {
                    if (dayOffs[i]) {
                        dayOffArray.push(`<span class="${gradeIntToClass(i)} grade-name">${gradeIntToStr(i, 1)}</span>`)
                    }
                }

                if (dayOffArray.length === 4){
                    dayOff_str += ' for <span class="all grade-name">All</span> grades'
                }
                else {
                    dayOff_str +=  ` for ${dayOffArray.join(' ')}`
                }
                html += `
                 <div class="event default-border border-color-theme golden-5">
                      <p>${dayOff_str}</p>
                </div>
                `
            }

            html += '</div>'
            events_holder.innerHTML = html;

            return events_holder
        }
        return null;
    }
    createEventsObject(index){
        if (this.events[index]) {
            const events_holder = document.createElement('div')
            events_holder.classList.add('event-holder')

            let html = ''
            const events = this.events[index]
            Array.from(events).forEach(function (event){
                html += `
                    <div class="event default-border border-color-theme golden-5">
                        <p class="event-title">${event['title']}</p>
                   `
                if (event['description']){
                    html += `
                        <p class="secondary-text">${event['description']}</p>
                    `
                }
                let timeString = ''
                if (event['time_start']){
                    timeString = calculateTimeForEventString(index, event['time_start'], event['time_end'])
                }
                html += `
                        <p class="time-field secondary-text">${timeString}</p>
                    </div>`

            })
            events_holder.innerHTML = html
            return events_holder
        }
        return null
    }
    createSportsObject(index){
        if (this.sports[index]){
            const sports_holder = document.createElement('div')
            sports_holder.classList.add('event-holder')

            let html = ''
            const sports = this.sports[index]
            Array.from(sports).forEach(function (sport) {
                const token = generateToken()

                let gender
                if (sport['gender'] === 'B'){
                    gender = 'boys'
                }
                else {
                    gender = 'girls'
                }

                html += `
                <div class="event default-border border-color-theme golden-5 ${gender}">
                    <div class="event-title">
                        <p>${sport['varsity']}</p>
                        <p style="align-content: center">${sport['sport']}</p>
                        <p>${sport['gender']}</p>    
                    </div>
                `
                if (sport['opponent'] !== null){
                    html += `<p class="enemy golden-5">${sport['opponent']}`
                }
                if (sport['side'] !== null){
                    html += ` — ${sport['side']}`
                }
                html += '</p>'
                let timeString = ''
                if (sport['time_start']){
                    timeString = calculateTimeForEventString(index, sport['time_start'], sport['time_end'])
                }
                html += `
                    <p id="${token}" class="time-field secondary-text"><span>${timeString}</span></p>
                </div>`


            })
            sports_holder.innerHTML = html
            return sports_holder;
        }
        return null;
    }
}

addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('calendar-page')){
        const calendar = new CalendarBig(
            currentDate, localDate, localDateGlobal, dayInfoArray, eventsArray, sportsArray, dayOffArray, retreatsArray
        )
        calendar.loadDaysCalendar()
    }
})
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

        this.periodStarted = false
        this.periodPosition = 0
        this.nextTimeStamp = null

        this.updateInterval = 1000

        this.periodStatusBarObjects = {}
        this.periodBoxDimention = {}

        this.dayOver = false

        this.overlay = false

        if (getCookieValue('overlayOnSleepTime') === null){
            setPermaCookie('overlayOnSleepTime', 5)
        }

        this.secondsUntilSleepModeConstant = getCookieValue('overlayOnSleepTime') * 60
        this.secondsUntilSleepMode = getCookieValue('overlayOnSleepTime') * 60
        this.sleepMode = strBooleanToBoolean(getCookieValue('overlayOnSleep'))
    }


    startUpdateInterval(){
        const intervalFunction = () => {
            if (window.getComputedStyle(document.getElementById('countdown-mask')).visibility === 'visible') {
                this.updateMask();
            }
            else {
                this.updateTopBarCountdown();
            }
            if (getCookieValue('tabCounter') === 'true'){
                this.updatePageTitle();
            }
            if (document.getElementById('current-time-runner')) {
                this.updateTimeRunner()
            }
            this.updateTable()
        };
        this.updateTable()
        intervalFunction();
        this.updateInterval = setInterval(intervalFunction, this.updateInterval);
    }

    findTimeUntilNext(){
        if (this.nextTimeStamp !== null){
            return this.nextTimeStamp - (Math.floor(Date.now() / 1000));
        }
        else {
            return null
        }
    }

    startSecondsCount() {
        this.lastTick = Date.now()
        this.secondsCountdown = setInterval(() => {
            const now = Date.now();
            const deltaSeconds = Math.floor((now - this.lastTick) / 1000);

            if (deltaSeconds > 0) {
                this.lastTick = now;

                if (this.sleepMode === true){
                    console.log(this.secondsUntilSleepMode)
                    this.secondsUntilSleepMode -= deltaSeconds;
                    if (this.secondsUntilSleepMode <= 0 && this.overlay === false) {
                        openCountdownMask()
                    }
                }

                if (this.findTimeUntilNext() < 0) {
                    const dayEnd = this.findCurrentPeriodData()
                    if (dayEnd === false){
                        this.endDay()
                    }
                }
            }
        }, 1000);
    }

    checkIsItTooEarly(){
        if (this.periodPosition === 0 &&
            this.findTimeUntilNext() >= 3600 &&
            this.periodStarted === false){
            return true;
        }
        return false;
    }

    findCurrentPeriodData() {
        const currentUnixTimestamp = Math.floor( Date.now() / 1000 )
        const midNightUnixTimestamp = Math.floor( new Date().setHours(0, 0, 0, 0) / 1000 )

        for (let i = this.periodPosition; i < Object.keys(this.table).length; i++) {
            this.periodName = this.table[i]['name']
            if (this.table[i]['start_unix'] !== null){
                if (currentUnixTimestamp < midNightUnixTimestamp + this.table[i]['start_unix']){
                    this.periodStarted = false
                    this.periodPosition = i
                    this.nextTimeStamp = midNightUnixTimestamp + this.table[i]['start_unix']
                    return
                }
            }
            if (this.table[i]['end_unix'] !== null) {
                if (currentUnixTimestamp < midNightUnixTimestamp + this.table[i]['end_unix']){
                    this.periodStarted = true
                    this.periodPosition = i
                    this.periodEndTime = this.table[i]['end_time']
                    this.nextTimeStamp = midNightUnixTimestamp + this.table[i]['end_unix']
                    return
                }
            }
        }
        return false
    }





    updateTopBarCountdown(){
        document.getElementById('countdown-bar').style.backgroundColor = this.getColor()
        document.getElementById('countdown-div').style.borderColor = this.getColor()
        document.getElementById('countdown-bar').style.width = `calc(${this.getPercentage()}% - 6px)`

        const time = this.getTime();
        const countdownTime = document.getElementById('countdown-time')
        if (countdownTime.value !== time){
            countdownTime.innerText = time;
        }
    }

    updatePageTitle(){
        if (document.hidden === true){
            const time = this.getTime()
            if(document.title !== time){
                document.title = time;
            }  //+ (this.periodStarted === true ? ' — ' + Math.floor(this.secondsToPercentage()) + '%' : '');
        }
        else {
            if (document.title !== this.pageName){
                document.title = this.pageName;
            }
        }
    }
    updateTable (){
        if (isEmpty(this.periodStatusBarObjects)){
            Array.from(document.getElementsByClassName('period-box')).forEach((item, index) => {
                this.periodStatusBarObjects[index] = (item.getElementsByClassName('period-status-bar')[0])
            })
            if (sameDay(localDate, currentDate)){
                for (let i = 0; i < Object.keys(this.periodStatusBarObjects).length; i++) {
                    const statusBarElement = this.periodStatusBarObjects[i];

                    if (i !== this.periodPosition) {
                        statusBarElement.style.height = '100%';
                    } else if (this.periodStarted === true) {
                        statusBarElement.style.height = this.getPercentage() + '%'
                        break;
                    }
                    else {
                        break
                    }
                }
            }
        }
        else {
            if (sameDay(localDate, currentDate) && this.periodStarted === true){
                this.periodStatusBarObjects[this.periodPosition].style.height = this.getPercentage() + '%';
            }

        }
    }

    updateMask(isDayOver = false){
        const maskTitle = document.getElementById('title-mask')
        const maskTime = document.getElementById('countdown-time-mask')
        const maskPercentage = document.getElementById('percentage-mask')
        const maskEndTime = document.getElementById('end-time-mask')

        if (this.isDayOver !== true && this.findTimeUntilNext() !== -1){
            const time = this.getTime();
            let percentage
            let endTime
            let title = this.periodName
            if (this.periodStarted === false) {
                percentage = '';
                endTime = '';
                title = title + ' starts in'
            }
            else {
                title = title + ' ends in'
                endTime = this.getEndTime()
                percentage = this.getPercentage(true);
            }



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
        else {
            addTransitionToElement(maskTitle)
            addTransitionToElement(maskTime)
            addTransitionToElement(maskPercentage)
            addTransitionToElement(maskEndTime)

            const objectArray = [maskTitle, maskTime, maskPercentage, maskEndTime]
            for (let i = 0; i < objectArray.length; i++) {
                setTimeout(() => objectArray[i].style.opacity = '0', (i + 1) * 100)
            }

            setTimeout(function() {
                maskTime.innerText = 'Still here?'
                addTransitionToElement(maskTime, 0.6)
            }, 3000)
            setTimeout(() => maskTime.style.opacity = '1', 60000)
        }
    }

    updateTimeRunner(){
        const pixelsPerHour = document.getElementsByClassName('time-line')[0].offsetHeight + parseInt(getComputedStyle(document.querySelector('#timestamp-block')).getPropertyValue('gap'))
        document.getElementById('current-time-runner').style.top = pixelsPerHour * ((getTimeFromMidnight() - (startTime * 60 * 60)) / 60 / 60) + document.getElementsByClassName('time-line')[0].offsetHeight / 2 + - 2.5 + 'px'
    }

    addPeriodBoxDimension(position, array){
        this.periodBoxDimention[position] = array
    }


    setSecondsUntilSleepMode(minutes){
        this.secondsUntilSleepModeConstant = minutes * 60
        this.resetSecondsUntilSleepMode()
    }
    resetSecondsUntilSleepMode(){
        this.secondsUntilSleepMode = this.secondsUntilSleepModeConstant
    }
    setSleepMode(boolean){
        if (boolean === true){
            this.secondsUntilSleepMode = this.secondsUntilSleepModeConstant
        }
        this.sleepMode = boolean
    }



    getPercentage(floor = false) {
        if (this.periodStarted === false) {
            return 100
        }
        let percentage
        const totalSeconds =  this.table[this.periodPosition]['end_unix'] - this.table[this.periodPosition]['start_unix']
        percentage = 100 - ((100 / totalSeconds) * this.findTimeUntilNext())
        if (floor === true) {
            percentage = Math.floor(percentage)
        }
        return percentage
    }

    getColor() {
        let degree
        if (this.periodStarted === false){
            degree = 50;
        }
        else {
            degree = (120 / 100) * this.getPercentage()
        }
        return `hsl(${degree} 95% 50%)`;
    }

    getTime() {
        const seconds = this.findTimeUntilNext()
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;

        // Format to always have two digits
        const formattedMinutes = minutes.toString().padStart(2, '0');
        const formattedSecs = secs.toString().padStart(2, '0');

        return `${formattedMinutes} : ${formattedSecs}`;
    }
    getEndTime(){
        return this.periodEndTime
    }
    getDayStatus() {
        return this.dayStatus
    }
    getCurrentPeriod(){
        return {
            'period': this.periodPosition,
            'status': this.periodStarted,
        }
    }
    isDayOver(){
        return this.dayOver
    }



    endDay(){
        this.dayOver = true
        const timeRunner = document.getElementById('current-time-runner')
        if (timeRunner){
            timeRunner.remove();
        }
        const countdownBorder = document.getElementById('countdown-border')
            if (countdownBorder){
                countdownBorder.replaceWith(document.createElement('div'));
            }
        clearInterval(this.updateInterval);
        clearInterval(this.secondsCountdown)
        if (document.getElementById('home-page') && sameDay(currentDate, localDate)){
            document.getElementById('bells-block').classList.add('day-done');
        }
        document.removeEventListener('keydown', (event) => {})
        document.removeEventListener('keyup', function (event) {
            if (!event.repeat) {
                makeCrossButtonActive()
            }
        })
        this.updateMask()
    }
}


function  closeCountdownMask(){
    countdown.overlay = false
    countdown.resetSecondsUntilSleepMode()

    const mask = document.getElementById("countdown-mask");

    if (!countdown.isDayOver()){
        countdown.updateTopBarCountdown()
    }

    mask.style.transition = 'opacity 0.2s, transform 0.2s, top 0.2s, left 0.2s, width 0.2s, height 0.2s';
    mask.style.opacity = '0';

    setTimeout(() => {
        if (!countdown.isDayOver()) {
            mask.style.visibility = "hidden";

            mask.style.top = '8px';
            if (document.getElementById('nav-bar-toggle').style.display !== 'none') {
                mask.style.left = document.getElementById('side-bar').offsetWidth + 16 + 'px';
                mask.style.transform = 'none';
            }
            else {
                mask.style.left = '50%';
                mask.style.transform = 'translate(-50%, -50%)';
            }


            mask.style.height = '56px';
            mask.style.width = '202px';

            mask.style.opacity = '1';
        }

        document.getElementById('cross-button').classList.remove('hover')
        removeTransition(mask)
    }, 200);
}

document.addEventListener("DOMContentLoaded", function () {
    if (typeof currentTable !== 'undefined' && document.getElementById('top-bar')) {
        if (currentTable !== null){
            // TODO special schedule or etc
            const countdownBorderObject = document.getElementById('countdown-border')
            const countdownDivObject = document.getElementById('countdown-div')
            if (currentTable !== 'special') {
                countdown = new countdownAnalyzer(currentTable)
                if (countdown.findCurrentPeriodData() === false){
                    countdown.endDay()
                }
                else {
                    if (countdown.checkIsItTooEarly()){
                        if (document.getElementById('current-time-runner')){
                            document.getElementById('current-time-runner').style.display = 'none'
                        }
                    }
                    else {
                        countdownDivObject.style.border = '3px solid'
                        countdownBorderObject.style.border = '1px solid'
                        countdown.startSecondsCount()
                        countdown.startUpdateInterval()

                        document.addEventListener("keydown", function (event) {
                            if (!event.repeat) {
                                makeCrossButtonActive()
                            }
                        });
                        document.addEventListener("keyup", closeCountdownMask)

                        document.addEventListener('click', () => countdown.resetSecondsUntilSleepMode())
                        document.addEventListener('mousemove', () => countdown.resetSecondsUntilSleepMode())
                    }
                }
            }
            else {
                if (getTimeFromMidnight < 51600) {
                    countdownBorderObject.removeAttribute("onclick");
                    countdownBorderObject.style.border = '1px solid'
                    countdownBorderObject.style.cursor = 'not-allowed'
                    countdownDivObject.style.cursor = 'not-allowed'

                    const countdownTime = document.getElementById('countdown-time')
                    countdownTime.innerText = 'Special'
                }
            }
        }
    }
})













function removeTransition(element){
    element.style.transition = 'none';
    element.style.webkitTransition = 'none';
}

function openCountdownMask(){
    countdown.overlay = true
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



function makeCrossButtonActive(){
    if (document.getElementById("countdown-mask").style.visibility === 'visible'){
        document.getElementById('cross-button').classList.add('hover')
    }
}

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('countdowns-page')) {

        function processDataTwo(date1, date2, currentDate, index, type){
            function addStylesAndTextToImportantTitle(descriptions, title){
                title.innerText = descriptions
                title.style.fontWeight = '700'
            }
            const title = document.getElementById('time-until-' + type + '-' + index)

            const date1Object = YmdDateToDate(date1)
            const date2Object = YmdDateToDate(date2)
            const descriptions = daysToStatus(
                datesToDayDifference(
                    YmdDateToDate(date1), currentDate
                ),
                datesToDayDifference(
                    YmdDateToDate(date2), currentDate
                ), date1Object, date2Object
            )
            if (descriptions === 'Ended') {
                addStylesAndTextToImportantTitle(descriptions, title)
                title.style.alignSelf = 'center'
                title.style.justifySelf = 'center'
                title.style.color = '#28b463'
                title.style.marginTop = 'auto'
                title.style.marginBottom = 'auto'
                document.getElementById('link-nextDate-' + index).remove();
            }

            else {
                if (descriptions.includes('tomorrow')) {
                    addStylesAndTextToImportantTitle(descriptions, title)
                    title.style.color = '#d4ac0d'
                }
                else if (descriptions.includes('today')) {
                    addStylesAndTextToImportantTitle(descriptions, title)
                    title.style.color = '#bc2424'
                }
                else {
                    title.innerHTML = highLightNumbers(descriptions)
                }
                if (descriptions.includes('Starts')) {
                    setDate(index, date1, type)
                }
                else {
                    setDate(index, date2, type)
                }
            }
        }
        function analyzeDateAndRecord(date1, dateCurrent, index, type){
            const title = document.getElementById('time-until-' + type + '-' + index)
            const string =
                daysDifferenceToString(
                    datesToDayDifference(
                        YmdDateToDate(date1), dateCurrent
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
            const string = dateToFullString(YmdDateToDate(dateYmd))
            object = document.getElementById('date-' + type + '-' + index)
            if (object){
                object.innerText = string
                document.getElementById('link-' + index).href += dateToUS(dateYmd)
            }
        }
        function processData(dataArray, currentDate, index, type){
            if (index !== null){
                dataArray = dataArray[index]
            }
            analyzeDateAndRecord(dataArray.date, currentDate, index, type)
            setDate(index, dataArray.date, type)
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
        if (gradeCountdowns.seniors != null) {
            processData(gradeCountdowns, currentDate, 'seniors', type)
        }


        type = 'coolSchedule'
        if (coolDayCountdowns.nonA !== null){
            processData(coolDayCountdowns, currentDate, 'nonA', type)
        }
        if (coolDayCountdowns.earlyDismissal !== null){
            processData(coolDayCountdowns, currentDate, 'earlyDismissal', type)
        }

        type = 'holiday'
        if (holidays !== null){
            Object.keys(holidays).forEach(key => {
                processData(holidays, currentDate, key, type)
            });
        }

        type = 'academics'
        if (academics !== null){
            Object.keys(academics).forEach(key => {
                processDataTwo(academics[key][1], academics[key][0], currentDate, key, type)
            });
        }
    }
})
class DateBar {
    constructor(currentDate, localDate) {
        this.currentDate = structuredClone(currentDate);
        this.localDate = YmdDateToDate(localDate);
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

        this.calendarDate = clone(this.localDate)

        this.calendarDaysObject = document.getElementById('calendar-days')
        this.calendarMonthObject = document.getElementById('calendar-months')

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
        this.dateInfoHTML.innerHTML = `<span id="local-date-string">${this.localDateToStr()}</span> <span class="date-bar-divider">|</span> <span  class="date-bar-difference background-theme light-shadow ${(difference === 0) ? 'today' : ''}" >${this.explainDifference(difference)}</span>`;
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
            return dateToFullString(this.localDate)
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







    changeCalendar(int){
        if (this.type === 'MDY'){
            this.calendarDate.setMonth(this.calendarDate.getMonth() + int);
        }
        else {
            this.calendarDate.setFullYear(this.calendarDate.getFullYear() + int);
        }
        this.updateCalendar()
    }
    updateCalendar(){
        this.updateCalendarTitle()

        let currentDateObject
        let localDateObject
        if (this.type === 'MDY'){
            this.calendarDaysObject.innerHTML = ''
            this.updateCalendarPrevMonthDays()
            this.updateCalendarCurrentMonthDays()
            this.updateCalendarNextMonthDays()

            Array.from(this.calendarDaysObject.getElementsByTagName('li')).forEach((item, index) => {
                const dayOfWeek = (index + 1) % 7
                if (dayOfWeek === 6 || dayOfWeek === 0){
                    const linkObject = item.getElementsByTagName('a')[0]
                    if (linkObject.classList.contains('secondary-text')) {
                        linkObject.style.color = '#aa5f5f'
                    }
                    else {
                        linkObject.style.color = '#b30707'
                    }
                }
            })

            currentDateObject = document.querySelector('[data-date="' + dateToUS(this.currentDate) + '"]');
            localDateObject = document.querySelector('[data-date="' + dateToUS(this.localDate) + '"]');
        }
        else {
            this.calendarMonthObject.innerHTML = ''
            this.updateMonths()

            currentDateObject = document.querySelector('[data-month="' + this.ifLessThan10Add0(this.currentDate.getMonth() + 1) + '-' + this.currentDate.getFullYear() + '"]');
            localDateObject = document.querySelector('[data-month="' + this.ifLessThan10Add0(this.localDate.getMonth() + 1) + '-' + this.localDate.getFullYear() + '"]');
        }
        if (currentDateObject){
            const linkObject = currentDateObject.getElementsByTagName('a')[0]
            linkObject.style.border = '1px solid #da5b5b'
            linkObject.style.backgroundColor = 'rgba(136, 8, 8, 0.1)'
        }
        if (localDateObject){
            const linkObject = localDateObject.getElementsByTagName('a')[0]
            linkObject.classList.replace('background-theme', 'background-theme-darker')
        }
    }
    updateMonths(){
        const monthArray = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
        monthArray.forEach((item, index) => {
            let schoolMonth = true;
            if (["June", "July", "August"].includes(item)) {
                schoolMonth = false;
            }
            this.calendarMonthObject.appendChild(this.createCalendarMonthObject(item, index + 1, this.calendarDate.getFullYear(), schoolMonth))
        })
    }
    updateCalendarTitle(){
        let value
        if (this.type === 'MDY'){
            value = dateToMonthYear(new Date(this.calendarDate.getFullYear(), this.calendarDate.getMonth() + 1, 0))
        }
        else {
            value = this.calendarDate.getFullYear()
        }
        const title = document.getElementById('calendar-month-year').innerText = value;
    }
    updateCalendarPrevMonthDays(){
        const prevMonthDateObject = clone(this.calendarDate);
        prevMonthDateObject.setMonth(this.calendarDate.getMonth() - 1);

        const previousMonthDays = getMonthDays(prevMonthDateObject.getMonth(), prevMonthDateObject.getFullYear())
        let firstWeekDay = getFirstWeekDay(this.calendarDate.getMonth(), this.calendarDate.getFullYear())
        if (firstWeekDay === 0){
            firstWeekDay = 7
        }

        for (let i = 1; i < firstWeekDay; i++) {
            this.calendarDaysObject.appendChild(this.createCalendarDateObject(
                previousMonthDays - firstWeekDay + i + 1,
                prevMonthDateObject.getMonth() + 1,
                prevMonthDateObject.getFullYear(),
                false));
        }
    }
    updateCalendarCurrentMonthDays(){
        const monthDays = getMonthDays(this.calendarDate.getMonth(), this.calendarDate.getFullYear())
        for (let i = 1; i <= monthDays; i++) {
            this.calendarDaysObject.appendChild(this.createCalendarDateObject(i));
        }
    }
    updateCalendarNextMonthDays(){
        const nextMonthDateObject = clone(this.calendarDate);
        nextMonthDateObject.setMonth(nextMonthDateObject.getMonth() + 1);

        const leftDays = 42 - Array.from(this.calendarDaysObject.getElementsByTagName('li')).length
        for (let i = 1; i <= leftDays; i++){
            this.calendarDaysObject.appendChild(this.createCalendarDateObject(
                i,
                nextMonthDateObject.getMonth() + 1,
                nextMonthDateObject.getFullYear(),
                false
            ));
        }
    }
    createCalendarDateObject(date, month = null, year = null, currentMonth = true){
        if (month === null) {
            month = this.calendarDate.getMonth() + 1
        }
        if (year === null) {
            year = this.calendarDate.getFullYear()
        }
        const dayObject = document.createElement('li');
        const dayObjectLink = this.createDefaultLinkObject(date, currentMonth)
        dayObject.setAttribute('data-date', `${this.ifLessThan10Add0(month)}-${this.ifLessThan10Add0(date)}-${year}`);
        dayObjectLink.href = this.path + (this.ifLessThan10Add0(month) + '-' +  this.ifLessThan10Add0(date) + '-' + year);
        dayObject.appendChild(dayObjectLink)
        return dayObject;
    }
    createCalendarMonthObject(name, month, year, schoolMonth = false){
        const dayObject = document.createElement('li');
        const monthObjectLink = this.createDefaultLinkObject(name, schoolMonth);
        dayObject.setAttribute('data-month', `${this.ifLessThan10Add0(month)}-${year}`);
        monthObjectLink.href = this.path + (this.ifLessThan10Add0(month) + '-' + year);
        dayObject.appendChild(monthObjectLink)
        return dayObject;
    }
    createDefaultLinkObject(text, currentMonth = true){
        const dayObjectLink = document.createElement('a');
        dayObjectLink.innerText = text
        dayObjectLink.classList.add('selectable')
        dayObjectLink.classList.add('background-theme');
        if (currentMonth !== true) {
            dayObjectLink.classList.add('secondary-text');
        }
        return dayObjectLink;
    }
}


document.addEventListener("DOMContentLoaded", function() {
    const topBarDate = document.getElementById('date-bar')
    if (topBarDate) {
        const DateBarClass = new DateBar(currentDate, date);

        DateBarClass.updateCalendar()
        DateBarClass.findDates()
        DateBarClass.updateButtons()
        DateBarClass.updateDateInfo()

        const comeBackButton = document.getElementById('comeback-button')
        if (DateBarClass.type === 'MDY'){
            document.getElementById('week-column').hidden = false;
            comeBackButton.href = DateBarClass.path + dateToUS(currentDate);
            comeBackButton.innerText = 'Today';
        }
        else {
            document.getElementById('calendar-months').hidden = false;
            comeBackButton.href = DateBarClass.path + dateToUS(currentDate, true);
            comeBackButton.innerText = 'This Month';
        }

        document.getElementById('next-calendar').addEventListener('click', (e) => {
            DateBarClass.changeCalendar(1);
        })
        document.getElementById('prev-calendar').addEventListener('click', (e) => {
            DateBarClass.changeCalendar(-1);
        })

        const dateInput = document.getElementById('date-local');
        const calendar = document.getElementById('calendar-object');

        dateInput.addEventListener('click', (e) => {
            // Toggle calendar visibility on input click
            calendar.hidden = !calendar.hidden;
            e.stopPropagation(); // Prevent the event from bubbling to the document
        });

        document.addEventListener('click', (e) => {
            // If click is outside both the input and the calendar, hide it
            if (!calendar.contains(e.target) && !dateInput.contains(e.target)) {
                calendar.hidden = true;
            }
        });
    }
})


function calculateTimeForEventString(localDateString, time_start, time_end){
    let localDate = new Date(localDateString.replace('-', '/'))

    const dateFormatter = new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });


    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour12: true,
        hour: 'numeric',
        minute: '2-digit',
    }) // Will give you "AM" or "PM"



    let timeString  = '<span>'
    const time_now_object = new Date();
    let time_end_object = null;
    let time_start_object = new Date(time_start);
    if (time_end === ''){
        if (time_start_object.getDate() === localDate.getDate()){
            timeString = timeFormatter.format(time_start_object);
        }
        else {
            timeString = time_start_object.toLocaleTimeString()
        }
    }
    else {
        time_end_object = new Date(time_end);
        if (time_start_object.toDateString() === localDate.toDateString() && time_end_object.toDateString() === localDate.toDateString()){
            timeString = timeFormatter.format(time_start_object) + ' - ' + timeFormatter.format(time_end_object);
        }
        else {
            if (isMidnight(time_start_object) && isMidnight(time_end_object)){
                timeString =
                    dateFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object)
            }
            else {
                timeString =
                    dateFormatter.format(time_start_object) +
                    ', ' +
                    timeFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object) +
                    ', ' +
                    timeFormatter.format(time_end_object)
            }
        }
    }
    if (time_start_object.getTime() < time_now_object.getTime() && time_now_object.getTime() < time_end_object.getTime()){
        timeString += '</span><span class="now-sign golden-5">&bull; now</span>'
    }
    timeString += '</span>'

    return timeString
}

function calculateTimeForEventObject(timeID, time_start, time_end) {
    const timeObject = document.getElementById(timeID);

    const dateFormatter = new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });


    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour12: true,
        hour: 'numeric',
        minute: '2-digit',
    }) // Will give you "AM" or "PM"



    let timeString
    const time_now_object = new Date();
    let time_end_object = null;
    let time_start_object = new Date(time_start);
    if (time_end === ''){
        if (time_start_object.getDate() === localDate.getDate()){
            timeString = timeFormatter.format(time_start_object);
        }
        else {
            timeString = time_start_object.toLocaleTimeString()
        }
    }
    else {
        time_end_object = new Date(time_end);
        if (time_start_object.toDateString() === localDate.toDateString() && time_end_object.toDateString() === localDate.toDateString()){
            timeString = timeFormatter.format(time_start_object) + ' - ' + timeFormatter.format(time_end_object);
        }
        else {
            if (isMidnight(time_start_object) && isMidnight(time_end_object)){
                timeString =
                    dateFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object)
            }
            else {
                timeString =
                    dateFormatter.format(time_start_object) +
                    ', ' +
                    timeFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object) +
                    ', ' +
                    timeFormatter.format(time_end_object)
            }
        }
    }

    const timeSpan = document.createElement('span');
    timeSpan.innerText = timeString
    timeObject.appendChild(timeSpan);
    if (time_start_object.getTime() < time_now_object.getTime() && time_now_object.getTime() < time_end_object.getTime()){
        const nowSign = document.createElement('span');
        nowSign.innerHTML = '&bull; now'
        nowSign.classList.add('now-sign', 'golden-5')
        timeObject.appendChild(nowSign);
    }
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
function daysToStatus(daysStart, daysEnd, dateStart, dateEnd) {
    if (daysStart < 0 && daysEnd < 0) {
        return 'Ended'
    }
    if (daysEnd === 0) {
        return 'Ends today'
    }
    if (daysStart === 1) {
        return 'Start tomorrow';
    }
    if (daysEnd === 1) {
        return 'Ends tomorrow'
    }
    if (daysEnd < 0 && daysStart > 0) {
        return 'Starts in ' + daysStart + ' days';
    }
    if (daysStart < 0 && daysEnd > 0) {
        return 'End in ' + daysEnd + ' days';
    }
    return 'Starts in ' + daysStart + ' days';
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
    const parts = date.split('-');
    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    let day
    if (parts.length === 3){
        day = parseInt(parts[2], 10);
    }
    else {
        day = 1;
    }
    return new Date(year, month, day);
}
function dateToFullString(date){
    const full = new Intl.DateTimeFormat('en-US', {
        dateStyle: 'full'
    })
    return full.format(date);
}
function dateToUS(date, noDay = false){
    let month
    let day
    let year
    if (typeof date === 'string'){
        const dateArray = date.split('-');
        month = dateArray[1]
        day = dateArray[2]
        year = dateArray[0]
    }
    else {
        month = date.getMonth() + 1
        day = date.getDate()
        year = date.getFullYear()
    }
    if (noDay === true){
        return  ifLessThan10Add0(month) + '-' + year
    }
    return ifLessThan10Add0(month) + '-' + ifLessThan10Add0(day) + '-' + year
}
function dateToMonthYear(date){
    const monthYear = new Intl.DateTimeFormat('en-US', {
        month: 'long',
        year: 'numeric'
    })
    return monthYear.format(date);
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


function getUserTheme (){
    const prefersDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;
    if (prefersDarkMode) {
        return "dark";
    } else {
        return "light";
    }
}

function fetchServer(title, content, type = 'fetch') {
    let url = '/services/'
    if (type === 'fetch') {
        url += 'server-fetch'
    }
    else if (type === 'request') {
        url += 'server-request'
    }

    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            title: title,
            content: content
        })
    })
        .then(response => {
            return response.json();
        })
}

function getTimeFromMidnight() {
    const now = new Date();
    const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0); // Set to midnight
    return (now.getTime() - midnight.getTime()) / 1000;
}

function isMidnight(date) {
    return date.getHours() === 0 &&
           date.getMinutes() === 0 &&
           date.getSeconds() === 0;
}

function ucwords (str) {
    let stringArray = []
    str.split(' ').forEach(word => {
        stringArray.push(word[0].toUpperCase() + word.substring(1));
    })
    return stringArray.join(' ')
}



function test(days){
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

function sameDay(d1, d2) {
    return d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate();
}

function isEmpty(obj) {
    for (const prop in obj) {
        if (Object.hasOwn(obj, prop)) {
            return false;
        }
    }

    return true;
}

function addTransitionToElement(element, time = 0.3){
    element.style.transition = time + 's'
}
function getMonthDays(month, year, date = null){
    if (date !== null){
        month = date.getMonth()
        year = date.getFullYear()
    }
    month ++
    return new Date(year, month, 0).getDate();
}

function getFirstWeekDay(month, year, date = null){
    if (date !== null){
        month = date.getMonth()
        year = date.getFullYear()
    }
    console.log(year, month, 1)
    return new Date(year, month, 1).getDay();
}

function clone(date){
    return new Date(date.getTime())
}

function ifLessThan10Add0(int){
    int = parseInt(int)
    if (int < 10) {
        return '0' + int;
    }
    return int;
}

function strBooleanToBoolean(str){
    return str === 'true';
}

function secondsToTimeStr(seconds){
    const leftMinutes = Math.floor(seconds / 60);
    const leftSeconds = seconds - (leftMinutes * 60);
    return leftMinutes + ':' + ifLessThan10Add0(leftSeconds);
}

function isWeekEnd(year, month, day) {
    // JavaScript months are 0-indexed (January is 0)
    const date = new Date(year, month - 1, day);
    const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
    return dayOfWeek === 0 || dayOfWeek === 6;
}

function gradeToName(int){
    switch (int){
        case 9:
            return 'freshman'
        case 10:
            return 'sophomore'
        case 11:
            return 'junior'
        case 12:
            return 'senior'
        default:
            return null
    }
}

function getCapitalizedFirstLetter(str) {
    if (!str) return '';
    return str[0].toUpperCase();
}

function generateToken(){
    return Math.random().toString(36).substr(2); // remove `0.`
}

function gradeIntToClass(grade){
    switch (grade) {
        case 9:
            return 'freshman'
        case 10:
            return 'sophomore'
        case 11:
            return 'junior'
        case 12:
            return 'senior'
        default:
            return grade
    }
}

function gradeIntToStr(grade, plural) {
    if (plural === true){
        switch (grade) {
            case 9:
                return 'Freshmen'
            case 10:
                return 'Sophomores'
            case 11:
                return 'Juniors'
            case 12:
                return 'Seniors'
            default:
                return grade
        }
    }
    else {
        switch (grade) {
            case 9:
                return 'Freshman'
            case 10:
                return 'Sophomore'
            case 11:
                return 'Junior'
            case 12:
                return 'Senior'
            default:
                return grade
        }
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return new Uint8Array([...rawData].map((char) => char.charCodeAt(0)));
}


function isAppleDevice() {
    const userAgent = navigator.userAgent;
    const iOS = !window.MSStream && /iPad|iPhone|iPod/.test(userAgent);
    return iOS;
}

function isSafari() {
    const ua = navigator.userAgent;
    return /^((?!chrome|android).)*safari/i.test(ua);
}


function setPermaCookie(name, value) {
    const days = 365 * 20; // 20 years
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + encodeURIComponent(value) + "; " + expires + "; path=/";
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof isLoggedIn !== 'undefined'){
        if (isLoggedIn === 1) {
            fetchServer('keepMyNotifAlive', null)
        }
    }
})
function switchNotificationToken(toggleID) {
    const instructionObject = document.getElementById('apple-notification-instruction')
    if (document.getElementById('toggle-' + toggleID).checked) {
        const toggle = document.getElementById('toggle-' + toggleID)
        const notifErrorObject = document.getElementById('alert-notificationsPermissionIssue')

        if(!('Notification' in window)){
            toggle.checked = false;
            notifErrorObject.style.display = 'flex';
            notifErrorObject.innerHTML = '<p>To turn on notification on phone, use a guide below to turn on PWA</p>'
        }
        else {
            Notification.requestPermission()
                .then((permission) => {
                    if (permission === 'granted') {
                        navigator.serviceWorker.ready.then((sw) => {
                            sw.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: "BPmY3aRTv3WMzUk87saQ_ecjhzlhKq8FtdX9j-_dJuwCnePGRgXSBRlhgEnmgNTUvgKF6Xoo5zqcd5ZSPXD3Bt8"
                            }).then(async (subscription) => {
                                if ((await fetchServer('notificationToken', JSON.stringify(subscription))).success === true) {
                                    Array.from(document.getElementsByClassName('notification-setting-hidden')).forEach((element) => {
                                        element.classList.remove('notification-setting-hidden')
                                        element.classList.add('notification-setting')
                                    });
                                    instructionObject.hidden = true
                                }
                            });
                        });
                    } else {
                        document.getElementById('toggle-' + toggleID).checked = false
                        notifErrorObject.style.display = 'flex';
                        notifErrorObject.innerHTML = '<p>Please allow Notification permission in website settings in order to use it. <br>(School Chromebooks are NOT supported)</p>'
                    }
                })
        }
    } else {
        fetchServer('notificationToken', null);
        Array.from(document.getElementsByClassName('notification-setting')).forEach((element) => {
            element.classList.remove('notification-setting')
            element.classList.add('notification-setting-hidden')
        });
        if (isSafari()){
            instructionObject.hidden = false
        }
    }
}

async function switchAccountParam(toggleID) {
    const object = document.getElementById('toggle-' + toggleID)
    console.log(toggleID)
    if (object.type === 'time') {
        console.log(await fetchServer(toggleID, object.value, 'fetch'))
    } else {
        if (object.checked) {
            console.log(await fetchServer(toggleID, 1, 'fetch'));
        } else {
            console.log(await fetchServer(toggleID, 0, 'fetch'));
        }
    }
}


document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('apple-notification-instruction')){
        if (isSafari()){
            document.getElementById('apple-notification-instruction').hidden = false
        }
    }
});
addEventListener("DOMContentLoaded", () => {
    var {
        OverlayScrollbars,
        ScrollbarsHidingPlugin,
        SizeObserverPlugin,
        ClickScrollPlugin
    } = OverlayScrollbarsGlobal;

    const scrollElements = ['target']
    scrollElements.forEach(scrollElements => {
        if (document.getElementById(scrollElements)){
            OverlayScrollbars(document.querySelector(`#${scrollElements}`), {
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
    })
})

addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('schedules-page')){
        const hash = window.location.hash
        if (hash !== ''){
            const targetObject = document.getElementById('schedule-' + hash.slice(1))
            if (targetObject){
                const title = targetObject.getElementsByClassName('table-title')[0]
                targetObject.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                const previousColor = title.style.color
                targetObject.classList.remove('border-color-theme')
                targetObject.style.borderColor = '#FF0000'
                title.style.color = '#FF0000'
                setTimeout(()=>{
                    targetObject.style.transition = 'border 3s'
                    title.style.transition = 'color 3s'
                    targetObject.classList.add('border-color-theme')
                    title.style.color = previousColor
                }, 2000)
            }
        }
    }
})

function setTheme(theme) {
    if (getCookieValue('theme') === theme){
        return;
    }

    const themes = ['dark', 'light', 'system'];
    const themesNotUsed = themes.filter(el => el !== theme);


    const currentThemeIndicator = document.getElementById(theme + "-theme-switcher");
    const currentThemeIcon = document.getElementById(theme + "-theme-switcher-icon");
    currentThemeIndicator.classList.add('selected-theme')
    currentThemeIcon.src = currentThemeIcon.src.replace(".svg", "-fill.svg")


    themesNotUsed.forEach(themeNotUsed => {
        const localThemeIndicator = document.getElementById(themeNotUsed + "-theme-switcher")
        const localThemeIcon = document.getElementById(themeNotUsed + "-theme-switcher-icon")

        localThemeIndicator.classList.remove('selected-theme')
        if (localThemeIcon.src.includes('-fill.svg')) {
            localThemeIcon.src = (localThemeIcon.src).replace("-fill", '');
        }
    })


    setPermaCookie('theme', theme)

    if (['dark', 'light'].includes(theme)) {
        document.getElementById("theme").href = '/frontend/dist/css/theme-' + theme + '.min.css';
    }
    else {
        document.getElementById("theme").href = '/frontend/dist/css/theme-' + getUserTheme() + '.min.css';
    }
}

function filterNames(periodID, faculty, clear = false){
    function formatText(str){
        return str.replace(/[\s.]/g, '').toLowerCase()
    }
    const teacherInputObject = document.getElementById('teacher-input-period-' + periodID)
    if (clear){
        teacherInputObject.value = ''
    }
    const teacherInputValue = formatText(teacherInputObject.value);

    let filteredFaculty;
    if (teacherInputValue === ''){
        filteredFaculty = faculty;
    }
    else {
        filteredFaculty = faculty.filter(function(object){
            let shortName = object['short_name']
            shortName = (shortName == null) ? '' : formatText(shortName)
            let firstName = object['first_name']
            firstName = (firstName == null) ? '' : firstName.toLowerCase()
            let lastName = object['last_name']
            lastName = (lastName == null) ? '' : lastName.toLowerCase()
            let fullName = formatText(firstName + lastName);

            let room = (object['room'] === null) ? '' : object['room']


            return shortName.includes(teacherInputValue) ||
                lastName.includes(teacherInputValue) ||
                fullName.includes(teacherInputValue) ||
                ((firstName !== null) ? firstName.includes(teacherInputValue) : false) ||
                ((room !== null) ? room.includes(teacherInputValue) : false)
        })
    }

    const listObject = document.getElementById('teacher-list-period-' + periodID);
    listObject.innerHTML = '';
    if (filteredFaculty.length > 0) {
        filteredFaculty.forEach(function(teacherObject) {
            const room = teacherObject['room'];
            const verified = (teacherObject['verified'] === 1) ? true : false;
            const teacherLiObject = document.createElement('li');
            teacherLiObject.classList.add('selectable-no-delay')
            const teacherNameSpanObject = document.createElement('span');
            teacherNameSpanObject.innerText = teacherObject['short_name'];
            teacherLiObject.appendChild(teacherNameSpanObject);

            if (room !== null) {
                const roomSpanObject = document.createElement('span');
                roomSpanObject.classList.add('room-number', 'secondary-text');
                roomSpanObject.innerText = room;
                teacherLiObject.appendChild(roomSpanObject);
            }
            if (verified !== true){
                teacherNameSpanObject.style.color = 'orange';
            }

            teacherLiObject.addEventListener("click", function() {
                fetchServer(formatFetchTitle(periodID), teacherObject['teacher_id'])
                teacherInputObject.innerHTML = ''
                userFacultyList[periodID] = teacherObject['short_name']
                // teacherInputObject.value = teacherObject['short_name'];
                document.getElementById('teacher-list-holder-period-' + periodID).style.display = 'none'
                setTimeout(() => {
                    document.getElementById('cross-holder-period-' + periodID).hidden = false;
                }, 300)
            });
            listObject.appendChild(teacherLiObject);
        });
    }
    else {
        const emptyNotifier = document.createElement('span');
        emptyNotifier.innerText = 'Nobody matches yours request';
        emptyNotifier.classList.add('secondary-text');
        emptyNotifier.classList.add('nobody-found');
        listObject.appendChild(emptyNotifier);
    }
}

addEventListener("DOMContentLoaded", () => {
    if  (document.getElementById('settings-page') !== null){
        const tabCounterSwitcher = document.getElementById('toggle-tabCounter')
        if (tabCounterSwitcher) {
            tabCounterSwitcher.addEventListener('change', function(event) {
                if (event.target.checked) {
                    setPermaCookie('tabCounter', 'true')
                } else {
                    setPermaCookie('tabCounter', 'false')
                }
            });
        }

        const overlayOnSleep = document.getElementById('toggle-overlayOnSleep')
        if (overlayOnSleep) {
            overlayOnSleep.addEventListener('change', function(event) {
                if (event.target.checked) {
                    setPermaCookie('overlayOnSleep', 'true')
                    if (countdown) countdown.setSleepMode(true)
                } else {
                    setPermaCookie('overlayOnSleep', 'false')
                    if (countdown) countdown.setSleepMode(false)
                }
            });
        }


        if (typeof periodsList !== 'undefined'){
            periodsList.forEach(period => {
                const inputObject = document.getElementById('teacher-input-period-' + period)
                const teacherListObject = document.getElementById('teacher-list-holder-period-' + period)

                inputObject.addEventListener("focus", () => {
                    teacherListObject.style.display = "block";
                });

                inputObject.addEventListener("blur", () => {
                    setTimeout(() => {
                        inputObject.value = userFacultyList[period]
                        teacherListObject.style.display = "none"
                    }, 200)
                });
            })
        }

        const logoutButton = document.getElementById('logout-button')
        if (logoutButton) {
            logoutButton.addEventListener('mousedown', () => {
                logoutButton.style.backgroundColor = 'rgba(255, 0, 0, 0.3)'
            })
            logoutButton.addEventListener('mouseup', () => {
                logoutButton.removeAttribute('style');
            })
            logoutButton.addEventListener('click', () => logOutButton(logoutButton))
            logoutButton.addEventListener('mouseleave', () => resetLogOutButton(logoutButton))
        }
    }
})

function removeTeacher(periodID){
    fetchServer(formatFetchTitle(periodID), null)
    userFacultyList[periodID] = null
    document.getElementById('teacher-input-period-' + periodID).value = ''
    document.getElementById('cross-holder-period-' + periodID).hidden = true
}

function formatFetchTitle(periodID){
    let fetchTitle = 'account'
    if (isNaN(parseInt(periodID))) {
        fetchTitle += (periodID === 'hr') ? 'Homeroom' : ucwords(periodID)
    }
    else {
        fetchTitle += ('Period' + periodID)
    }
    return fetchTitle
}

function switchInactiveTimeMinutes (switchInactiveTimeMinutes, minutesMin, minutesMax) {
    const inputObject = document.getElementById(switchInactiveTimeMinutes)
    const minutes = inputObject.value
    let value
    if (minutes > minutesMax) {
        value = minutesMax
        inputObject.value = minutesMax
    }
    else if (minutes < minutesMin) {
        value = minutesMin
        inputObject.value = minutesMin
    }
    else {
        value = minutes
    }

    setPermaCookie('overlayOnSleepTime', value)
    if (countdown){
        countdown.setSecondsUntilSleepMode(value)
    }
}

function logOutButton(element){
    if (element.dataset.usersure === '0') {
        element.innerText = 'Are You Sure?'

        element.style.WebkitBoxShadow = '0px 0px 20px 1px rgba(255,0,0,1)';
        element.style.MozBoxShadow = '0px 0px 20px 1px rgba(255,0,0,1)';
        element.style.boxShadow = '0px 0px 20px 1px rgba(255,0,0,1)';

        element.dataset.usersure = '1'
    }
    else {
        fetchServer('logOut', null)
        window.location.href = '/'
    }
}

function resetLogOutButton(element){
    element.innerText = 'Log Out'

    element.dataset.usersure = '0'

    element.removeAttribute('style');
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
    setPermaCookie('sideBar', status)
    console.log(getCookieValue('sideBar') );

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
    const websiteLogoSection = document.getElementById('website-logo-section')

    // const websiteLogoDivider = document.getElementById('website-logo-divider')
    // const websiteLogoDividerLine = document.getElementById('website-logo-divider-line')
    // const websiteTitle = document.getElementById('website-title')
    //
    // const content = document.getElementById('screen');

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
    changeStatus(websiteLogoSection, 'logo-section', status)

    // changeStatus(websiteLogoDivider, 'website-divider-holder', status)
    // changeStatus(websiteLogoDividerLine, 'website-divider', status)
    // changeStatus(websiteTitle, 'logoTitle', status)
    // changeStatus(content, 'content', status)


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

function turnBar(x){
    x.classList.toggle("turn-bar");
    if (document.getElementById("side-bar").offsetLeft === -210){
        document.getElementById("side-bar").style.left = '0px'
    }
    else {
        document.getElementById("side-bar").style.left = '-210px'
    }
}