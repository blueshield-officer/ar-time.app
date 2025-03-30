{assign var=currentPage value="{block name=page}{/block}"}
{assign var=loggedIn value=$smarty.session['logged_in']}

<html>
<head>
    <title>{$currentPage} · ar-time</title>
    <link rel="icon" type="image/x-icon" href="{block name=icon}/assets/img/website-icons/ar-time-bar-icon.png{/block}">
    {include 'elements/head.tpl'}
</head>
<body class="background-theme">
{block name=body}{/block}
</body>
</html>