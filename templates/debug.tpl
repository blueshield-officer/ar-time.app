<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/img/website-icons/ar-time-bar-icon.png">
    <title>Debug page</title>

    <link rel="stylesheet" href="/assets/css/normalize.css">
    <link rel="stylesheet" href="/assets/css/debug.css">
</head>
<body>
<h1 class="title_text">Debug: {$name}</h1>
<h3 class="title_text">Time spent: {$spent_time} seconds</h3>
<h3 class="title_text">Local Date:
{if isset($local_date)}
    {$local_date|date_format:"%A, %B %e, %Y"}
    {else}
    null
{/if}
</h3>


{if $type === 'default'}
    {foreach $data as $key => $object}
        <div class="block">
            <h3 class="block_title">{$key}</h3>
            <div class="block_content">
                {if $object['important']}
                    <pre>{$object['data']|var_dump}</pre>
                {else}
                    {$object['data']|var_dump}
                {/if}
            </div>
        </div>
    {/foreach}
    {elseif $type === 'compare'}
        <table class="compare_table block">
            <thead>
                <tr>
                    {foreach $data as $key => $object}
                        <th>{$key}</th>
                    {/foreach}
                </tr>
            </thead>
            <tbody>
                <tr>
                    {foreach $data as $key => $object}
                        <td>
                            {if $object['important']}
                                <pre>{$object['data']|var_dump}</pre>
                            {else}
                                {$object['data']|var_dump}
                            {/if}
                        </td>
                    {/foreach}
                </tr>
            </tbody>
        </table>
{/if}
</body>
</html>