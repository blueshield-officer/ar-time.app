class CountdownOld {
    constructor(dayInfo, bells) {
        this.dayInfo = dayInfo;
        this.bells = bells
        this.periodPosition = 0
        this.pageName = document.title;

        this.updateInterval = 1000
    }

    getTime() {
        const seconds = this.findTimeUntilNext()
        if (seconds < 0){
            return '00 : 00'
        }
        else {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            // Format to always have two digits
            const formattedHours = hours > 0 ? hours.toString().padStart(2, '0') + ' : ' : '';
            const formattedMinutes = minutes.toString().padStart(2, '0');
            const formattedSecs = secs.toString().padStart(2, '0');

            return `${formattedHours}${formattedMinutes} : ${formattedSecs}`;
        }
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

                if (this.findTimeUntilNext() < 0) {
                    const dayEnd = this.findCurrentPeriodData()
                    if (dayEnd === false){
                        this.endDay()
                    }
                }
            }
        }, 1000);
    }

    startUpdateInterval(){
        const intervalFunction = () => {
            if (getCookieValue('tabCounter') === 'true'){
                this.updatePageTitle();
            }
            this.updateCounter()
        };
        intervalFunction();
        this.updateInterval = setInterval(intervalFunction, this.updateInterval);
    }

    endDay(){
        window.location.reload();
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

    updateCounter(){
        document.getElementById('countdown-time').innerText = this.getTime();
        if (this.periodStarted === true){
            document.getElementById('countdown-progress-percentage').innerText = this.getPercentage(true) + '%'
                const bar = document.getElementById('countdown-progress-bar-filled')
                bar.style.width = this.getPercentage() + '%';
                bar.style.backgroundColor = this.getColor();
        }
    }

    updatePeriodConstant(){
        // let labelStr = `<strong>${(this.periodName === 'Warning Bell') ? 'School' : this.periodName}</strong> `
        let labelStr = `<strong>${this.periodName}</strong>`
        labelStr += ' '
        labelStr += (this.periodStarted === false) ? 'starts in:' : 'ends in:'
        document.getElementById('countdown-label').innerHTML = labelStr;

        if (this.periodStarted === true){
            document.getElementById('countdown-progress-time').innerText = this.periodEndTime;
            document.getElementById('countdown-progress-box').hidden = false
        }
        else {
            document.getElementById('countdown-progress-box').hidden = true
        }
    }

    getPercentage(floor = false) {
        if (this.periodStarted === false) {
            return 100
        }
        let percentage
        const totalSeconds =  this.bells[this.periodPosition]['end_unix'] - this.bells[this.periodPosition]['start_unix']
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
        return `hsl(${degree} 70% 50%)`;
    }

    updateTablePeriod(periodPosition, status){
        const color = {
            '-2': '(255, 165, 0',
            '-1': '255,255,0',
            '0': '255, 0, 0',
            '1': '0, 255, 0'
        }
        const periodObject = document.getElementById(`period-${periodPosition}`)
        periodObject.style.backgroundColor = `rgba(${color[status]}, 0.3)`
        if (status === 0){
            periodObject.color = 'white'
        }
        else {
            periodObject.color = 'black'
        }
    }

    findCurrentPeriodData() {
        const currentUnixTimestamp = Math.floor( Date.now() / 1000 )
        const midNightUnixTimestamp = Math.floor(YmdDateToDate(this.dayInfo['date']).setHours(0, 0, 0, 0) / 1000 )

        for (let i = this.periodPosition; i < Object.keys(this.bells).length; i++) {
            this.periodName = this.bells[i]['name']
            if (this.bells[i]['start_unix'] !== null){
                if (currentUnixTimestamp < midNightUnixTimestamp + this.bells[i]['start_unix']){
                    this.periodStarted = false
                    this.periodPosition = i
                    this.nextTimeStamp = midNightUnixTimestamp + this.bells[i]['start_unix']
                    this.updatePeriodConstant()
                    if (i !== 0) this.updateTablePeriod(i - 1, 1)
                    this.updateTablePeriod(i, -1)
                    return
                }
            }
            if (this.bells[i]['end_unix'] !== null) {
                if (currentUnixTimestamp < midNightUnixTimestamp + this.bells[i]['end_unix']){
                    this.periodStarted = true
                    this.periodPosition = i
                    this.periodEndTime = this.bells[i]['end_time']
                    this.nextTimeStamp = midNightUnixTimestamp + this.bells[i]['end_unix']
                    this.updatePeriodConstant()
                    this.updateTablePeriod(i, 0)
                    return
                }
            }
            this.updateTablePeriod(i, 1)
        }
        return false
    }
}


addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('legacy-page')) {
        const countdown = new CountdownOld(dayInfo, bells)
        if (countdown.findCurrentPeriodData() === false){
            countdown.endDay()
        }
        else {
            countdown.startSecondsCount()
            countdown.startUpdateInterval()
        }
    }
})