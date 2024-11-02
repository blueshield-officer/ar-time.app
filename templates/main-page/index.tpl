<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../../libs/bootstrap/css/bootstrap.css">

    <link rel="stylesheet" href="../../css/universal/normalize.css">
    <link rel="stylesheet" href="../../css/universal/fonts.css">

    <link rel="stylesheet" href="../../css/universal/navbar.css">
    <link rel="stylesheet" href="../../css/countdown-box.css">
    <link rel="stylesheet" href="../../css/bell-table.css">
    <link rel="stylesheet" href="../../css/partnerships.css">
    <link rel="stylesheet" href="../../css/absence.css">
    <link rel="stylesheet" href="../../css/universal/botbar.css">
    <link rel="stylesheet" href="../../css/universal/eventbar.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../img/website-icons/ar-time-bar-icon.png">

    <link rel="stylesheet" href="../../css/index.css">
    <title>ar-time</title>
    <script>
      let data = JSON.parse('{$schedule_JSON}');
      let absence = {
        data: JSON.parse('{$absence['absence_JSON']}'),
        md5: '{$absence['absence_MD5']}',
        version_time: '{$absence['version_time']}'
      }
      console.log(absence)

      event_bar = '{$event_bar}'

    </script>
</head>
<body>
{include file="$navbar"}
{if '$event_bar' !== ''}
    {include file="universal/event-bar.tpl"}
{/if}
<div class="container">
    <div class="row">
        <div class="col-md-5">
            {include file="main-page/countdown-box.tpl"}
        </div>
        <div class="col-md-7">
            {if is_array($table_data)}
                {include file="main-page/bell-table.tpl" tableData = $table_data}
            {else}
                {include file="main-page/bell-table-null.tpl"}
            {/if}
        </div>
        <div class="col-md-12 justify-content-center">
            {include file="main-page/absence.tpl"}
        </div>
    </div>
</div>
<!---->
{include file="universal/botbar.tpl"}

<script src="../../libs/bootstrap/js/bootstrap.bundle.js"></script>
<script src="../../js/countdown-box.js"></script>
<script src="../../js/navbar.js"></script>
<script src="../../js/absent.js"></script>
<script src="../../js/botbar.js"></script>
<script src="../../js/eventbar.js"></script>
</body>
</html>