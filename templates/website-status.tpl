<!DOCTYPE html>
<html lang="en">
<html>
<head>
    <link rel="stylesheet" href="../libs/bootstrap/css/bootstrap.css">
    <script src="../libs/bootstrap/js/bootstrap.bundle.js"></script>

    <link rel="stylesheet" href="../css/website-status.css">
    <link rel="stylesheet" href="../css/universal/fonts.css">
    <link rel="stylesheet" href="../css/universal/normalize.css">

    <link rel="icon" type="image/x-icon" href="../img/website-icons/ar-time-bar-icon.png">

    <script>
      data = JSON.parse('{$status_JSON}');
    </script>
    <script src="../js/website-status.js"></script>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-md-6 message">
            <div class="info-block block">
                <div class="info-box box">
                    <img src="../img/svg/lock-fill.svg" alt="icon" class="icon">
                    <div class="title">Title</div>
                    <div class="text">Test</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 website">
            <div class="website-block block">
                <div class="website-box box">
                    <div class="disclaimer-block block">
                        <div class="disclaimer-box blur-background">
                            <p class="disclaimer"><strong>AR-TIME.ORG</strong> is <strong>NOT</strong> affiliated or owned by Archbishop Ryan High School</p>
                        </div>
                    </div>
                    <div class="ar-time-block block">
                        <div class="ar-time-box box blur-background">
                            <img class="image" src="../img/website-icons/ar-time-circle.png" alt="ar-time">
                            <p class="name">AR-TIME.ORG</p>
                            <p class="slogan">USE &middot; ENJOY &middot; SHARE</p>
                        </div>
                    </div>
                    <div class="author-block block">
                        <div class="author-box box blur-background">
                            <p class="grey-text">powered by</p>
                            <div class="DBR33">
                                <img class="avatar" src="../img/other/nuke-ops-dbr33.png" alt="nuke-ops">
                                <p class="username">DBR33</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="image-credit position-absolute bottom-0 end-0 blur-background">
    <a href="https://commons.wikimedia.org/wiki/User:Farragutful">Image owner.</a>
    <a href="https://commons.wikimedia.org/wiki/File:Archbishop_Ryan_High_School_-_Philadelphia_01.jpg" target="_blank">Link to original background image.</a>
</div>
</body>

</html>
