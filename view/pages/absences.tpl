{extends 'elements/wrapper.tpl'}

{block name=page}Absences{/block}

{block name=content}
<div id="absences-page">
    {include 'elements/date-bar.tpl'}
    <div class="screen page-scroll-bar-auto page-scroll-bar">
        <div class="container-xxl">
            <div class="row align-items-start safe-date-bar">
                <div class="col column-page page-scroll-bar-auto page-scroll-bar">
                    <h3>Absences & Room Changes</h3>
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
                </div>
                <div class="col column-page page-scroll-bar-auto page-scroll-bar">
                    <h3>Analyze Data</h3>
                    <div class="default-border border-color-theme golden-5 golden-padding">
                        <p>
                            I am still thinking which tools I would place on this page.
                            I am not really sure about them, but there might be stuff like "when my teachers were out last time" etc.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}
