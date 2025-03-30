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