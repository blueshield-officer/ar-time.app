<html>
<head>
    {include 'elements/header.tpl'}
    <link rel="icon" type="image/x-icon" href="/img/website-icons/ar-time-bar-icon.png">
    <title>Schedule Uploader</title>
</head>
<body>
<form action="/services/upload_schedule" method="post" enctype='multipart/form-data'>
    <label for="file">Select Image:</label>
    <input type="file" id="file" name="file">

    <label for="date">Date:</label>
    <input type="text" id="date" name="date">

    <label for="schedule_id">schedule_id:</label>
    <input type="text" id="schedule_id" name="schedule_id">

    <label for="code">Input Admin Code:</label>
    <input type="password" id="code" name="code">

    <input type="submit">
</form>
</body>
</html>