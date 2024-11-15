<html lang="en">
<head>
    {include file='constructor/header.tpl'}
    {assign var="version" value="1"}
    <link rel="stylesheet" href="/css/other/access.css?v={$version}">
</head>
<body>
<div class="position-absolute top-50 start-50 translate-middle">
    <div id="holder" class="holder">
        <p id="title" class="title"></p>
        <p id="description" class="description"></p>
        <p>(this is just a sketch)</p>
    </div>
</div>
<script>
    const status = {$access}
    const bodyStyle = document.body.style
    const title = document.getElementById('title')
    const description = document.getElementById('description')
    function redirect (){
        window.location.href = '/'
    }
    if (status === 1){
        bodyStyle.backgroundColor = '#006800';
        const answer = 'ACCESS GRANTED'
        title.textContent = answer
        document.title = answer
        description.textContent = 'You will keep yours access until closing a browser.'
        setTimeout(redirect, 2000)
    }
    else {
        const answer = 'ACCESS DENIED'
        bodyStyle.backgroundColor = '#b92727';
        title.textContent = answer
        document.title = answer
        setTimeout(redirect, 1000)
    }
</script>
</body>
</html>