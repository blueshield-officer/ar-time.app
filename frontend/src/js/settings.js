function setTheme(theme) {
    if (getCookieValue('theme') === theme){
        return;
    }

    const themes = ['dark', 'light', 'system'];
    const themesNotUsed = themes.filter(el => el !== theme);


    const currentThemeIndicator = document.getElementById(theme + "-theme-switcher");
    const currentThemeIcon = document.getElementById(theme + "-theme-switcher-icon");
    currentThemeIndicator.classList.add('selected-theme')
    currentThemeIcon.src = currentThemeIcon.src.replace(".svg", "-fill.svg")


    themesNotUsed.forEach(themeNotUsed => {
        const localThemeIndicator = document.getElementById(themeNotUsed + "-theme-switcher")
        const localThemeIcon = document.getElementById(themeNotUsed + "-theme-switcher-icon")

        localThemeIndicator.classList.remove('selected-theme')
        if (localThemeIcon.src.includes('-fill.svg')) {
            localThemeIcon.src = (localThemeIcon.src).replace("-fill", '');
        }
    })


    setPermaCookie('theme', theme)

    if (['dark', 'light'].includes(theme)) {
        document.getElementById("theme").href = '/frontend/dist/css/theme-' + theme + '.min.css';
    }
    else {
        document.getElementById("theme").href = '/frontend/dist/css/theme-' + getUserTheme() + '.min.css';
    }
}

function setDesign(design){
    if (getCookieValue('design') !== design){
        setPermaCookie('design', design)
        window.location.href = (design === 'regular') ? '/settings' : '/'
    }
}

function filterNames(periodID, faculty, clear = false){
    function formatText(str){
        return str.replace(/[\s.]/g, '').toLowerCase()
    }
    const teacherInputObject = document.getElementById('teacher-input-period-' + periodID)
    if (clear){
        teacherInputObject.value = ''
    }
    const teacherInputValue = formatText(teacherInputObject.value);

    let filteredFaculty;
    if (teacherInputValue === ''){
        filteredFaculty = faculty;
    }
    else {
        filteredFaculty = faculty.filter(function(object){
            let shortName = object['short_name']
            shortName = (shortName == null) ? '' : formatText(shortName)
            let firstName = object['first_name']
            firstName = (firstName == null) ? '' : firstName.toLowerCase()
            let lastName = object['last_name']
            lastName = (lastName == null) ? '' : lastName.toLowerCase()
            let fullName = formatText(firstName + lastName);

            let room = (object['room'] === null) ? '' : object['room']


            return shortName.includes(teacherInputValue) ||
                lastName.includes(teacherInputValue) ||
                fullName.includes(teacherInputValue) ||
                ((firstName !== null) ? firstName.includes(teacherInputValue) : false) ||
                ((room !== null) ? room.includes(teacherInputValue) : false)
        })
    }

    const listObject = document.getElementById('teacher-list-period-' + periodID);
    listObject.innerHTML = '';
    if (filteredFaculty.length > 0) {
        filteredFaculty.forEach(function(teacherObject) {
            const room = teacherObject['room'];
            const verified = (teacherObject['verified'] === 1) ? true : false;
            const teacherLiObject = document.createElement('li');
            teacherLiObject.classList.add('selectable-no-delay')
            const teacherNameSpanObject = document.createElement('span');
            teacherNameSpanObject.innerText = teacherObject['short_name'];
            teacherLiObject.appendChild(teacherNameSpanObject);

            if (room !== null) {
                const roomSpanObject = document.createElement('span');
                roomSpanObject.classList.add('room-number', 'secondary-text');
                roomSpanObject.innerText = room;
                teacherLiObject.appendChild(roomSpanObject);
            }
            if (verified !== true){
                teacherNameSpanObject.style.color = 'orange';
            }

            teacherLiObject.addEventListener("click", function() {
                fetchServer(formatFetchTitle(periodID), teacherObject['teacher_id'])
                teacherInputObject.innerHTML = ''
                userFacultyList[periodID] = teacherObject['short_name']
                // teacherInputObject.value = teacherObject['short_name'];
                document.getElementById('teacher-list-holder-period-' + periodID).style.display = 'none'
                setTimeout(() => {
                    document.getElementById('cross-holder-period-' + periodID).hidden = false;
                }, 300)
            });
            listObject.appendChild(teacherLiObject);
        });
    }
    else {
        const emptyNotifier = document.createElement('span');
        emptyNotifier.innerText = 'Nobody matches yours request';
        emptyNotifier.classList.add('secondary-text');
        emptyNotifier.classList.add('nobody-found');
        listObject.appendChild(emptyNotifier);
    }
}

addEventListener("DOMContentLoaded", () => {
    if  (document.getElementById('settings-page') || document.getElementById('legacy-page') ){
        const tabCounterSwitcher = document.getElementById('toggle-tabCounter')
        if (tabCounterSwitcher) {
            tabCounterSwitcher.addEventListener('change', function(event) {
                if (event.target.checked) {
                    setPermaCookie('tabCounter', 'true')
                } else {
                    setPermaCookie('tabCounter', 'false')
                }
            });
        }

        const overlayOnSleep = document.getElementById('toggle-overlayOnSleep')
        if (overlayOnSleep) {
            overlayOnSleep.addEventListener('change', function(event) {
                if (event.target.checked) {
                    setPermaCookie('overlayOnSleep', 'true')
                    if (countdown) countdown.setSleepMode(true)
                } else {
                    setPermaCookie('overlayOnSleep', 'false')
                    if (countdown) countdown.setSleepMode(false)
                }
            });
        }


        if (typeof periodsList !== 'undefined'){
            periodsList.forEach(period => {
                const inputObject = document.getElementById('teacher-input-period-' + period)
                const teacherListObject = document.getElementById('teacher-list-holder-period-' + period)

                inputObject.addEventListener("focus", () => {
                    teacherListObject.style.display = "block";
                });

                inputObject.addEventListener("blur", () => {
                    setTimeout(() => {
                        inputObject.value = userFacultyList[period]
                        teacherListObject.style.display = "none"
                    }, 200)
                });
            })
        }

        const logoutButton = document.getElementById('logout-button')
        if (logoutButton) {
            logoutButton.addEventListener('mousedown', () => {
                logoutButton.style.backgroundColor = 'rgba(255, 0, 0, 0.3)'
            })
            logoutButton.addEventListener('mouseup', () => {
                logoutButton.removeAttribute('style');
            })
            logoutButton.addEventListener('click', () => logOutButton(logoutButton))
            logoutButton.addEventListener('mouseleave', () => resetLogOutButton(logoutButton))
        }
    }
})

function removeTeacher(periodID){
    fetchServer(formatFetchTitle(periodID), null)
    userFacultyList[periodID] = null
    document.getElementById('teacher-input-period-' + periodID).value = ''
    document.getElementById('cross-holder-period-' + periodID).hidden = true
}

function formatFetchTitle(periodID){
    let fetchTitle = 'account'
    if (isNaN(parseInt(periodID))) {
        fetchTitle += (periodID === 'hr') ? 'Homeroom' : ucwords(periodID)
    }
    else {
        fetchTitle += ('Period' + periodID)
    }
    return fetchTitle
}

function switchInactiveTimeMinutes (switchInactiveTimeMinutes, minutesMin, minutesMax) {
    const inputObject = document.getElementById(switchInactiveTimeMinutes)
    const minutes = inputObject.value
    let value
    if (minutes > minutesMax) {
        value = minutesMax
        inputObject.value = minutesMax
    }
    else if (minutes < minutesMin) {
        value = minutesMin
        inputObject.value = minutesMin
    }
    else {
        value = minutes
    }

    setPermaCookie('overlayOnSleepTime', value)
    if (countdown){
        countdown.setSecondsUntilSleepMode(value)
    }
}

function logOutButton(element){
    if (element.dataset.usersure === '0') {
        element.innerText = 'Are You Sure?'

        element.style.WebkitBoxShadow = '0px 0px 20px 1px rgba(255,0,0,1)';
        element.style.MozBoxShadow = '0px 0px 20px 1px rgba(255,0,0,1)';
        element.style.boxShadow = '0px 0px 20px 1px rgba(255,0,0,1)';

        element.dataset.usersure = '1'
    }
    else {
        fetchServer('logOut', null)
        setTimeout(() => {
            window.location.href = '/'
        }, 300)
    }
}

function resetLogOutButton(element){
    element.innerText = 'Log Out'

    element.dataset.usersure = '0'

    element.removeAttribute('style');
}