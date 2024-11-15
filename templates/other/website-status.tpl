<!DOCTYPE html>
<html lang="en">
<head>
    {include file='constructor/header.tpl'}
    {assign var="version" value="1.08"}
    <link rel="stylesheet" href="/css/website-status.css?v={$version}">

    <script>
      data = JSON.parse('{$status_json}');
      console.log(data)
    </script>
    <script src="/js/website-status.js?v={$version}"></script>
</head>

<body>
<div class="parent container d-flex justify-content-around align-items-center h-100">
    <div class="row align-items-center">
        <div class="col-md-7 message">
                <div id="info-box" class="info-box box">
                    <img id="image" src="" alt="icon" class="icon">
                    <div id="title" class="title"></div>
                    <div id="description" class="description"></div>
                    <div class="bypass">
                        <p class="title">Got The Bypass Code?</p>
                        <form id="bypass-form" class="form" method="post" action="checks/bypass/">
                            <div class="form-floating">
                                <input type="password" step="1" class="form-control" id="floatingPassword" placeholder="Password" name="code" required>
                                <label for="floatingPassword">Code</label>
                            </div>
                            <button type="submit" class="btn btn-primary button">Bypass</button>
                        </form>
                    </div>
                    <a class="text" href="https://commons.wikimedia.org/wiki/File:Archbishop_Ryan_High_School_-_Philadelphia_01.jpg" target="_blank">Link to original background image.</a>
                </div>
        </div>
        <div class="col-md-5 website">
                <div class="website-box box blur-background">
                    <div class="disclaimer-box box">
                        <p class="disclaimer"><strong>AR-TIME.ORG</strong> is <strong>NOT</strong> affiliated or owned by Archbishop Ryan High School.</p>
                    </div>
                    <div class="ar-time-box box">
                        <img class="image" src="/img/website-icons/ar-time-circle.png" alt="ar-time">
                        <p class="name">AR-TIME.ORG</p>
                        <p class="slogan">USE &middot; ENJOY &middot; SHARE</p>
                    </div>
                    <div class="author-box box">
                        <p class="grey-text">powered by</p>
                        <div class="DBR33">
                            <img class="avatar" src="/img/other/nuke-ops-dbr33.png" alt="nuke-ops">
                            <p class="username">DBR33</p>
                        </div>
                    </div>
                    <div class="social-media-box box">
                        <a href="https://www.instagram.com/ar_time_arhs/" target="_blank" class="link">Instagram</a>
                        <p>·</p>
                        <a href="https://discord.gg/3aX2WpMrtC" target="_blank" class="link">Discord</a>
                        <p>·</p>
                        <a class="link">Email</a>
                    </div>
                </div>
        </div>
    </div>
</div>
</body>

</html>