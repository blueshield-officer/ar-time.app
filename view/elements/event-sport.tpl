{assign var=uniqueId value="{uniqueId}"}
<div class="event default-border border-color-theme golden-5 {if {$sport['gender']}}{if {$sport['gender']} === 'B'}boys{else}girls{/if}{/if}">
    <div class="event-title">
        <p>{$sport['varsity']}</p>
        <p style="align-content: center">{$sport['sport']}</p>
        {if {$sport['gender']}}<p>{$sport['gender']}</p>{/if}
    </div>

    <p class="enemy golden-5">{if {$sport['opponent']} === ''}Open{else}{$sport['opponent']}{/if}{if $sport['side'] !== null} — {$sport['side']}{/if}</p>
    {if {$sport['description']}}<p>{$sport['description']}</p>{/if}
    <p id="{$uniqueId}" class="time-field secondary-text"></p>
</div>

{if $sport['time_start'] !== null}
    <script>
        calculateTimeForEventObject("{$uniqueId}", '{$sport['time_start']}', '{$sport['time_end']}')
    </script>
{/if}