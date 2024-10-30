console.log(data)
addEventListener("DOMContentLoaded", function(){
  const box = document.getElementById('info-box')
  const title = document.getElementById('title')
  const description = document.getElementById('description')
  const image = document.getElementById('image')


  box.style.backgroundColor = data.bgColor;
  box.style.backdropFilter = 'blur(10px)';
  box.style.border = '5px solid ' + data.borderColor;
  box.style.color = data.color

  title.style.fontWeight = 'bold';
  title.innerText = data.title;

  description.innerText = data.description;

  image.src = data.image;
});