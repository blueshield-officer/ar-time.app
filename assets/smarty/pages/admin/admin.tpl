<!<!doctype html>
<html lang="en">
<head>
    {include file='elements/header.tpl'}
    {assign var='version' value='1.21'}
    <link rel="stylesheet" href="assets/css/admin.css?v={$version}">
    <title>admin | ar-time</title>
</head>
<body>
<div class="control-panel">
    <div class="title">
        Modules Operator
    </div>
    <div class="modules">
        {foreach $modules as $module}
            <div class="module">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="{$module['name']}" {if $module['status'] === 1}checked{/if}>
                    <label class="form-check-label" for="flexSwitchCheckChecked">{$module['name']}</label>
                </div>
            </div>
        {/foreach}
    </div>
</div>
</body>
</html>

<!--
    <link rel="stylesheet" href="assets/node_modules/bootstrap/dist/css/bootstrap.css">
    <script src="assets/node_modules/bootstrap/dist/js/bootstrap.js/bootstrap.js"></script>
-->