<html>
<head>

</head>
<body>
<p>test</p>
<script language="JavaScript">
    navigator.serviceWorker.register("sw.js");

    function enableNotif() {
        Notification.requestPermission().then((permission)=> {
            if (permission === 'granted') {
                // get service worker
                navigator.serviceWorker.ready.then((sw)=> {
                    // subscribe
                    sw.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: "BFl0KXWYVr8dPCxMz2hNhP-qObaXFlmE-dTk08xgafS3vwQZVJbxzpVlKEw2bIMXM27vQu0adHgx517m3XsUliw"
                    }).then((subscription)=> {
                        console.log(JSON.stringify(subscription));
                    });
                });
            }
        });
    }
</script>
<button onclick="enableNotif()">Enable Notif</button>
</body>
</html>
