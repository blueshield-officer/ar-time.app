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


console.log(data)

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
        if (Array.isArray(data.bells)) {
            this.position = position;
            this.period = period;
            this.status = status;

            this.tableRows = document.getElementsByClassName('table-row');
            if (this.status === 0) {
                this.lastUNIX = null
                this.nextUNIX = this.bells[position].start_unix
                this.statusText = 'starts in'
            } else {
                this.lastUNIX = this.bells[position].start_unix
                this.nextUNIX = this.bells[position].end_unix
                this.statusText = 'ends in'
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
            element.textContent = this.bells[this.position].end_time
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

            document.getElementById('countdown-progress-time').innerText = this.bells[this.position].end_time
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
            (data.testDay === null ? '' : '<p class="day">' + '<strong>' + data.testDay + '</strong>' + ' Day' + '</p>');
    }

    #scheduleTable() {
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

        } else {
            for (let i = 0; i <= position; i++) {
                this.tableRows[i].style.backgroundColor = this.colorList.green.backgroundColor;
                this.tableRows[i].style.color = this.colorList.green.color;
            }
            if (status === 0) {
                this.tableRows[this.position].style.backgroundColor = this.colorList.yellow.backgroundColor;
                this.tableRows[this.position].style.color = this.colorList.yellow.color;
            } else {
                this.tableRows[this.position].style.backgroundColor = this.colorList.red.backgroundColor;
                this.tableRows[this.position].style.color = this.colorList.red.color;
            }
        }
    }

    scheduleWarningBell() {
        if (this.tableRows[this.position - 1].style.backgroundColor !== 'rgb(255, 165, 0)') {
            const timeUntilStart = UNIX.day() + this.bells[this.position].start_unix - UNIX.now()
            if (timeUntilStart <= 300) {
                const row = this.position - 1
                this.tableRows[row].style.backgroundColor = this.colorList.orange.backgroundColor;
                this.tableRows[row].style.color = this.colorList.orange.color;

                console.log('done')
            }
        }
    }

    #scheduleTableUpdate() {
        if (this.status === 0) {
            this.tableRows[this.position - 1].style.backgroundColor = this.colorList.green.backgroundColor;
            this.tableRows[this.position - 1].style.color = this.colorList.green.color;

            this.tableRows[this.position].style.backgroundColor = this.colorList.yellow.backgroundColor;
            this.tableRows[this.position].style.color = this.colorList.yellow.color;
        } else {
            this.tableRows[this.position].style.backgroundColor = this.colorList.red.backgroundColor;
            this.tableRows[this.position].style.color = this.colorList.red.color;
        }
    }

    #alerts() {
        const alerts = data.special
        if (alerts === null) {
        } else {
            const mainHolder = document.getElementById('info-holder')
            alerts.forEach((alert) => {
                console.log(alert)
                //
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
        console.log(updateTime)
        if (UNIX.now() >= updateTime) {
            location.reload()
        }
    }

    progressBarUpdate(percentage) {
        document.getElementById('countdown-progress-bar-filled').style.backgroundColor = 'hsl(' + 0.9 * percentage + ', 100%, 50%)'
    }

    nextPeriod() {
        //  0 => 1, period started
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
            this.lastUNIX = this.bells[this.position].start_unix
            this.nextUNIX = this.bells[this.position].end_unix
            this.status = 1
            this.statusText = 'ends in'

            this.#progressBar()

        }
        // 1 => 0, period ended
        else {
            this.position += 1
            if (typeof this.bells[this.position] === 'undefined') {
                location.reload()
            }
            this.status = 0
            this.statusText = 'starts in'
            this.lastUNIX = null
            this.nextUNIX = this.bells[this.position].start_unix

            this.#progressBar()
        }
        this.nextUNIX += this.UNIX
        this.lastUNIX = this.lastUNIX = null ? null : this.lastUNIX += this.UNIX;
        this.period = this.bells[this.position].name
        this.#labelUpdate()
        this.#scheduleTableUpdate()
        counterUpdater(UNIX, classStatus)
    }
}


function counterUpdater(UNIX, classStatus) {
    if (classStatus.position === 1 && classStatus.status === 0 && classStatus.period === 'School') {
        classStatus.scheduleWarningBell()
    }
    let totalSeconds = classStatus.nextUNIX - UNIX.now();
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
    const convertedTime = UNIX.secondToTime(totalSeconds);
    let myArray = []

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
    classStatus.countdownTime.innerHTML = myArray.join(':')
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
        counterUpdater(UNIX, classStatus)
        setInterval('counterUpdater(UNIX, classStatus)', 1000)
    } else {
        classStatus = new ClassStatus(data, null, null, null, null)
    }
}

//classStatus.nextPeriod()

// particle accelerator
//setInterval('counterUpdater(UNIX, classStatus)', 1)
//setInterval('UNIX.particleAccelerator()', 1)
//UNIX.accelerator =