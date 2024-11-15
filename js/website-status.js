addEventListener("DOMContentLoaded", function () {
    document.title = 'ar-time | ' + data.title;
    const box = document.getElementById('info-box')
    const title = document.getElementById('title')
    const description = document.getElementById('description')
    const image = document.getElementById('image')


    box.style.backgroundColor = 'rgba(' + data.bg_color + ', 0.7)'
    box.style.backdropFilter = 'blur(10px)';
    box.style.border = '5px solid ' + 'rgb(' + data.bg_color + ')'
    box.style.color = 'rgb(' + data.color + ')'

    title.style.fontWeight = 'bold'
    title.innerText = data.title

    description.innerHTML = data.description

    image.src = '/img/svg/' + data.image + '.svg'
});