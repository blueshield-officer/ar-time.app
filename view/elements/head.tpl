<!-- Google Analytics -->
<!--
{literal}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-F0F4K1TJVH"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-F0F4K1TJVH');
    </script>
{/literal}
-->

<!-- Standard Meta -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Content-Security-Policy -->


<!-- header version -->
{assign var="hVersion" value="1.05"}

<link rel="stylesheet" href="/frontend/dist/css/bootstrap.min.css?v={$hVersion}">
<link rel="stylesheet" href="/frontend/dist/css/normalize.min.css?v={$hVersion}">
<link rel="stylesheet" href="/frontend/dist/css/monster.min.css?v={$hVersion}">
<link rel="stylesheet" href="/frontend/dist/css/overlayscrollbars.min.css?v={$hVersion}">

<!--
<link rel="stylesheet" href="frontend/dist/css/theme-dark.min.css?v={$hVersion}">
-->
<link id="theme" rel="stylesheet"
      type="text/css" href="/frontend/dist/css/theme-light.min.css?v={$hVersion}"/>

<script src="/frontend/dist/js/bootstrap.bundle.min.js?v={$hVersion}"></script>
<script src="/frontend/dist/js/monster-min.js?v={$hVersion}"></script>
<script src="/frontend/dist/js/overlayscrollbars.browser.es5.min.js?v={$hVersion}"></script>

<link rel="stylesheet" href="/assets/css/fonts.css">


{include 'elements/script.tpl'}