{extends 'elements/wrapper.tpl'}

{block name=page}Home{/block}

{block name=content}

<div id="home">
    {include 'elements/date-bar.tpl'}
    <div class="row align-items-start">
        <div class="col-5 column-home border-color-theme padding-16">
            <div class="content">
                <h3>Day Info</h3>
                <p class="page-description-h3">Short summary of what is going on.</p>

                {if $schedule !== null}
                    <div class="day-info">
                        <div>
                            <span>{$schedule['schedule']}</span>
                            <span>{$schedule['testDay']}</span>
                        </div>
                        <div>
                            {$schedule['description']}
                        </div>
                    </div>
                {/if}
                {var_dump($dayOff)}
            </div>
        </div>
        <div class="col-3 column-home border-color-theme padding-16">
            <div class="content">
                <h3>Absences & Room Changes</h3>
                <p class="page-description-h3">test</p>
            </div>
        </div>
        <div class="col-4 column-home border-color-theme padding-16">
            <div class="content">
                <h3>Schedule</h3>
                <p class="page-description-h3">Bell table for a day.</p>

                {if $schedule !== null}
                    {if $schedule['schedule'] === 'Special'}
                        {if $localDate !== $currentDate}
                            <p>Unknown Schedule</p>
                        {else}
                            <p>As soon as I get special schedule, I will transfer it here.</p>
                        {/if}
                    {elseif $schedule['schedule'] === 'fid'}
                    {elseif $schedule['schedule'] === null}
                    {else}
                        <div id="bells-block">
                            {assign var='startTime' value={findBellTime bells=$bells type='start'}}
                            {assign var='endTime' value={findBellTime bells=$bells type='end'}}
                            <div id="timestamp-block" class="timestamp-block">
                                {for $i = $startTime to $endTime}
                                    <div class="time-line">
                                        <div class="time-block">
                                            <span class="hour">{if $i > 12}{$i - 12}{else}{$i}{/if}</span>
                                            <span class="date-time">{if $i < 12}AM{else}PM{/if}</span>
                                        </div>
                                        <div id="{$i}-h" class="line"></div>
                                    </div>
                                {/for}
                            </div>
                            <div class="periods-block">
                                {foreach $bells as $position => $period}
                                    <div id="period-{$position}" style="display: flex" class="period-box">
                                        <span>{$period['name']}</span>
                                        <span>{$period['length']}</span>
                                        <span>{$period['start_time']}</span>
                                        <span>{$period['end_time']}</span>
                                    </div>
                                {/foreach}
                            </div>
                            <div id="current-time-runner">
                                <div id="time-runner-circle"></div>
                                <div id="time-runner-line"></div>
                            </div>
                        </div>

                        <script>
                            const startTime = '{$startTime}'
                            const endTime = '{$endTime}'
                            const bells = JSON.parse('{json_encode($bells)}')
                        </script
                    {/if}
                {/if}
        </div>
{/block}
