<html>
<head>
    <title>Home</title>
    {include 'elements/header.tpl'}
</head>
<body>
<script>
    let date = '{$smarty.session.date}'
    let calendar_schedules = []
    let calendar_sports = []
    let calendar_events = []
    let calendar_day_off = []

    calendar_schedules[date] = analyzeSQLdata('{$calendar_schedules}')
    calendar_sports[date] = analyzeSQLdata('{$calendar_sports}')
    calendar_events[date] = analyzeSQLdata('{$calendar_events}')
    calendar_day_off[date] = analyzeSQLdata('{$calendar_day_off}')

    console.log(calendar_schedules)
    console.log(calendar_sports)
    console.log(calendar_events)
    console.log(calendar_day_off)
</script>
{assign var=currentPage value="Home"}
{include 'elements/wrapper.tpl'}
<div id="screen">
</div>
</body>
</html>