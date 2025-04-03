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
            }
            else {
                this.updateTopBarCountdown();
            }
            if (getCookieValue('tabCounter') === 'true'){
                this.updateTitle();
            }
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
    if (typeof currentScheduleTable !== 'undefined') {
        if (currentScheduleTable !== null){
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