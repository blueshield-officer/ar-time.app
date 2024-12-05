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


class ManagerUNIX {
    constructor(data) {
        this.date = data.date
        this.accelerator = 0
    }

    particleAccelerator() {
        this.accelerator += 1
    }

    day() {
        let tempDate = this.date.split('/')
        let date = new Date(tempDate[2], tempDate[0] - 1, tempDate[1])
        date = new Date(date.getFullYear(), date.getMonth(), date.getDate())
        return Math.floor(date.valueOf() / 1000)
    }

    now() {
        return Math.floor(Date.now() / 1000) + this.accelerator
    }

    secondToTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainingSeconds = seconds % 60;

        return {
            hours,
            minutes,
            seconds: remainingSeconds
        };
    }
}

class ClassStatus {
    constructor(data, position, period, status, unix) {
        this.bells = data.bells;
        this.UNIX = unix;
        this.loadTimeOut = 700
        this.tempAnnouncStatus = 1
        if (Array.isArray(data.bells)) {
            this.position = position;
            this.period = period;
            this.status = status;

            this.tableRows = document.getElementsByClassName('table-row');
            if (this.status === 0) {
                this.lastUNIX = null
                this.nextUNIX = this.bells[this.position].start_unix
                this.statusText = 'starts in'
                this.nextRoomChanges = 1
            } else {
                // announc
                if (this.period === 'announcements'){
                    this.period = this.bells[this.position - 1].name
                    this.nextUNIX = this.bells[this.position].end_unix
                    this.position = this.position - 1
                }
                else if (this.bells[this.position + 1] !== undefined && this.bells[this.position + 1].name === 'announcements') {
                    this.nextUNIX = this.bells[this.position + 1].end_unix
                }
                else {
                    this.nextUNIX = this.bells[this.position].end_unix
                }
                this.lastUNIX = this.bells[this.position].start_unix
                this.statusText = 'ends in'
                this.nextRoomChanges = 0
            }
            // Changing homeroom to school
            if (this.position === 0 && this.status === 0) {
                this.period = 'school';
            }
            else if (this.position === 1 && this.status === 0 && this.bells[this.position-1].name === 'warning bell') {
                this.period = 'school';
            }


            this.lastUNIX = this.lastUNIX = null ? null : this.lastUNIX += this.UNIX;
            this.nextUNIX += this.UNIX

            this.countdownTime = document.getElementById('countdown-time');
            this.countdownLabel = document.getElementById('countdown-label');

            //height fixer
            document.getElementById('countdown-holder').style.minHeight = '212px';

            // important function loaders
            this.#progressBar()
            this.#scheduleTable()
            this.#labelUpdate()
            this.colorRoomChanges(2)
        } else {
            document.getElementById('countdown-time').innerText = 'No Data'
            // 2:20 updater
            setInterval(this.#specialAutoUpdate, 1000)
        }
        this.#boxInfo()
        document.addEventListener("DOMContentLoaded", this.#alerts());
    }

    #console() {
        return {
            position: this.position,
            period: this.period,
            status: this.status,
            statusText: this.statusText,
            nextUNIX: this.nextUNIX,
            lastUNIX: this.lastUNIX,
            UNIX: this.UNIX,
        }
    }
    secondsToTime(seconds){
        let myArray = []
        const convertedTime = UNIX.secondToTime(seconds)
        if (convertedTime.hours > 0) {
            myArray.push(convertedTime.hours)
        }
        if (convertedTime.minutes > 0 || convertedTime.hours > 0) {
            if (convertedTime.minutes < 10 && convertedTime.hours > 0) {
                convertedTime.minutes = '0' + convertedTime.minutes
            }
            myArray.push(convertedTime.minutes)
        }
        if (convertedTime.seconds < 10) {
            if (convertedTime.hours > 0 || convertedTime.minutes > 0) {
                convertedTime.seconds = '0' + convertedTime.seconds
            }
        }
        myArray.push(convertedTime.seconds)
        return myArray.join(':')
    }
    colorRoomChanges (status){
        let position = this.position
        if (status === 1){
            position += 1;
            const nextPosition = this.bells[position]
            if (nextPosition === undefined){
                return
            }
            else if (nextPosition.name.toLowerCase() === 'announcements') {
                position += 1;
            }
        }
        const periodName = this.bells[position].name.toLowerCase();
        if (periodName.includes('period')) {
            const period = document.getElementById('period-' + periodName.slice(7, 8))
            function changeColor (bg, color, border){
                period.style.backgroundColor = bg;
                period.style.color = color;
                period.style.borderRightColor = border;
                period.style.borderLeftColor = border;
            }
            if (period !== null) {
                switch (status) {
                    case 0:
                        changeColor('', 'black', 'white')
                        break;
                    case 1:
                        changeColor('#ffde21', 'black', 'black')
                        break;
                    case 2:
                        changeColor('#ed2024', 'white', 'black')
                }
            }
        }
    }
    #progressBar() {
        if (this.status === 1) {
            let element = document.createElement('div');
            element.id = 'countdown-progress-box'
            document.getElementById('countdown').appendChild(element);
            //
            element = document.createElement('div');
            element.id = 'countdown-progress-info'
            document.getElementById('countdown-progress-box').appendChild(element);
            //
            element = document.createElement('span');
            element.id = 'countdown-progress-percentage'
            document.getElementById('countdown-progress-info').appendChild(element);
            //
            element = document.createElement('span');
            element.id = 'countdown-progress-time'
            element.textContent = ''
            document.getElementById('countdown-progress-info').appendChild(element);
            //
            element = document.createElement('div');
            element.id = 'countdown-progress-bar'
            document.getElementById('countdown-progress-box').appendChild(element);
            //
            element = document.createElement('span');
            element.id = 'countdown-progress-bar-filled'
            element.className = 'percentage-color'
            document.getElementById('countdown-progress-bar').appendChild(element);

            let endTime = ''
            const nextName = this.bells[this.position + 1]
            if (nextName !== undefined && this.bells[this.position + 1].name === 'announcements'){
                endTime = this.bells[this.position + 1].end_time
            }
            else {
                endTime = this.bells[this.position].end_time
            }
            document.getElementById('countdown-progress-time').innerText = endTime
        } else {
            const element = document.getElementById('countdown-progress-box')
            if (element !== null) {
                element.remove();
            }
        }
    }

    #labelUpdate() {
        this.period = this.period.charAt(0).toUpperCase() + this.period.slice(1)
        this.countdownLabel.innerHTML = '<strong>' + this.period + '</strong>' + ' ' + this.statusText + ':'
    }


    #boxInfo() {
        // date box
        const date = new Date(data.date)
        const dateBoxID = document.getElementById('date-box')
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        const day = date.getDate();
        const monthName = monthNames[date.getMonth()];
        const weekName = dayNames[date.getDay()];
        const year = date.getFullYear();

        dateBoxID.innerText = weekName + ', ' + day + ' ' + monthName + ', ' + year;


        // schedule box
        const scheduleBoxID = document.getElementById('schedule-box')
        let schedule
        if (Array.isArray(data.bells)) {
            const numericSchedule = parseInt(data.schedule)
            if (Number.isInteger(numericSchedule)) {
                const scheduleName = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'P', 'R', 'T', 'V'];
                schedule = scheduleName[numericSchedule - 1];
            }
        } else {
            schedule = data.schedule
        }
        scheduleBoxID.innerHTML = '<p class="schedule">' + '<strong>' + schedule + '</strong>' + ' ' + 'Schedule' + '</p>' +
            (data.description === null ? '' : '<p class="description">' + data.description + '</p>') +
            (data.testDay === null ? '' : '<p class="day">' + 'Day ' + '<strong>' + data.testDay + '</strong></p>');
    }

    async #scheduleTable() {
        this.colorList = {
            red: {
                color: 'white',
                backgroundColor: '#ed2024',
            },
            yellow: {
                color: 'black',
                backgroundColor: '#ffde21',
            },
            green: {
                color: 'white',
                backgroundColor: '#008000',
            },

            orange: {
                color: 'black',
                backgroundColor: '#FFA500',
            }
        }
        const position = this.position - 1
        const status = this.status
        if (status === 0 && position <= 0) {

        }
        else {
            let timeout = this.loadTimeOut/(position + 3)
            let i = 0
            let interval = setInterval(() => {
                this.tableRows[i].style.backgroundColor = this.colorList.green.backgroundColor;
                this.tableRows[i].style.color = this.colorList.green.color;
                i++
                if (i > position) {
                    setTimeout(() => {
                        if (status === 0) {
                            this.tableRows[this.position].style.backgroundColor = this.colorList.yellow.backgroundColor;
                            this.tableRows[this.position].style.color = this.colorList.yellow.color;
                        } else {
                            this.tableRows[this.position].style.backgroundColor = this.colorList.red.backgroundColor;
                            this.tableRows[this.position].style.color = this.colorList.red.color;
                        }
                    }, timeout)

                    clearInterval(interval)
                }
            } , timeout)
        }
    }

    scheduleWarningBell() {
        if (this.tableRows[this.position - 1].style.backgroundColor !== 'rgb(255, 165, 0)') {
            const timeUntilStart = UNIX.day() + this.bells[this.position].start_unix - UNIX.now()
            if (timeUntilStart <= 300) {
                const row = this.position - 1
                this.tableRows[row].style.backgroundColor = this.colorList.orange.backgroundColor;
                this.tableRows[row].style.color = this.colorList.orange.color;
            }
        }
    }

    scheduleTableUpdate(announcments = null) {
        if (announcments === null){
            if (this.status === 0) {
                if (this.tableRows[this.position - 1].name === 'announcements'){
                    this.tableRows[this.position - 2].style.backgroundColor = this.colorList.green.backgroundColor;
                    this.tableRows[this.position - 2].style.color = this.colorList.green.color;
                }
                this.tableRows[this.position - 1].style.backgroundColor = this.colorList.green.backgroundColor;
                this.tableRows[this.position - 1].style.color = this.colorList.green.color;

                this.tableRows[this.position].style.backgroundColor = this.colorList.yellow.backgroundColor;
                this.tableRows[this.position].style.color = this.colorList.yellow.color;
            }
            else {
                this.tableRows[this.position].style.backgroundColor = this.colorList.red.backgroundColor;
                this.tableRows[this.position].style.color = this.colorList.red.color;
            }
        }
        else {
            const nextPosition = this.position + 1
            this.tableRows[nextPosition].style.backgroundColor = this.colorList.red.backgroundColor;
            this.tableRows[nextPosition].style.color = this.colorList.red.color;
        }
    }

    #alerts() {
        const alerts = data.special
        if (alerts === null) {
        } else {
            const mainHolder = document.getElementById('info-holder')
            alerts.forEach((alert) => {
                let alertHolder = document.createElement('div')
                alertHolder.className = 'alert-block'
                //
                let alertBox = document.createElement('div');
                alertBox.className = 'alert-box'
                alertBox.style.backgroundColor = 'rgba(' + this.#codeToColor(alert.code) + ', 0.65)'
                alertBox.style.backdropFilter = 'blur(10px)';
                //
                let alertLine = document.createElement('div');
                alertLine.className = 'alert-line'
                alertLine.style.backgroundColor = 'rgb(' + this.#codeToColor(alert.code) + ')'
                //
                let text = document.createElement('p');
                text.textContent = alert.text;
                text.className = 'text'
                //
                mainHolder.appendChild(alertHolder);
                alertHolder.appendChild(alertBox);
                alertBox.appendChild(alertLine)
                alertBox.appendChild(text)
            })
        }
    }

    #codeToColor(color) {
        switch (color) {
            case 'gamma':
                return '174, 95, 121';
            case 'red':
                return '154, 33, 35';
            case 'blue':
                return '22, 106, 194';
            case 'yellow':
                return '194, 155, 30';
            case 'green':
                return '6, 123, 9';
        }
    }

    #specialAutoUpdate() {
        const updateTime = UNIX.day() + 51600
        if (UNIX.now() >= updateTime) {
            location.reload()
        }
    }
    progressBarAndCounterFirstUpdate (){
        // calculate data
        const classLength = this.nextUNIX - this.lastUNIX
        let totalSeconds = this.nextUNIX - UNIX.now();

        let percentage
        if (totalSeconds <= 0) {
            percentage = 100
        } else if (totalSeconds > this.nextUNIX - this.lastUNIX) {
            percentage = 0
        } else {
            percentage = 100 - (totalSeconds / (this.nextUNIX - this.lastUNIX) * 100)
        }
        const times = 22
        let intervalTime = (this.loadTimeOut / times )

        const progressBarFilled = document.getElementById('countdown-progress-bar-filled')
        const progressPercentage = document.getElementById('countdown-progress-percentage');

        // on load
        this.countdownTime.innerHTML = this.secondsToTime(classLength)
        progressBarFilled.style.backgroundColor = 'hsl(' + 0.9 * 0 + ', 100%, 50%)'
        progressBarFilled.style.width = 0 + '%'
        progressPercentage.innerText = Math.floor(0) + '%'

        // loop
        let i = 0
        let interval = setInterval(() => {
            const tempPercents = (percentage / 100) * (i*5)
            const leftSecond = Math.round(classLength - ((classLength * tempPercents) / 100))
            this.countdownTime.innerHTML = this.secondsToTime(leftSecond)
            progressBarFilled.style.backgroundColor = 'hsl(' + 0.9 * tempPercents + ', 100%, 50%)'
            progressBarFilled.style.width = tempPercents + '%'
            progressPercentage.innerText = Math.floor(tempPercents) + '%'
            i++
            if (i >= times){
                clearInterval(interval)
                counterUpdater(UNIX, classStatus)
            }
        }, intervalTime)
    }
    progressBarUpdate(percentage) {
        document.getElementById('countdown-progress-bar-filled').style.backgroundColor = 'hsl(' + 0.9 * percentage + ', 100%, 50%)'
    }

    nextPeriod() {
        //  0 => 1, period started
        // Current Period
        if (this.status === 0) {
            // warning bell to green
            const warningBellPosition = this.position - 1
            const warningBell = this.bells[warningBellPosition]
            if (warningBell !== undefined && warningBell.name === 'warning bell') {
                this.tableRows[warningBellPosition].style.backgroundColor = this.colorList.green.backgroundColor;
                this.tableRows[warningBellPosition].style.color = this.colorList.green.color;
            }

            // check on -
            if (this.bells[this.position].end_unix === null) {
                location.reload()
            }
            if (this.bells[this.position + 1] !== undefined && this.bells[this.position + 1].name === 'announcements'){
                this.nextUNIX = this.bells[this.position + 1].end_unix
            }
            else {
                this.nextUNIX = this.bells[this.position].end_unix
            }
            this.lastUNIX = this.bells[this.position].start_unix
            this.status = 1
            this.statusText = 'ends in'

            this.#progressBar()
        }
        // 1 => 0, period ended
        // Next Period
        else {
            this.colorRoomChanges(0)
            this.position += 1
            if (typeof this.bells[this.position] === 'undefined') {
                location.reload()
            }
            this.status = 0
            this.statusText = 'starts in'
            this.lastUNIX = null
            this.nextUNIX = this.bells[this.position].start_unix
            this.#progressBar()
            // room changes table
            this.colorRoomChanges(2)
        }
        this.nextUNIX += this.UNIX
        this.lastUNIX = this.lastUNIX = null ? null : this.lastUNIX += this.UNIX;
        this.period = this.bells[this.position].name

        if (this.period !== 'Announcements' && this.status === 1) {
            this.nextRoomChanges = 0
        }
        this.#labelUpdate()
        this.scheduleTableUpdate()
        counterUpdater(UNIX, classStatus)
    }
}


function counterUpdater(UNIX, classStatus) {
    if (classStatus.position === 1 && classStatus.status === 0 && classStatus.period === 'School') {
        classStatus.scheduleWarningBell()
    }
    let totalSeconds = classStatus.nextUNIX - UNIX.now();
    // yellow room changes if less than 10 minutes
    if (classStatus.nextRoomChanges !== 1 && totalSeconds <= 600){
        classStatus.nextRoomChanges = 1
        classStatus.colorRoomChanges(1)
    }
    // red announcments
    const nextPeriodObject = classStatus.bells[classStatus.position + 1]
    if (nextPeriodObject !== undefined && classStatus.status === 1 && nextPeriodObject.name === 'announcements' && classStatus.tempAnnouncStatus === 1 && totalSeconds < 60){
        classStatus.tempAnnouncStatus = 0
        classStatus.scheduleTableUpdate(1)
    }
    // progress update if status === 1
    if (classStatus.status === 1) {
        const progressBar = document.getElementById('countdown-progress-bar-filled');
        const progressPercentage = document.getElementById('countdown-progress-percentage');
        let percentage = 0
        if (totalSeconds <= 0) {
            percentage = 100
        } else if (totalSeconds > classStatus.nextUNIX - classStatus.lastUNIX) {
            percentage = 0
        } else {
            percentage = 100 - (totalSeconds / (classStatus.nextUNIX - classStatus.lastUNIX) * 100)
        }
        progressBar.style.width = percentage + '%'
        progressPercentage.innerText = Math.floor(percentage) + '%'
        classStatus.progressBarUpdate(percentage)
    }
    if (totalSeconds < 0) {
        classStatus.nextPeriod();
        totalSeconds = classStatus.nextUNIX - UNIX.now()
    }
    classStatus.countdownTime.innerHTML = classStatus.secondsToTime(totalSeconds);
}


let UNIX = new ManagerUNIX(data)
let classStatus
window.onload = function () {
    if (Array.isArray(data.bells)) {
        data.bells.every(function (element) {
            if (element.name === 'warning bell' && element.position === 1) {
                return true
            } else {
                if (UNIX.now() < UNIX.day() + element.start_unix) {
                    //period starts in
                    classStatus = new ClassStatus(data, element.position - 1, element.name, 0, UNIX.day())
                    return false
                } else if (UNIX.now() < UNIX.day() + element.end_unix) {
                    //period ends in
                    classStatus = new ClassStatus(data, element.position - 1, element.name, 1, UNIX.day())
                    return false
                }
                return true
            }
        })
        if (classStatus.status === 1) {
            classStatus.progressBarAndCounterFirstUpdate()
        }
        else {
            counterUpdater(UNIX, classStatus)
        }
        setInterval('counterUpdater(UNIX, classStatus)', 1000)
    } else {
        classStatus = new ClassStatus(data, null, null, null, null)
    }
}

//classStatus.nextPeriod()

// particle accelerator
//setInterval('counterUpdater(UNIX, classStatus)', 1)
//setInterval('UNIX.particleAccelerator()', 1)
//UNIX.accelerator = 230000