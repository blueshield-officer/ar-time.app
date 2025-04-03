document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('countdowns')) {

        function processDataTwo(date1, date2, currentDate, index, type){
            function addStylesAndTextToImportantTitle(descriptions, title){
                title.innerText = descriptions
                title.style.fontWeight = '700'
            }
            const title = document.getElementById('time-until-' + type + '-' + index)

            const date1Object = YmdDateToDate(date1)
            const date2Object = YmdDateToDate(date2)
            const descriptions = daysToStatus(
                datesToDayDifference(
                    YmdDateToDate(date1), currentDate
                ),
                datesToDayDifference(
                    YmdDateToDate(date2), currentDate
                ), date1Object, date2Object
            )
            if (descriptions === 'Ended') {
                addStylesAndTextToImportantTitle(descriptions, title)
                title.style.alignSelf = 'center'
                title.style.justifySelf = 'center'
                title.style.color = '#28b463'
                title.style.marginTop = 'auto'
                title.style.marginBottom = 'auto'
                document.getElementById('link-nextDate-' + index).remove();
            }

            else {
                if (descriptions.includes('tomorrow')) {
                    addStylesAndTextToImportantTitle(descriptions, title)
                    title.style.color = '#d4ac0d'
                }
                else if (descriptions.includes('today')) {
                    addStylesAndTextToImportantTitle(descriptions, title)
                    title.style.color = '#bc2424'
                }
                else {
                    title.innerHTML = highLightNumbers(descriptions)
                }
                if (descriptions.includes('Starts')) {
                    setDate(index, date1, type)
                }
                else {
                    setDate(index, date2, type)
                }
            }
        }
        function analyzeDateAndRecord(date1, dateCurrent, index, type){
            const title = document.getElementById('time-until-' + type + '-' + index)
            const string =
                daysDifferenceToString(
                    datesToDayDifference(
                        YmdDateToDate(date1), dateCurrent
                    )
                )
            title.innerHTML = highLightNumbers(string)

            if (string.match(/\d/) === null){
                if (string === 'Today'){
                    title.style.color = '#28b463'
                }
                else if (string === 'Tomorrow') {
                    title.style.color = '#d4ac0d'
                }
                title.style.fontWeight = '700'
            }
        }
        function setDate(index, dateYmd, type){
            const string = dateToString(YmdDateToDate(dateYmd))
            object = document.getElementById('date-' + type + '-' + index)
            if (object){
                object.innerText = string
                document.getElementById('link-' + index).href += YmdDateToUS(dateYmd)
            }
        }
        function processData(dataArray, currentDate, index, type){
            if (index !== null){
                dataArray = dataArray[index]
            }
            analyzeDateAndRecord(dataArray.date, currentDate, index, type)
            setDate(index, dataArray.date, type)
        }

        let type
        type = 'dayoff'
        if (gradeCountdowns.freshmen !== null){
            processData(gradeCountdowns, currentDate, 'freshmen', type)
        }
        if (gradeCountdowns.sophomores !== null){
            processData(gradeCountdowns, currentDate, 'sophomores', type)

        }
        if (gradeCountdowns.juniors !== null){
            processData(gradeCountdowns, currentDate, 'juniors', type)

        }
        if (gradeCountdowns.seniors != null){
            processData(gradeCountdowns, currentDate, 'seniors', type)
        }


        type = 'coolSchedule'
        if (coolDayCountdowns.nonA !== null){
            processData(coolDayCountdowns, currentDate, 'nonA', type)
        }
        if (coolDayCountdowns.earlyDismissal !== null){
            processData(coolDayCountdowns, currentDate, 'earlyDismissal', type)
        }

        type = 'holiday'
        if (holidays !== null){
            Object.keys(holidays).forEach(key => {
                processData(holidays, currentDate, key, type)
            });
        }

        type = 'academics'
        if (academics !== null){
            Object.keys(academics).forEach(key => {
                processDataTwo(academics[key][1], academics[key][0], currentDate, key, type)
            });
        }
    }
})