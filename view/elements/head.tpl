<!-- Google Analytics -->
{literal}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-F0F4K1TJVH"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-F0F4K1TJVH');
    </script>
{/literal}

<!-- Standard Meta -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- iOS specific -->
<link rel="apple-touch-icon" href="/frontend/assets/img/website-icons/ar-time-bar-icon.png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<!-- Content-Security-Policy -->


<!-- header version -->
{assign var="hVersion" value="1.00"}

<link rel="stylesheet" href="/frontend/dist/css/bootstrap.min.css?v={$hVersion}">
<link rel="stylesheet" href="/frontend/dist/css/normalize.min.css?v={$hVersion}">
<link rel="stylesheet" href="/frontend/dist/css/monster.min.css?v={$hVersion}">
<link rel="stylesheet" href="/frontend/dist/css/overlayscrollbars.min.css?v={$hVersion}">

<!-- Manifest for PWA -->
<link rel="manifest" href="/manifest.json?v={$hVersion}">


<link id="theme" rel="stylesheet" type="text/css" href="{if $theme !== 'system'}/frontend/dist/css/theme-{$theme}.min.css?v={$hVersion}{/if}"/>
<script>

    if ('{$theme}' === 'system') {
        let theme
        const prefersDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;
        if (prefersDarkMode) {
            theme = "dark";
        } else {
            theme = "light";
        }
        document.getElementById('theme').href = '/frontend/dist/css/theme-' + theme + '.min.css?v={$hVersion}';
    }
</script>


<script src="/frontend/dist/js/bootstrap.bundle.min.js?v={$hVersion}"></script>
<script src="/frontend/dist/js/monster-min.js?v={$hVersion}"></script>
<script src="/frontend/dist/js/overlayscrollbars.browser.es5.min.js?v={$hVersion}"></script>


