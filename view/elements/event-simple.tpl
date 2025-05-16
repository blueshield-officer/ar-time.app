{assign var=uniqueId value="{uniqueId}"}
<div class="event default-border border-color-theme golden-5">
    <p class="event-title">{$event['title']}</p>
    {if $event['description']}<p>{$event['description']}{/if}
    {if $event['location']}<p class="secondary-text">{$event['location']}{/if}

    <p id="{$uniqueId}" class="time-field secondary-text"></p>

</div>
{if $event['time_start']}
    <script>
        calculateTimeForEventObject("{$uniqueId}", '{$event['time_start']}', '{$event['time_end']}')
    </script>
{/if}