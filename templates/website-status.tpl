<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../libs/bootstrap/css/bootstrap.css">
    <script src="../libs/bootstrap/js/bootstrap.bundle.js"></script>

    <link rel="stylesheet" href="../css/website-status.css">
    <link rel="stylesheet" href="../css/universal/fonts.css">
    <link rel="stylesheet" href="../css/universal/normalize.css">

    <link rel="icon" type="image/x-icon" href="../img/website-icons/ar-time-bar-icon.png">

    <script>
      data = JSON.parse('{$status_json}');
      console.log(data)
    </script>
    <script src="../js/website-status.js"></script>
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-6 message">
            <div class="info-block block justify-content-center align-content-center">
                <div id="info-box" class="info-box box">
                    <img id="image" src="" alt="icon" class="icon">
                    <div id="title" class="title"></div>
                    <div id="description" class="description"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="website-block block blur-background justify-content-center align-content-center">
                <div class="website-box box">
                    <div class="disclaimer-box box">
                        <p class="disclaimer"><strong>AR-TIME.ORG</strong> is <strong>NOT</strong> affiliated or owned by Archbishop Ryan High School.</p>
                    </div>
                    <div class="ar-time-box box">
                        <img class="image" src="../img/website-icons/ar-time-circle.png" alt="ar-time">
                        <p class="name">AR-TIME.ORG</p>
                        <p class="slogan">USE &middot; ENJOY &middot; SHARE</p>
                    </div>
                    <div class="author-box box">
                        <p class="grey-text">powered by</p>
                        <div class="DBR33">
                            <img class="avatar" src="../img/other/nuke-ops-dbr33.png" alt="nuke-ops">
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
</div>


<div class="image-credit blur-background">
    <a class="owner" href="https://commons.wikimedia.org/wiki/User:Farragutful">Image owner</a>
    <p> | </p>
    <a class="text" href="https://commons.wikimedia.org/wiki/File:Archbishop_Ryan_High_School_-_Philadelphia_01.jpg" target="_blank">Link to original background image.</a>
</div>
</body>

</html>