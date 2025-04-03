function enableNotifications(toggleID){
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            console.log(permission);
            navigator.serviceWorker.ready.then((sw)=> {
                console.log('Service Worker is ready');

                sw.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: "BPmY3aRTv3WMzUk87saQ_ecjhzlhKq8FtdX9j-_dJuwCnePGRgXSBRlhgEnmgNTUvgKF6Xoo5zqcd5ZSPXD3Bt8"
                }).then((subscription)=> {
                    console.log(JSON.stringify(subscription));
                });
            });
        }
        else {
            document.getElementById('toggle-' + toggleID).checked = false
            document.getElementById('label-' + toggleID).style.cursor = 'not-allowed';
            document.getElementById('alert-notificationsPermissionIssue').style.display = 'flex';
        }
    })
}

function sendNewAccountParams(type, data) {
    fetch('/services/account-fetch/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json(); // Parse JSON response
        })
        .then(responseData => {
            console.log('Success:', responseData); // Log the parsed response data
        })
        .catch(error => {
            console.error('Error:', error);
        });
}