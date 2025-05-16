{extends 'elements/wrapper.tpl'}

{block name=page}Home{/block}

{block name=content}

<div id="home-page">
    {include 'elements/date-bar.tpl'}
    <div class="screen page-scroll-bar-auto page-scroll-bar">
        <div class="container-xxl">
            <div class="row align-items-start safe-date-bar">
                {if $schedule === null &&
                $events === null &&
                $sports === null &&
                $dayOffs === null &&
                $retreats === null
                }
                <div class="center-div border-color-theme default-border background-theme golden-5">
                    No data for this day.
                </div>
                {else}
                <div class="col column-page page-scroll-bar-auto page-scroll-bar">
                    <h3>Day Info</h3>
                    <div class="day-info">
                        <div class="event-holder">
                            {if $schedule !== null}
                                <div class="main-day-info">
                                    <div class="event important default-border golden-5" {if $schedule['testDay'] !== null}style="max-width:100%;"{/if}>
                                        <p class="main-day-title"><span class="very-important-text">{$schedule['schedule']}</span> Schedule</p>
                                        <p class="description secondary-text">{$schedule['description']}</p>
                                    </div>
                                    {if $schedule['testDay'] !== null}
                                        <div class="event important default-border golden-5">
                                            <p class="main-day-title">Day <span class="very-important-text">{$schedule['testDay']}</span></p>
                                        </div>
                                    {/if}
                                </div>
                            {/if}
                            {if $dayOffs !== null}
                                {$dayOffArray = []}
                                {if $dayOffs[9] === 1}{$dayOffArray[] = '<span class="freshman grade-name">Freshmen</span>'}{/if}
                                {if $dayOffs[10] === 1}{$dayOffArray[] = '<span class="sophomore grade-name">Sophomores</span>'}{/if}
                                {if $dayOffs[11] === 1}{$dayOffArray[] = '<span class="junior grade-name">Juniors</span>'}{/if}
                                {if $dayOffs[12] === 1}{$dayOffArray[] = '<span class="senior grade-name">Seniors</span>'}{/if}

                                {if $dayOffs['type'] === 'f'}
                                    {$dayOffStartStr = 'No school'}
                                {else}
                                    {$dayOffStartStr = 'There are online classes'}
                                {/if}

                                <div class="event default-border border-color-theme golden-5">
                                    <p>
                                        {if count($dayOffArray) === 4}
                                            {$dayOffStartStr} for <span class="all grade-name">All</span> grades
                                        {else}
                                            {$dayOffStartStr} for {join(' ', $dayOffArray)}
                                        {/if}
                                    </p>

                                </div>
                            {/if}
                            {if $retreats !== null}
                                <div class="event default-border border-color-theme golden-5">
                                    <p>
                                        <span class="{gradeIntToClassStr grade=$retreats} grade-name">{gradeIntToStr grade=$retreats plural='0'}</span>
                                        Retreat
                                    </p>
                                </div>
                            {/if}
                        </div>
                        {if $events !== null}
                            <div class="event-holder">
                                {foreach $events as $event}
                                    {include file="elements/event-simple.tpl" event=$event}
                                {/foreach}
                            </div>
                        {/if}
                        {if $sports !== null}
                            <div class="event-holder">
                                {foreach $sports as $sport}
                                    {include file="elements/event-sport.tpl" sport=$sport}
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                </div>
                {if $schedule !== null}
                <div class="col column-page">
                    {if $schedule['schedule'] === 'Special'}
                    {if $localDate !== $currentDate}
                    <p>Unknown Schedule.</p>
                    {else}
                    <p>If this text is still here, don't worry; a special schedule soon will be replaced with a custom one, once I get a table.</p>
                    {/if}
                    {elseif $schedule['schedule'] === 'FID'}
                    <p>No school. No Schedule.</p>
                    {elseif $schedule['schedule'] === null}
                    {else}
                    <div id="bells-block" class="{if $currentDateObject !== $localDateObject && $currentDateObject > $localDateObject}day-done{/if}">
                        {assign var='startTime' value={findBellTime bells=$bells type='start'} + 1}
                        {assign var='endTime' value={findBellTime bells=$bells type='end'}}
                        <div id="timestamp-block" class="timestamp-block">
                            {for $i = $startTime to $endTime + (6 - ($endTime - $startTime))}
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
                                <div id="period-{$position}" style="display: flex" class="period-box default-box golden-5 background-theme default-border border-color-theme">
                                    <div class="period-status-bar"></div>
                                    <div class="period-name">
                                        <div class="name">{periodToNumber p=$period['name']}</div>
                                        <div class="period-divider border-background-color-theme"></div>
                                    </div>
                                    <div class="period-info">
                                        <div class="time-length">
                                            <img class="clock-icon" src="/frontend/assets/img/svg/clock-fill.svg" alt="clock-icon">
                                            <span class="start-time">{$period['start_time']}</span>
                                            {if {$period['end_time']} !== ''}
                                                <span> - </span>
                                                <span class="end-time">{$period['end_time']}</span>
                                            {/if}
                                        </div>
                                        {$periodStrToLow = strtolower($period['name'])}
                                        {if $loggedIn && ($periodStrToLow|strstr:'period' || $periodStrToLow === 'homeroom')}
                                            {if $periodStrToLow !== 'homeroom'}
                                                {$periodShort = {periodToNumber p=$periodStrToLow}}
                                            {else}
                                                {$periodShort = 'hr'}
                                            {/if}
                                            <div class="location">
                                                {if isset($userFaculty[$periodShort])}
                                                    {$currentTeacherID = $userFaculty[$periodShort]}
                                                    {if isset({$faculty[$currentTeacherID]['short_name']})}
                                                        {$faculty[$currentTeacherID]['short_name']}

                                                        {if isset($absentCombined[$currentTeacherID][$periodShort])}
                                                            -> <span class="warning">{$absentCombined[$currentTeacherID][$periodShort]}</span>
                                                        {elseif isset($faculty[$currentTeacherID]['room'])}
                                                            -> Room {$faculty[$currentTeacherID]['room']}
                                                        {/if}
                                                    {/if}
                                                {/if}
                                            </div>
                                        {/if}
                                    </div>
                                    {if $period['length'] !== null}
                                        <div class="period-length">
                                            <div class="period-divider border-background-color-theme"></div>
                                            <div class="length border-color-theme">{$period['length']}</div>
                                        </div>
                                    {/if}
                                </div>
                            {/foreach}
                        </div>
                        {if $currentDateObject == $localDateObject}
                            <div id="current-time-runner">
                                <div id="time-runner-circle"></div>
                                <div id="time-runner-line"></div>
                            </div>
                        {/if}

                        <script>
                            const startTime = '{$startTime}'
                            const endTime = '{$endTime}'
                            const bells = JSON.parse('{json_encode($bells)}')
                        </script
                        {/if}
                    </div>
                    {/if}
                    {/if}
                </div>
    </div>
{/block}
<script>
    console.log(test(45))
</script>