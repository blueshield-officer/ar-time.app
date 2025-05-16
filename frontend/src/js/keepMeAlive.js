document.addEventListener('DOMContentLoaded', () => {
    if (typeof isLoggedIn !== 'undefined'){
        if (isLoggedIn === 1) {
            fetchServer('keepMyNotifAlive', null)
        }
    }
})