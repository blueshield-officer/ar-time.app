function switchNotificationToken(toggleID) {
    const instructionObject = document.getElementById('apple-notification-instruction')
    if (document.getElementById('toggle-' + toggleID).checked) {
        const toggle = document.getElementById('toggle-' + toggleID)
        const notifErrorObject = document.getElementById('alert-notificationsPermissionIssue')

        if(!('Notification' in window)){
            toggle.checked = false;
            notifErrorObject.style.display = 'flex';
            notifErrorObject.innerHTML = '<p>To turn on notification on phone, use a guide below to turn on PWA</p>'
        }
        else {
            Notification.requestPermission()
                .then((permission) => {
                    if (permission === 'granted') {
                        navigator.serviceWorker.ready.then((sw) => {
                            sw.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: "BPmY3aRTv3WMzUk87saQ_ecjhzlhKq8FtdX9j-_dJuwCnePGRgXSBRlhgEnmgNTUvgKF6Xoo5zqcd5ZSPXD3Bt8"
                            }).then(async (subscription) => {
                                if ((await fetchServer('notificationToken', JSON.stringify(subscription))).success === true) {
                                    Array.from(document.getElementsByClassName('notification-setting-hidden')).forEach((element) => {
                                        element.classList.remove('notification-setting-hidden')
                                        element.classList.add('notification-setting')
                                    });
                                    instructionObject.hidden = true
                                }
                            });
                        });
                    } else {
                        document.getElementById('toggle-' + toggleID).checked = false
                        notifErrorObject.style.display = 'flex';
                        notifErrorObject.innerHTML = '<p>Please allow Notification permission in website settings in order to use it. <br>(School Chromebooks are NOT supported)</p>'
                    }
                })
        }
    } else {
        fetchServer('notificationToken', null);
        Array.from(document.getElementsByClassName('notification-setting')).forEach((element) => {
            element.classList.remove('notification-setting')
            element.classList.add('notification-setting-hidden')
        });
        if (isSafari()){
            instructionObject.hidden = false
        }
    }
}

async function switchAccountParam(toggleID) {
    const object = document.getElementById('toggle-' + toggleID)
    console.log(toggleID)
    if (object.type === 'time') {
        console.log(await fetchServer(toggleID, object.value, 'fetch'))
    } else {
        if (object.checked) {
            console.log(await fetchServer(toggleID, 1, 'fetch'));
        } else {
            console.log(await fetchServer(toggleID, 0, 'fetch'));
        }
    }
}


document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('apple-notification-instruction')){
        if (isSafari()){
            document.getElementById('apple-notification-instruction').hidden = false
        }
    }
});