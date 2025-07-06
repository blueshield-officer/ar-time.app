class CalendarBig {
    constructor(currentDate, localDate, localDateGlobal, dayInfo, events, sports, dayOffs, retreats) {
        this.currentDate = clone(currentDate)
        this.localDate = clone(localDate)
        this.localDateGlobal = clone(localDateGlobal)
        this.prevMonthObject = clone(this.localDate)
        this.nextMonthObject = clone(this.localDate)

        this.prevMonthObject.setMonth(this.prevMonthObject.getMonth() - 1)
        this.nextMonthObject.setMonth(this.nextMonthObject.getMonth() + 1)


        this.dayInfo = dayInfo
        this.events = events
        this.sports = sports
        this.dayOffs = dayOffs
        this.retreats = retreats

        this.dayInfoPart = document.getElementById('calendar-day-info')
        this.dayInfoObject = document.getElementById('day-info')

        this.calendarFieldsBox = document.getElementById('calendar-big-days')
        this.localDateStr = document.getElementById('local-date-str')
    }

    loadDaysCalendar() {
        let leftDays = 42;

        const currentMonthDays = getMonthDays(null, null, this.localDate);
        const firstDayOfWeek = getFirstWeekDay(null, null, this.localDate);

        const startOffset = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

        const prevMonthDays = getMonthDays(null, null, this.prevMonthObject);

        for (let i = prevMonthDays - startOffset + 1; i <= prevMonthDays; i++) {
            leftDays -= 1;
            this.insertDay(this.prevMonthObject.getFullYear(), this.prevMonthObject.getMonth(), i);
        }

        for (let i = 1; i <= currentMonthDays; i++) {
            leftDays -= 1;
            this.insertDay(this.localDate.getFullYear(), this.localDate.getMonth(), i);
        }

        for (let i = 1; i <= leftDays; i++) {
            this.insertDay(this.nextMonthObject.getFullYear(), this.nextMonthObject.getMonth(), i);
        }
    }

    analyzeDataAmount(object){
        if (object !== null){
            return object.length
        }
        else {
            return 0;
        }
    }

    insertDay(year, month, day){
        month ++
        const dateIndex = year + '-' + ifLessThan10Add0(month) + '-' + ifLessThan10Add0(day)

        const dayObject = document.createElement('li')
        dayObject.classList.add('day', 'border-color-theme', 'background-theme', 'selectable')
        if (this.currentDate.getFullYear() === year &&  this.currentDate.getMonth() + 1 === month &&this.currentDate.getDate() === day){
            dayObject.classList.add('current-day')
        }
        if (this.localDateGlobal.getFullYear() === year &&  this.localDateGlobal.getMonth() + 1 === month && this.localDateGlobal.getDate() === day){
            this.displayDayInfo(dateIndex, false)
        }

        if (isWeekEnd(year, month, day)) dayObject.classList.add('weekend')


        // main day info
        const dayInfo = this.dayInfo[dateIndex]
        let scheduleStr = ''
        let testDayStr = ''
        if (dayInfo !== null){
            if (dayInfo['schedule'] === 'Special'){
                dayInfo['schedule'] = 'S'
            }
            if (dayInfo['schedule'].length <= 3){
                dayInfo['schedule'] = dayInfo['schedule'].toUpperCase()
            }
            scheduleStr = dayInfo['schedule']
            if (dayInfo['testDay'] !== null){
                testDayStr += '(' + dayInfo['testDay'] + ')'
            }
        }
        // day info
        const dayDateAndInfo = document.createElement('p')
        if (this.localDate.getMonth() + 1 !== month){
            dayDateAndInfo.classList.add('secondary-day-info')
        }
        dayDateAndInfo.innerHTML = `
        <div class="day-title">
            <p class="day-number">${day}</p> 
            <p class="academic-info">
                <span class="testday">${testDayStr}</span>
                <span class="schedule">${scheduleStr}</span>
            </p>
        </div>
        `


        const dayAdditional = document.createElement('div')
        dayAdditional.classList.add('day-additional')
        if (this.localDate.getMonth() + 1 !== month){
            dayAdditional.classList.add('secondary-day-info')
        }

        // day events (important, events, sports, etc)
        const dayEvents = document.createElement('div')
        dayEvents.classList.add('day-events')


        let importantEventsAmount = 0
        if (this.retreats[dateIndex] !== null){
            importantEventsAmount += 1
        }
        let eventsAmount = this.analyzeDataAmount(this.events[dateIndex]) // events
        if (eventsAmount !== 0){
            Array.from(this.events[dateIndex]).forEach((event) => {
                if (event['important'] === 1){
                    importantEventsAmount += 1
                    eventsAmount --
                }
            })
        }
        if (importantEventsAmount > 0){
            const importantEventsSection = document.createElement('p')
            importantEventsSection.innerText = importantEventsAmount
            importantEventsSection.classList.add('events-important', 'number-holder', 'golden-5', 'quantity-number-holder')
            dayEvents.appendChild(importantEventsSection)
        }
        if (eventsAmount > 0){
            const eventsSection = document.createElement('p')
            eventsSection.innerText = eventsAmount
            eventsSection.classList.add('events', 'number-holder', 'golden-5', 'quantity-number-holder')
            dayEvents.appendChild(eventsSection)
        }
        const sportsAmount = this.analyzeDataAmount(this.sports[dateIndex]) // sports
        if (sportsAmount !== 0){
            const sportsSection = document.createElement('p')
            sportsSection.innerText = sportsAmount
            sportsSection.classList.add('sports', 'number-holder', 'golden-5', 'quantity-number-holder')
            dayEvents.appendChild(sportsSection)
        }
        const dayOffsObject = document.createElement('div')
        dayOffsObject.classList.add('day-offs')
        let dayOff = this.dayOffs[dateIndex] !== null
        if (dayOff === true){
            dayOff = this.dayOffs[dateIndex]

            let allGradesOut = true
            for (let i = 9; i <= 12; i++){
                if (dayOff[i] !== 1) allGradesOut = false
            }
            if (!allGradesOut){
                for (let i = 9; i <= 12; i++) {
                    if (dayOff[i] === 1) {
                        const gradeName = gradeToName(i)
                        const dayOffGradeObject = document.createElement('p')
                        dayOffGradeObject.classList.add(gradeName, 'day-off', 'golden-5')
                        dayOffGradeObject.innerText = getCapitalizedFirstLetter(gradeName)
                        dayOffsObject.appendChild(dayOffGradeObject)
                    }
                }
            }
            else {
                dayObject.classList.add('day-day-off')
                const dayOffGradeObject = document.createElement('p')
                dayOffGradeObject.classList.add('all', 'day-off', 'golden-5')
                dayOffGradeObject.innerText = 'All'
                dayOffsObject.appendChild(dayOffGradeObject)
            }
        }



        // last
        dayObject.appendChild(dayDateAndInfo)
        dayAdditional.appendChild(dayEvents)
        dayAdditional.appendChild(dayOffsObject)
        dayObject.appendChild(dayAdditional)

        dayObject.addEventListener("click", () => {
            this.displayDayInfo(dateIndex)
        });

        this.calendarFieldsBox.appendChild(dayObject)
    }


    displayDayInfo(index, changeLocalDate = true){
        if (changeLocalDate){
            fetchServer('localDate', index, 'request')
        }

        this.dayInfoObject.innerHTML = ''

        this.createLocalDateStr(index)
        const mainDayInfo = this.createMainInfoObject(index)
        const events = this.createEventsObject(index)
        const sports = this.createSportsObject(index)

        if (mainDayInfo !== null){
            this.dayInfoObject.appendChild(mainDayInfo)
        }
        if (events !== null){
            this.dayInfoObject.appendChild(events)
        }
        if (sports !== null){
            this.dayInfoObject.appendChild(sports)
        }

    }

    createLocalDateStr(index){
        this.localDateStr.innerHTML = `
            <p>${dateToFullString(YmdDateToDate(index))}</p>
        `
    }

    createMainInfoObject(index) {
        if (this.dayInfo[index] || this.dayOffs[index] || this.retreats[index]) {
            const events_holder = document.createElement('div')
            events_holder.classList.add('event-holder')

            let html = ''

            if (this.dayInfo[index]) {
                let dayInfo = this.dayInfo[index]
                if (dayInfo.schedule === 'S') {
                    dayInfo.schedule = 'Special'
                }

                html += `
                        <div class="main-day-info">
                            <div class="event important default-border golden-5">
                                <p class="main-day-title"><span class="very-important-text">${dayInfo.schedule}</span> Schedule</p>
                                <p class="description secondary-text">${dayInfo.description}</p>
                            </div>
                `;

                if (dayInfo['testDay']) {
                    html += `
                            <div class="event important default-border golden-5">
                                <p class="main-day-title">Day <span class="very-important-text">${dayInfo['testDay']}</span></p>
                            </div>
                         </div>
                `;
                }

                html += '</div>'
            }

            if (this.dayOffs[index]) {
                let dayOffs = this.dayOffs[index]
                let dayOff_str
                if (dayOffs['type'] === 'f') {
                    dayOff_str = 'No school'
                } else {
                    dayOff_str = 'There are online classes'
                }

                let dayOffArray = []
                for (let i = 9; i <= 12; i++) {
                    if (dayOffs[i]) {
                        dayOffArray.push(`<span class="${gradeIntToClass(i)} badge">${gradeIntToStr(i, 1)}</span>`)
                    }
                }

                if (dayOffArray.length === 4){
                    dayOff_str += ' for <span class="all badge">All</span> grades'
                }
                else {
                    dayOff_str +=  ` for ${dayOffArray.join(' ')}`
                }
                html += `
                 <div class="event default-border border-color-theme golden-5">
                      <p>${dayOff_str}</p>
                </div>
                `
            }

            html += '</div>'
            events_holder.innerHTML = html;

            return events_holder
        }
        return null;
    }
    createEventsObject(index){
        if (this.events[index]) {
            const events_holder = document.createElement('div')
            events_holder.classList.add('event-holder')

            let html = ''
            const events = this.events[index]
            Array.from(events).forEach(function (event){
                html += `
                    <div class="event default-border border-color-theme golden-5">
                        <p class="event-title">${event['title']}</p>
                   `
                if (event['description']){
                    html += `
                        <p class="secondary-text">${event['description']}</p>
                    `
                }
                let timeString = ''
                if (event['time_start']){
                    timeString = calculateTimeForEventString(index, event['time_start'], event['time_end'])
                }
                html += `
                        <p class="time-field secondary-text">${timeString}</p>
                    </div>`

            })
            events_holder.innerHTML = html
            return events_holder
        }
        return null
    }
    createSportsObject(index){
        if (this.sports[index]){
            const sports_holder = document.createElement('div')
            sports_holder.classList.add('event-holder')

            let html = ''
            const sports = this.sports[index]
            Array.from(sports).forEach(function (sport) {
                const token = generateToken()

                let gender
                if (sport['gender'] === 'B'){
                    gender = 'boys'
                }
                else {
                    gender = 'girls'
                }

                html += `
                <div class="event default-border border-color-theme golden-5 ${gender}">
                    <div class="event-title">
                        <p>${sport['varsity']}</p>
                        <p style="align-content: center">${sport['sport']}</p>
                        <p>${sport['gender']}</p>    
                    </div>
                `
                if (sport['opponent'] !== null){
                    html += `<p class="enemy golden-5">${sport['opponent']}`
                }
                if (sport['side'] !== null){
                    html += ` — ${sport['side']}`
                }
                html += '</p>'
                let timeString = ''
                if (sport['time_start']){
                    timeString = calculateTimeForEventString(index, sport['time_start'], sport['time_end'])
                }
                html += `
                    <p id="${token}" class="time-field secondary-text"><span>${timeString}</span></p>
                </div>`


            })
            sports_holder.innerHTML = html
            return sports_holder;
        }
        return null;
    }
}

addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('calendar-page')){
        const calendar = new CalendarBig(
            currentDate, localDate, localDateGlobal, dayInfoArray, eventsArray, sportsArray, dayOffArray, retreatsArray
        )
        calendar.loadDaysCalendar()
    }
})