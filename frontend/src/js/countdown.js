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
