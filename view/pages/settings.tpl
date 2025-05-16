{extends 'elements/wrapper.tpl'}

{block name=page}Settings{/block}
{block name=content}
<div id="screen" class="padding-32">
    <div id="settings-page" class="container">
        <h1>Settings</h1>
        <p class="page-description-h1">Make your experience even better by setting up a website only for your needs.</p>

        {if $loggedIn === true}
        <div class="mb-4 section-card border-color-theme margin-16">
            <div class="part border-color-theme account-info">
                <div class="account-title">
                    <div>
                        <h3>{$userName}</h3>
                        <p>{$userEmail}</p>
                    </div>
                    <p class="account-badge account-badge-{if $additionalAccountInfo['type'] === 'student'}mortal{elseif $additionalAccountInfo['type'] === 'staff'}immortal{/if}">{capitalize str=$additionalAccountInfo['type']}</p>
                </div>
                {if $additionalAccountInfo['graduation_year'] !== null && $additionalAccountInfo['type'] === 'student'}
                    <div>
                        <span class="{$additionalAccountInfo['grade']} golden-5 grade">{capitalize str={$additionalAccountInfo['grade']}}</span> <span>Class of </span> <span class="highlighted-text">{$additionalAccountInfo['graduation_year']}</span>
                    </div>
                {/if}
                <div class="d-flex justify-content-between">
                    <div></div>
{*                    <button data-usersure="0" class="action-button change-password-button" id="change-password-button">*}
{*                        Change Password*}
{*                    </button>*}
                    <button data-usersure="0" class="action-button log-out-button" id="logout-button">
                        Log Out
                    </button>
                </div>
            </div>
        </div>
        {/if}

        <div class="section-card border-color-theme margin-16">
            <div class="first-part part border-color-theme">
                <h3>Page Title Countdown</h3>
                <p>Turn the counter in the page title once the tab is hidden.</p>
            </div>
            <div class="second-part part">
                {include 'html/toggle.tpl' id='tabCounter' checked=$smarty.cookies['tabCounter']}
            </div>
        </div>

        <div class="section-card border-color-theme margin-16">
            <div class="first-part part border-color-theme">
                <h3>Sleeping Mode</h3>
                <p>Once you are inactive for some time, countdown overlay would appear by itself.</p>
                <input
                        style="width: 60px; margin-right: 7px"
                        id="switchInactiveTimeMinutes"
                        class="mt-2 time-selector border-color-theme"
                        type="number"
                        min="1"
                        max="30"
                        name="switchInactiveTimeMinutes"
                        value="{$smarty.cookies['overlayOnSleepTime']}"
                        oninput="switchInactiveTimeMinutes('switchInactiveTimeMinutes', 1, 30)"
                />
                <label for="switchInactiveTimeMinutes">Duration (minutes)
                </label>
            </div>
            <div class="second-part part">
                {include 'html/toggle.tpl' id='overlayOnSleep' checked=$smarty.cookies['overlayOnSleep']}
            </div>
        </div>


        <div class="section-card border-color-theme margin-16">
            <div class="first-part part border-color-theme w-50">
                <h3>Website theme</h3>
                <p>Welcome to burn out eyes gang.</p>
            </div>
            <div class="second-part part gap-5 w-50">
                    <div id="light-theme-switcher" onclick="setTheme('light')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $theme === 'light'}selected-theme{/if}"><img id="light-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/sun{if $theme === 'light'}-fill{/if}.svg" alt="light-theme-icon">Light</div>
                    <div id="dark-theme-switcher" onclick="setTheme('dark')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $theme === 'dark'}selected-theme{/if}" ><img id="dark-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/moon{if $theme === 'dark'}-fill{/if}.svg" alt="dark-theme-icon">Dark</div>
                    <div id="system-theme-switcher" onclick="setTheme('system')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $theme === 'system'}selected-theme{/if}"><img id="system-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/laptop{if $theme === 'system'}-fill{/if}.svg" alt="system-theme-icon">System</div>
            </div>
        </div>


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
    </div>
</div>


{/block}