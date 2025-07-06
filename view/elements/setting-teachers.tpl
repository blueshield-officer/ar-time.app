{if $loggedIn === true && $additionalAccountInfo['type'] === 'student'}
    <script>
        let userFacultyList = []
    </script>
    {assign var=periods value=['hr', 1, 2, 3, 4, 5, 6, 7, 8]}
    <div class="mt-4 section-card border-color-theme margin-16" id="teacher-selection-block">
        <div class="first-part part border-color-theme w-25">
            <h3>My teachers</h3>
            <p>Choose all of your current teachers.</p>
        </div>
        <div class="second-part part w-75 periods-part">
            {assign var="periodCount" value=0}
            <div class="periods-column">
                {foreach $periods as $period}
                {if $periodCount > 0 && $periodCount % 5 == 0}
            </div><div class="periods-column">
                {/if}
                <script>
                    userFacultyList['{$period}'] = '{if $userFaculty[$period] !== null}{$faculty[$userFaculty[$period]]['short_name']}{else}{/if}'
                </script>
                <div class="input-block golden-5 border-color-theme default-border">
                    <div class="period-block period-name border-color-theme">
                        <p class="period-label">{if $period === 'hr'}Homeroom{elseif $period === 'custom'}Custom{else}Period {$period}{/if} </p>
                    </div>
                    <div class="period-block period-teacher">
                        <div id="cross-holder-period-{$period}" class="cross-holder" {if $userFaculty[$period] === null}hidden{/if}>
                            <div class="cross"
                                 onclick="removeTeacher('{$period}')"
                            ></div>
                        </div>
                        <div class="input-holder">
                            <input
                                    value="{if $userFaculty[$period] !== null}{$faculty[$userFaculty[$period]]['short_name']}{else}{/if}"
                                    name="period-{$period}"
                                    id="teacher-input-period-{$period}"
                                    type="text"
                                    class="input-teacher-field"
                                    placeholder="Select a teacher..."
                                    onfocus="filterNames('{$period}', faculty, true)"
                                    oninput="filterNames('{$period}', faculty)"
                            >
                            <div id="teacher-list-holder-period-{$period}" class="input-teacher-list-holder background-theme" data-overlayscrollbars-initialize>
                                <div class="list-holder">
                                    <ul id="teacher-list-period-{$period}" class="input-teacher-list">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {$periodCount = $periodCount + 1}
                {/foreach}
            </div>
        </div>
    </div>
    <script>
        const faculty = Object.values(JSON.parse(`{($facultyJSON)}`))
        const periodsList = JSON.parse(`{json_encode($periods)}`)
        console.log(userFacultyList)
    </script>
    <div class="alert">
        Currently teachers' database is still being collected.
        The First part fetched from the AR website, the second one is being added gradually from Google Docs,
        where after adding to a database it must be manually verified by me.
        <br>
        People with orange names are UNVERIFIED, don't be surprised if they disappear from your chosen teachers.
    </div>
{/if}