<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{block title}{/block}</title>
</head>
<body>
<div>
    <div style="
    width: 100%;
    display:flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;">
        <div style="
        display: flex;
        flex-direction: column;
        border: 1px solid grey;
        border-radius: 5px;
        justify-content: center;
        align-items: center;
        width: 300px;">
            <div style="display: flex; width: 100%; padding: 10px 0; border-bottom: 1px solid grey; align-content: center; justify-content: center;">
                <img src="https://sandbox.ar-time.org/frontend/assets/img/website-icons/ar-time-bar-icon.png" alt="ar-time" style="width: 40px; height: 40px; margin-right: 10px;">
                <div style="font-weight: bold; font-size: 24px; align-self: center">ar-time</div>
            </div>
            <div style="padding: 25px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                {block content}

                {/block}
            </div>
        </div>
    </div>
</div>
</body>
</html>