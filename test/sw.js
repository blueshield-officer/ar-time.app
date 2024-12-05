console.log('test')

self.addEventListener('push', function(event) {
    const notification = event.data.json();
    // {"title":"Hi" , "body":"test test test" , "url":"./?message=123"}
    event.waitUntil(self.registration.showNotification(notification.title , {
        body: notification.body,
        icon: '/img/website-icons/ar-time-circle.png',
        data: {
            notifURL: notification.url,
        }
    }));
});

self.addEventListener('notificationclick', function(event) {
    event.waitUntil(client.openWindow(event.notification.data.notifURL));

    function enableNotifications() {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                // get service worker
                navigator.serviceWorker.ready.then((sw) => {
                    // subscribe
                    sw.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: ""
                    })
                })
            } else {
                console.log('Unable to get permission to notify.');
            }
        })
    }
});