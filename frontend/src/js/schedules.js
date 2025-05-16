addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('schedules-page')){
        const hash = window.location.hash
        if (hash !== ''){
            const targetObject = document.getElementById('schedule-' + hash.slice(1))
            if (targetObject){
                const title = targetObject.getElementsByClassName('table-title')[0]
                targetObject.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                const previousColor = title.style.color
                targetObject.classList.remove('border-color-theme')
                targetObject.style.borderColor = '#FF0000'
                title.style.color = '#FF0000'
                setTimeout(()=>{
                    targetObject.style.transition = 'border 3s'
                    title.style.transition = 'color 3s'
                    targetObject.classList.add('border-color-theme')
                    title.style.color = previousColor
                }, 2000)
            }
        }
    }
})
