{extends file='main.tpl'}

{assign var=pages value=[
'Home' =>       ['/', 'house-fill.svg', 20, false],
'Calendar' =>   ['/calendar', 'calendar-fill.svg', 18, false],
'Absences' =>   ['/absences', 'person-fill.svg', 20, true],
'Schedules' => ['/schedules', 'table-solid.svg', 19, false],
'Countdowns' => ['/countdowns', 'hourglass-start-solid.svg', 15, true],
'Roadmap' =>    ['/roadmap', 'tools.svg', 20, true],
'Settings' =>    ['/settings', 'gear-fill.svg', 20, true]
]}


{block name=body}
<!--
<button onclick="test()">
    test
</button>
<button onclick="toggleThemes()">
    TOGGLE
</button>
-->
    {assign var=sideBarStatus value="{if $smarty.cookies['sideBar'] === 1}r{else}s{/if}"}

    <aside id="side-bar" class="side-bar base-transition {$sideBarStatus}-side-bar background-theme border-color-theme">
        <div id="website-logo-section" class="logo-section border-color-theme">
            <div class="logo">
                <a id="website-logo" class="icon-holder base-transition {$sideBarStatus}-icon-holder background-theme-darker selectable" href="https://www.archbishopryan.com/" target="_blank">
                    <img class="icon" alt="ar" src="/assets/img/website-icons/archbishop-ryan.png">
                </a>
                <div id="website-logo-divider" class="website-divider-holder base-transition {$sideBarStatus}-website-divider-holder background-theme-darker">
                    <div id="website-logo-divider-line" class="{$sideBarStatus}-website-divider base-transition divider-color-theme"></div>
                </div>
                <a href="/about" target="_blank" id="website-title" class="logoTitle {$sideBarStatus}-logoTitle base-transition background-theme-darker selectable">
                    <p class="website">ar-time</p>
                    <p class="school secondary-text">Archbishop Ryan High School</p>
                </a>
            </div>
        </div>
        <div class="nav-bar background-theme">
            <div id="target" class="scroll-bar" data-overlayscrollbars-initialize>
                <div class="pages">
                    {foreach $pages as $page => $info}
                        <a {if $currentPage !== $page} href="{$info[0]}" {/if} target="_self" class="{if $currentPage === $page} current {/if} page background-theme selectable">
                            {if $currentPage === $page}
                                <div class="currentBar"></div>
                            {/if}
                            <img class="page-icon size{$info[2]} wrapper-page-icon" alt="icon_{$info[1]}" src="/frontend/assets/img/svg/{$info[1]}">
                            <p class="pageTitle base-transition">{$page}</p>
                        </a>
                        {if $info[3] === true}
                            <div class="page-divider divider-color-theme"></div>
                        {/if}
                    {/foreach}
                    <!--
                    <div style="border-bottom: 1px solid" class="wrapper-border">
                        <div style="justify-content: space-between; border: none" class="page" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                            <div style="display: flex">
                                <img class="icon size20 wrapper-page-icon" alt="icon_house-fill.svg" src="frontend/assets/img/svg/house-fill.svg">
                                <p class="pageTitle" style="transition: 0.3s; margin-left: 10px;">Settings</p>
                            </div>
                            <img src="frontend/assets/img/svg/caret-down-fill.svg">
                        </div>
                        <div style="margin-left: 47px;" class="collapse" id="collapseExample">
                            <p class="pageTitle">Website</p>
                            <p class="pageTitle">Account</p>
                        </div>
                    </div>
                    -->
                </div>
            </div>
            <div onclick='toggleSideBar({if $sideBarStatus === 'r'}0{else}1{/if})' id="collapse-button" class="collapse-button base-transition {$sideBarStatus}-collapse-button border-color-theme background-theme selectable">
                <img class="icon base-transition" alt="chevron-double-left" src="/frontend/assets/img/svg/chevron-double-left.svg">
                <p class="base-transition">Collapse sidebar</p>
            </div>
        </div>
    </aside>

    <div id="top-bar" class="top-bar base-transition {$sideBarStatus}-top-bar border-color-theme background-theme">
        {if $currentTable !== null}
            <div onclick="openCountdownMask()" id="countdown-border" class="wrapper-border">
                <div id="countdown-div">
                    <p id="countdown-time" class="time"></p>
                    <div id="countdown-bar"></div>
                </div>
            </div>
        {else}
            <div></div>
        {/if}
        <div class="account-block">
            {if $smarty.session.logged_in === true}
                <div id="account-box" class="account-box dropdown">
                    <div style="display: flex; align-items: center;" class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div>
                            <p class="name">{$smarty.session.name}</p>
                            <p style="margin-right: 5px" class="email secondary-text">{$smarty.session.email}</p>
                        </div>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><a class="dropdown-item" href="#">Logout</a></li>
                    </ul>
                </div>
            {else}
                <a type="button" class="account-button account-button-login border-color-theme background-theme selectable" href="/login" target="_self">
                    Log In
                </a>
                <a type="button" class="account-button account-button-signup background-theme default-border golden-5" href="/register" target="_self">
                    Sign Up
                </a>
            {/if}
        </div>
    </div>

<div id="countdown-mask" class="background-theme">
    <div class="mask-holder">
        <p id="title-mask" class="mask-secondary-data"></p>
        <p id="countdown-time-mask" class="time"></p>
        <div style="display: flex; justify-content: space-between" class="mask-secondary-data">
            <p id="percentage-mask" class="time"></p>
            <p id="end-time-mask"></p>
        </div>
    </div>
    <div onclick="closeCountdownMask()" class="cross-button">
        <span class="cross-symbol">&Cross;</span>
    </div>
</div>

<div id="content" class="{$sideBarStatus}-content base-transition">
    {block name="content"}
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    {/block}
</div>
{/block}