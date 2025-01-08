function liveTimer(){
    let time = new Date();
    document.getElementById('watch').innerHTML = time.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', second: "numeric", hour12: true })
}
liveTimer()
setInterval('liveTimer()', 1000)
