{extends file='main.tpl'}

{block name=page}Legacy{/block}

{block 'body'}
    <script>
        const dayInfo = JSON.parse(`{json_encode($schedule)}`)
        const bells = JSON.parse(`{json_encode($bells)}`)
        const isLoggedIn = {$loggedIn|@json_encode}
    </script>
    <div id="legacy-page">
        <div class="top-bar base-transition border-color-theme background-theme">
            <div class="top-bar-panel background-theme container">
                <div class="logo-section border-color-theme r-logo-section">
                    <div class="logo  background-theme-darker">
                        <a id="website-logo" class="icon-holder base-transition background-theme-darker selectable" href="https://www.archbishopryan.com/" target="_blank">
                            <img class="school-icon" alt="ar" src="/frontend/assets/img/website-icons/archbishop-ryan.png">
                        </a>
                        <div id="website-logo-divider" class="website-divider-holder base-transition background-theme-darker">
                            <div id="website-logo-divider-line" class="website-divider base-transition border-background-color-theme"></div>
                        </div>
                        <a href="/about" target="_blank" id="website-title" class="logoTitle base-transition background-theme-darker selectable">
                            <p class="website">ar-time</p>
                            <p class="school secondary-text">Archbishop Ryan High School</p>
                        </a>
                    </div>
                </div>
                <div class="account-block">
                    {if $loggedIn === true}
                        <a href="" class="account-phone border-color-theme top-bar-block"><img id="replacer-account-icon" src="/frontend/assets/img/svg/person-circle.svg" alt="auth-icon"></a>
                        <div id="account-info-box" class="account-box selectable background-theme golden-5">
                            <a href="" class="name">{$userName|escape:"html"}</a>
                        </div>
                    {else}
                        <a href="" class="account-phone border-color-theme top-bar-block"><img id="replacer-account-icon" src="/frontend/assets/img/svg/person-fill.svg" alt="auth-icon"></a>
                        <div id="account-info-box">
                            <a type="button" class="account-button account-button-login border-color-theme background-theme selectable no-copy" href="/login" target="_self">
                                Log In
                            </a>
                            <a type="button" class="account-button account-button-signup background-theme default-border golden-5 no-copy" href="/signup" target="_self">
                                Sign Up
                            </a>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
        <div style="height: calc(100vh - 61px) !important;" class="page-scroll-bar-auto page-scroll-bar">
            <div class="padding-32">
                <div class="container">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="day-info-column">
                                {if {$schedule['schedule']} !== 'Special'}
                                    <div id="countdown" class="default-border border-color-theme golden-5 golden-padding background-theme-darker-softer">
                                        <p id="countdown-label"></p>
                                        <p id="countdown-time"></p>
                                        <div id="countdown-progress-box">
                                            <div id="countdown-progress-info">
                                                <p id="countdown-progress-percentage"></p>
                                                <p id="countdown-progress-time"></p>
                                            </div>
                                            <div id="countdown-progress-bar" class="golden-5">
                                                <span id="countdown-progress-bar-filled" class="golden-5"></span>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                                    <div class="default-border border-color-theme golden-5 golden-padding">
                                        <p style="font-size: 24px" class="day-schedule"><strong>{formatScheduleName schedule=$schedule['schedule']}</strong> Schedule</p>
                                        <p class="secondary-text">{$schedule['description']}</p>
                                        {if $schedule['testDay'] !== null}<p style="font-size: 20px">Day <strong>{$schedule['testDay']}</strong></p>{/if}
                                    </div>
                                    <div class="default-border border-color-theme golden-5 golden-padding">
                                        <p>{formatDate date=$localDate format='l, F j, Y'}</p>
                                    </div>

                                    {if $dayOffs !== null}
                                        {$dayOffArray = []}
                                        {if $dayOffs[9] === 1}{$dayOffArray[] = '<span class="freshman badge">Freshmen</span>'}{/if}
                                        {if $dayOffs[10] === 1}{$dayOffArray[] = '<span class="sophomore badge">Sophomores</span>'}{/if}
                                        {if $dayOffs[11] === 1}{$dayOffArray[] = '<span class="junior badge">Juniors</span>'}{/if}
                                        {if $dayOffs[12] === 1}{$dayOffArray[] = '<span class="senior badge">Seniors</span>'}{/if}

                                        {if $dayOffs['type'] === 'f'}
                                            {$dayOffStartStr = 'No school'}
                                        {else}
                                            {$dayOffStartStr = 'There are online classes'}
                                        {/if}

                                        <div class="default-border border-color-theme golden-5 golden-padding">
                                            <p>
                                                {if count($dayOffArray) === 4}
                                                    {$dayOffStartStr} for <span class="all badge">All</span> grades
                                                {else}
                                                    {$dayOffStartStr} for {join(' ', $dayOffArray)}
                                                {/if}
                                            </p>
                                        </div>
                                    {/if}
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="bell-table-column">
                                {if isset($bells)}
                                    <div class="default-border border-color-theme golden-5 golden-padding background-theme-darker-softer">
                                        <table class="bell-table">
                                            <thead>
                                            <tr>
                                                <th>Description / Period</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Length</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {foreach $bells as $position => $row}
                                                <tr id="period-{$position}" class="border-color-theme table-row background-theme">
                                                    <td>{$row['name']}</td>
                                                    <td>{$row['start_time']}</td>
                                                    <td>{$row['end_time']}</td>
                                                    <td>{$row['length']}</td>
                                                </tr>
                                            {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                {else}
                                    <p>Unknown Schedule.</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                    {if $absentCombined !== null}
                        {assign var=periodsFull value=['hr', 1, 2, 3, 4, 5, 6, 7, 8]}
                        <div class="absent-holder">
                            {foreach $absentAdditional as $additional}
                                {$additional = $additional['text']}
                                {if strstr($additional, 'error occured')}
                                    <div class="alert">
                                        {$additional}
                                    </div>
                                {/if}
                            {/foreach}
                            {foreach $absentCombined as $personID => $periods}

                                {assign var=roomChangesExist value=[]}

                                {foreach $periods as $period}
                                    {if $period !== null}
                                        {$roomChangesExist[] = $period}
                                    {/if}
                                {/foreach}

                                {assign var=absentPeriods value=[]}
                                {foreach $periods as $period => $location}
                                    {if $period !== 'sb'}
                                        {$absentPeriods[] = $period}
                                    {/if}
                                {/foreach}

                                {assign var=periodsKeys value=array_keys($periods)}
                                {if count(array_intersect($periodsFull, $periodsKeys)) === count($periodsFull)}
                                    {$absentPeriods = 'full'}
                                {/if}

                                {$roomChangesExist = count($roomChangesExist) !== 0}


                                <div class="absent default-border border-color-theme golden-5 {if $roomChangesExist === false}background-theme-darker-softer{/if}">
                                    <div class="teacher {if $roomChangesExist === true}teacher-with-redirections{/if} border-color-theme background-theme-darker-softer
                        {if $loggedIn === true}
                        {foreach $userFaculty as $myPeriod => $myTeacherID}
                        {if $myTeacherID === $personID}
                                {$myPeriod = strtolower($myPeriod)}
                                {if $absentPeriods === 'full' || in_array($myPeriod, $absentPeriods)}my-teacher{break}{/if}
                        {/if}
                        {/foreach}
                        {/if}">
                                        <div class="absent-teacher-name">
                                            <p>{$faculty[$personID]["short_name"]}</p>
                                            <p>{capitalize str={$faculty[$personID]["position"]}}</p>
                                        </div>
                                        <p class="absent-periods-list">
                                            <span>Absent:</span>
                                            {if $absentPeriods === 'full'}
                                                <span class="success">All Day</span>
                                            {else}
                                                <span class="warning">{strtoupper({join(', ', $absentPeriods)})}</span>
                                            {/if}
                                            {if in_array('sb', $periodsKeys)}
                                                <span class="warning">(SB)</span>
                                            {/if}
                                        </p>
                                    </div>
                                    {if $roomChangesExist === true}
                                        <div class="redirections">
                                            {foreach $periods as $period => $location}
                                                {if $location !== null}
                                                    <div class="{if $loggedIn === true && $userFaculty[strtolower($period)] === $personID}my-teacher-redirection {/if}redirection-field golden-5">
                                                        <div class="period background-theme-darker golden-5">{$period}</div>
                                                        <p class="period-divider">-</p>
                                                        <p>{$location}</p>
                                                    </div>
                                                {/if}
                                            {/foreach}
                                        </div>
                                    {/if}
                                </div>
                            {/foreach}
                        </div>
                    {else}
                        <div class="border-color-theme golden-5 default-border border-color-theme golden-padding">
                            No absences data for this day.
                        </div>
                    {/if}

                    <div class="section-card border-color-theme margin-16">
                        <div class="part border-color-theme">
                            I did this in a hurry, will fix stuff later.
                        </div>
                    </div>


                    {if $loggedIn === true}
                    <p class="d-inline-flex gap-1">
                        <button class="golden-padding golden-5 border-color-theme default-border background-theme selectable" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse-notifications" aria-expanded="false" aria-controls="collapse-notifications">
                            <img class="page-icon size19" alt="icon_bell-fill.svg" src="/frontend/assets/img/svg/bell-fill.svg"> Switch Notification Settings
                        </button>
                    </p>
                    {/if}

                    <p class="d-inline-flex gap-1">
                        <button class="golden-padding golden-5 border-color-theme default-border background-theme selectable" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse-settings" aria-expanded="false" aria-controls="collapse-settings">
                            <div style="align-items: center">
                                <img class="page-icon size20" alt="icon_gear-fill.svg" src="/frontend/assets/img/svg/gear-fill.svg">
                                <span>Switch Settings</span>
                            </div>
                        </button>
                    </p>



                    {if $loggedIn === true}
                        <div class="collapse" id="collapse-notifications">
                            <div style="margin-bottom: 50px;">
                                {include file='elements/notification-allow.tpl'}


                                {include file='elements/notification-absences.tpl'}


                                {include file='elements/notification-report.tpl'}


                                {include file='elements/notification-apple-instruction.tpl'}
                            </div>
                        </div>
                    {/if}

                    <div class="collapse" id="collapse-settings">
                        <div>
                            {include file='elements/account-info.tpl'}


                            {include file='elements/setting-title-countdown.tpl'}


                            {include file='elements/setting-theme.tpl'}


                            {include file='elements/setting-design.tpl'}


                            {include file='elements/setting-teachers.tpl'}

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
{/block}