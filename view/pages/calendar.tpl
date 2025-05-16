{extends 'elements/wrapper.tpl'}

{block name=page}Calendar{/block}

{block name=content}
<div id="calendar-page">
    {include 'elements/date-bar.tpl'}
    <div class="screen page-scroll-bar-auto page-scroll-bar">
        <div class="container-xxl">
            <div class="calendar-big-block safe-date-bar">
                <div id="calendar-day-info" class="calendar-part calendar-day-info">
                    <div class="local-date-str border-color-theme" id="local-date-str"></div>
                    <div id="day-info" class="day-info day-info-cal"></div>
                </div>
                <div class="calendar-part calendar-big-part">
                    <script>
                        const dayInfoArray = analyzeSQLdata({$schedules})
                        const eventsArray = analyzeSQLdata({$events});
                        const sportsArray = analyzeSQLdata({$sports});
                        const dayOffArray = analyzeSQLdata({$dayOffs});
                        const retreatsArray = analyzeSQLdata({$retreats})
                    </script>
                    <div class="calendar-big golden-5 border-color-theme">
                        <ul id="week-column" class="weeks column dates-column secondary-text  border-color-theme">
                            <li>Mon</li>
                            <li>Tue</li>
                            <li>Wed</li>
                            <li>Thu</li>
                            <li>Fri</li>
                            <li class="li-weekend">Sat</li>
                            <li class="li-weekend">Sun</li>
                        </ul>
                        <ul id="calendar-big-days" class="days column dates-column">

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}