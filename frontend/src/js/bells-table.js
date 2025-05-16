function findPeriodDimention(bells, position, dayStartUnix) {
    const gap = parseInt(getComputedStyle(document.querySelector('#timestamp-block')).getPropertyValue('gap'))

    let top = null
    let height = null
    const pixelsPerHour = document.getElementsByClassName('time-line')[0].offsetHeight + gap
    const pixelsPerMinute = pixelsPerHour / 60
    const topError = document.getElementsByClassName('time-line')[0].offsetHeight / 2
    let type

    const periodName = bells[position]['name'].toLowerCase()
    if (['announcements', 'warning bell'].includes(periodName)) {
        type = 'label'
    }
    else {
        type = 'default'
    }

    const periodStartUnix = bells[position]['start_unix']
    const periodEndUnix = bells[position]['end_unix']


    if (periodStartUnix !== null) {
        top = (pixelsPerHour * ((periodStartUnix - dayStartUnix) / 60 / 60)) + topError
        if (type === 'label') {
            top = top - ((10 / pixelsPerMinute) / 2) * pixelsPerMinute + 1
        }
    }
    if (periodStartUnix !== null && periodEndUnix !== null) {
        height = pixelsPerHour * ((periodEndUnix - periodStartUnix) / 60 / 60)
    }

    return dimensions = {
        top: top,
        height: height,
        type: type,
    }
}

function setMinWidthOfTitle(){
    const periodBoxArray = document.querySelectorAll('.period-box')
    periodBoxArray.forEach(periodBox => {
        const height = periodBox.offsetHeight
        periodBox.querySelector('.name').style.minWidth = height + 'px'
    })
}

addEventListener("DOMContentLoaded", () => {

    if (document.getElementById('bells-block')){
        const startUnix = startTime * 60 * 60

        // let test = 1
        // setInterval(() => {
        //     document.getElementById('current-time-runner').style.top = test + 'px'
        //     test ++
        // }, 100)


        for (let i = 0; i <  Object.keys(bells).length; i++) {
            const objectData = findPeriodDimention(bells, i, startUnix)
            const periodObject = document.getElementById('period-' + i)
            periodObject.style.top = objectData.top + 'px'
            periodObject.style.height = objectData.height + 'px'
            if (objectData.type === 'label') {
                periodObject.classList.remove('default-box')
                periodObject.classList.add('label-box')
            }
            else if (objectData.height < 30){
                periodObject.classList.add('period-info-hidden')
                periodObject.addEventListener('mouseover', function () {
                    periodObject.style.height = '50px'
                })
                periodObject.addEventListener('mouseout', function (){
                    periodObject.style.height = objectData.height + 'px'
                })
            }
        }

        setMinWidthOfTitle()
    }
});