function calculateTimeForEventString(localDateString, time_start, time_end){
    let localDate = new Date(localDateString.replace('-', '/'))

    const dateFormatter = new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });


    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour12: true,
        hour: 'numeric',
        minute: '2-digit',
    }) // Will give you "AM" or "PM"



    let timeString  = '<span>'
    const time_now_object = new Date();
    let time_end_object = null;
    let time_start_object = new Date(time_start);
    if (time_end === ''){
        if (time_start_object.getDate() === localDate.getDate()){
            timeString = timeFormatter.format(time_start_object);
        }
        else {
            timeString = time_start_object.toLocaleTimeString()
        }
    }
    else {
        time_end_object = new Date(time_end);
        if (time_start_object.toDateString() === localDate.toDateString() && time_end_object.toDateString() === localDate.toDateString()){
            timeString = timeFormatter.format(time_start_object) + ' - ' + timeFormatter.format(time_end_object);
        }
        else {
            if (isMidnight(time_start_object) && isMidnight(time_end_object)){
                timeString =
                    dateFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object)
            }
            else {
                timeString =
                    dateFormatter.format(time_start_object) +
                    ', ' +
                    timeFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object) +
                    ', ' +
                    timeFormatter.format(time_end_object)
            }
        }
    }
    if (time_start_object.getTime() < time_now_object.getTime() && time_now_object.getTime() < time_end_object.getTime()){
        timeString += '</span><span class="now-sign golden-5">&bull; now</span>'
    }
    timeString += '</span>'

    return timeString
}

function calculateTimeForEventObject(timeID, time_start, time_end) {
    const timeObject = document.getElementById(timeID);

    const dateFormatter = new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });


    const timeFormatter = new Intl.DateTimeFormat('en-US', {
        hour12: true,
        hour: 'numeric',
        minute: '2-digit',
    }) // Will give you "AM" or "PM"



    let timeString
    const time_now_object = new Date();
    let time_end_object = null;
    let time_start_object = new Date(time_start);
    if (time_end === ''){
        if (time_start_object.getDate() === localDate.getDate()){
            timeString = timeFormatter.format(time_start_object);
        }
        else {
            timeString = time_start_object.toLocaleTimeString()
        }
    }
    else {
        time_end_object = new Date(time_end);
        if (time_start_object.toDateString() === localDate.toDateString() && time_end_object.toDateString() === localDate.toDateString()){
            timeString = timeFormatter.format(time_start_object) + ' - ' + timeFormatter.format(time_end_object);
        }
        else {
            if (isMidnight(time_start_object) && isMidnight(time_end_object)){
                timeString =
                    dateFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object)
            }
            else {
                timeString =
                    dateFormatter.format(time_start_object) +
                    ', ' +
                    timeFormatter.format(time_start_object) +
                    ' - ' +
                    dateFormatter.format(time_end_object) +
                    ', ' +
                    timeFormatter.format(time_end_object)
            }
        }
    }

    const timeSpan = document.createElement('span');
    timeSpan.innerText = timeString
    timeObject.appendChild(timeSpan);
    if (time_start_object.getTime() < time_now_object.getTime() && time_now_object.getTime() < time_end_object.getTime()){
        const nowSign = document.createElement('span');
        nowSign.innerHTML = '&bull; now'
        nowSign.classList.add('now-sign', 'golden-5')
        timeObject.appendChild(nowSign);
    }
}