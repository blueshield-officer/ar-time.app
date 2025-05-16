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

