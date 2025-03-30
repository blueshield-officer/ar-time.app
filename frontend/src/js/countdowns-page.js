document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('countdowns')) {

        function analyzeDateAndRecord(date1, date2, index, type){
            const title = document.getElementById('time-until-' + type + '-' + index)
            const string =
                daysDifferenceToString(
                    datesToDayDifference(
                        YmdDateToDate(date1), date2
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
            object.innerText = string
            document.getElementById('link-' + index).href += YmdDateToUS(dateYmd)
        }
        function processData(dataArray, currentDate, index, type){
            analyzeDateAndRecord(dataArray[index].date, currentDate, index, type)
            setDate(index, dataArray[index].date, type)
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
    }
})