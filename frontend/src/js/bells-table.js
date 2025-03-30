function findPeriodDimention(bells, position, dayStartUnix) {
    let top = null
    let height = null
    const pixelsPerHour = document.getElementsByClassName('time-line')[0].offsetHeight + 20
    const topError = document.getElementsByClassName('time-line')[0].offsetHeight / 2



    const periodStartUnix = bells[position]['start_unix']
    const periodEndUnix = bells[position]['end_unix']


    if (periodStartUnix !== null) {
        top = (pixelsPerHour * ((periodStartUnix - dayStartUnix) / 60 / 60)) + topError + 'px'
    }
    if (periodStartUnix !== null && periodEndUnix !== null) {
        height = pixelsPerHour * ((periodEndUnix - periodStartUnix) / 60 / 60) + 'px'
    }

    return {
        top: top,
        height: height,
    }
}

addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('bells-block')){
        const startUnix = startTime * 60 * 60

        let test = 1
        setInterval(() => {
            document.getElementById('current-time-runner').style.top = test + 'px'
            test ++
        }, 100)

        for (let i = 1; i <=  Object.keys(bells).length; i++) {
            const objectData = findPeriodDimention(bells, i, startUnix)
            document.getElementById('period-' + i).style.top = objectData.top
            document.getElementById('period-' + i).style.height = objectData.height
        }
    }
});