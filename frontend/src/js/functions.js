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