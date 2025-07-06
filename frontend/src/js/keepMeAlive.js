document.addEventListener('DOMContentLoaded', () => {
    if (typeof isLoggedIn !== 'undefined'){
        if (isLoggedIn === true) {
            fetchServer('keepMyNotifAlive', null)
        }
    }
})