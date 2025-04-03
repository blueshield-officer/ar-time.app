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


function getUserTheme (){
    const prefersDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;
    if (prefersDarkMode) {
        return "dark";
    } else {
        return "light";
    }
}