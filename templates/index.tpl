<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../libs/bootstrap/css/bootstrap.css">

    <link rel="stylesheet" href="../css/universal/normalize.css">
    <link rel="stylesheet" href="../css/universal/fonts.css">

    <link rel="stylesheet" href="../css/universal/navbar.css">
    <link rel="stylesheet" href="../css/countdown-box.css">
    <link rel="stylesheet" href="../css/bell-table.css">
    <link rel="stylesheet" href="../css/partnerships.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/website-icons/ar-time-bar-icon.png">

    <link rel="stylesheet" href="../css/index.css">
    <title>ar-time.com</title>
    <script>
      let data = JSON.parse('{$schedule_JSON}');
    </script>
</head>
<body>
{include file="$navbar"}
<div class="container">
    <div class="row">
        <div class="col-md-4">
            {include file="$countdown"}
        </div>
        <div class="col-md-8">
            {if $table_data === 'Special'}
                {include file="no-table.tpl"}
            {else}
                {include file="bell-table.tpl" tableData = $table_data}
            {/if}
        </div>
    </div>
</div>
{include file="partnerships.tpl"}

<script src="../libs/bootstrap/js/bootstrap.bundle.js"></script>
<script src="../js/countdown-box.js"></script>
<script src="../js/navbar.js"></script>
</body>
</html>