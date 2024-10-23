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
    <title>ar-time</title>
    <script>
      let data = JSON.parse('{$schedule_JSON}');
    </script>
</head>
<body>
{include file="$navbar"}
<div class="container">
    <div class="row">
        <div class="col-md-5">
            {include file="$countdown"}
        </div>
        <div class="col-md-7">
            {if is_array($table_data)}
                {include file="bell-table.tpl" tableData = $table_data}
            {else}
                {include file="no-table.tpl"}
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