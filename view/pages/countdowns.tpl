{extends 'elements/wrapper.tpl'}

{block name=page}Countdowns{/block}

{block name=content}

{assign var='regularPath' value="/"}
<div id="countdowns" class="padding-32">
    <div class="container">
        <h1 class="page-title">School Countdowns</h1>
        <p class="page-description-h1">All countdowns until important, expected events or holidays are all collected here.</p>

        <h3>Academic</h3>
        <div class="d-flex mb-3">
            {for $i = 1; $i <= 4; $i++}
                <div class=" default-border border-color-theme golden-5">
                    <p>Quarter {$i}</p>
                    <div>
                        <p>Ends</p>
                        <p>Starts</p>
                    </div>
                </div>
            {/for}
        </div>



        <h3>Next day off</h3>
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
        <h3>Next cool days</h3>
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
        <script>
            const gradeCountdowns = JSON.parse('{json_encode($nextDayOff)}')
            const coolDayCountdowns = JSON.parse('{json_encode($nextSchedule)}')
            console.log(coolDayCountdowns)
        </script>
    </div>
    <pre>
        {var_dump($earlyDismissalData)}
    </pre>
</div>

{/block}