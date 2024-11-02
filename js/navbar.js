function liveTimer() {
    let date = new Date();
    let hours = date.getHours()
    let dayTime = hours > 12 ? 'PM' : 'AM';
    hours = hours > 12 ? hours - 12 : hours;
    let minutes = date.getMinutes()
    let seconds = date.getSeconds()

    function timeReturn(number) {
        return number < 10 ? '0' + number : number
    }

    document.getElementById('watch').innerHTML = timeReturn(hours) + ':' + timeReturn(minutes) + ':' + timeReturn(seconds) + ' ' + dayTime
}

liveTimer()
setInterval('liveTimer()', 1000)