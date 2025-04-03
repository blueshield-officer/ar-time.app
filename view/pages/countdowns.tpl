{extends 'elements/wrapper.tpl'}

{block name=page}Countdowns{/block}

{block name=content}

{assign var='regularPath' value="/"}
<div id="countdowns" class="padding-32">
    <div class="container">
        <h1 class="page-title">School Countdowns</h1>
        <p class="page-description-h1">All countdowns until important, expected events or holidays are all collected here.</p>


        {if $nextDayOff !== null}
            <div class="margin-32">
                <h3>Days off</h3>
                <div class="mb-4 cards-group">
                    {foreach $nextDayOff as $grade => $data}
                        {if $data !== null}
                            <div class="info-card default-border border-color-theme golden-5">
                                <div class="card-title border-color-theme {gradeToClass str=$grade}">{capitalize str=$grade}</div>
                                <div class="p-2 info-box">
                                    <p class="date-until" id="time-until-dayoff-{$grade}">Well</p>
                                    <div class="link-nextDate">
                                        <a class="link-endNextDate" id='link-{$grade}' href="{$regularPath}"><span class="secondary-text text-link" id="date-dayoff-{$grade}">This should not be here...</span></a>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {/if}


        {if $nextSchedule !== null}
            <div class="margin-32">
                <h3>Cool days</h3>
                <div class="mb-3 cards-group">
                    {foreach $nextSchedule as $name => $data}
                        {if $data !== null}
                            <div class="info-card default-border border-color-theme golden-5">
                                <div class="card-title border-color-theme">
                                    {if $name === 'nonA'}Non-A Schedule{elseif $name === 'earlyDismissal'}Early Dismissal {include 'html/more-info.tpl' content='Early dismissal schedules scope consist of those which have 40 minutes or more of early dismissal from the A schedule.'}{else}This should not be here...{/if}</div>
                                <div class="p-2 info-box golden-5">
                                    <p class="date-until" id="time-until-coolSchedule-{$name}">Well</p>
                                    <div class="card-description">
                                        <p>Schedule: <span class="make-text-more-visible">{capitalize str=$data['schedule']}</span></p>
                                        {if $data['schedule'] !== 'special' & $name === 'earlyDismissal'}
                                            <p>School ends at: <span class="end-time">{$earlyDismissalData[$data['schedule']]['end_time']}</span></p>
                                            <p>You get out of building in <span class="saved-time">{$earlyDismissalData[$data['schedule']]['end_unix']}</span> earlier</p>
                                        {/if}
                                    </div>
                                    <div class="link-nextDate">
                                        <a id='link-{$name}' href="{$regularPath}"><span class="secondary-text text-link" id="date-coolSchedule-{$name}">This should not be here...</span></a>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {/if}


        {if $academics !== null}
            <div class="margin-32">
                <h3>Academics</h3>
                <div class="mb-4 cards-group">
                    {foreach $academics as $name => $data}
                        {if $data !== null}
                            <div class="info-card default-border border-color-theme golden-5">
                                <div class="card-title border-color-theme">{capitalize str=$data['name']}</div>
                                <div class="p-2 info-box">
                                    <p class="date-until" id="time-until-academics-{$name}"></p>
                                    <div class="link-nextDate" id="link-nextDate-{$name}">
                                        <a class="link-endNextDate" id='link-{$name}' href="{$regularPath}"><span class="secondary-text text-link" id="date-academics-{$name}"></span></a>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {/if}


        {if $holidays !== null}
            <div class="margin-32">
                <h3>Holidays</h3>
                <div class="mb-4 cards-group">
                    {foreach $holidays as $name => $data}
                        {if $data !== null}
                            <div class="info-card default-border border-color-theme golden-5">
                                <div class="card-title border-color-theme">{capitalize str=$data['name']}</div>
                                <div class="p-2 info-box">
                                    <p class="date-until" id="time-until-holiday-{$name}">Well</p>
                                    <div class="link-nextDate">
                                        <a class="link-endNextDate" id='link-{$name}' href="{$regularPath}"><span class="secondary-text text-link" id="date-holiday-{$name}">This should not be here...</span></a>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>

        {/if}


    </div>


    <script>
        const gradeCountdowns = JSON.parse('{json_encode($nextDayOff)}')
        const coolDayCountdowns = JSON.parse('{json_encode($nextSchedule)}')
        const holidays = JSON.parse('{json_encode($holidays)}')
        const academics = JSON.parse('{json_encode($academics)}')
    </script>
</div>

{/block}