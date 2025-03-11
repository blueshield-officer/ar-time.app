{assign var=pages value=[
    'Home' =>       ['/', 'house-fill.svg', 20, false],
    'Calendar' =>   ['/calendar', 'calendar-fill.svg', 18, false],
    'Absences' =>   ['/absences', 'person-fill.svg', 20, true],
    'Schedules' => ['/schedules', 'table.svg', 18, false],
    'Countdowns' => ['/countdowns', 'hourglass-split.svg', 20, true],
    'Roadmap' =>    ['/roadmap', 'map-fill.svg', 18, false]
]}
<!--
<button onclick="test()">
    test
</button>
<button onclick="toggleThemes()">
    TOGGLE
</button>
-->
<div class="top-bar">
    <div id="website-logo-section" class="logo-section wrapper-background wrapper-border">
        <div class="logo">
            <a id="website-logo" class="icon-holder wrapper-logo" href="https://www.archbishopryan.com/" target="_blank">
                <img class="icon" alt="ar" src="assets/img/website-icons/archbishop-ryan.png">
            </a>
            <div id="website-logo-divider" class="divider-holder wrapper-logo">
                <div  class="divider wrapper-divider"></div>
            </div>
            <a href="/about" id="website-title" class="logoTitle wrapper-logo">
                <p class="website">ar-time</p>
                <p class="school secondary-text">Archbishop Ryan High School</p>
            </a>
        </div>
    </div>
    <div class="top-section wrapper-background wrapper-border">
        <div onclick="timerOpenWindow()" onmouseover="timerToPercentage()" id="countdown-div">
            <p id="countdown-time">00 : 05</p>
            <div id="countdown-bar"></div>
        </div>
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
                <a type="button" class="account-button account-button-login" href="/login" target="_self">
                    Log In
                </a>
                <a type="button" class="account-button account-button-signup" href="/register" target="_self">
                    Sign Up
                </a>
            {/if}
        </div>
    </div>
</div>
<div id="side-bar" class="side-bar wrapper-background wrapper-border">
    <div id="target" class="scroll-container scroll-bar overlayscrollbars" data-overlayscrollbars-initialize>
        <div class="pages">
            {foreach $pages as $page => $info}
                <a {if $currentPage !== $page} href="{$info[0]}" {/if} target="_self" class="{if $currentPage === $page} current {/if} page wrapper-{if $info[3] === false}no-{/if}border">
                    {if $currentPage === $page}
                        <div class="currentBar"></div>
                    {/if}
                    <img class="icon size{$info[2]} wrapper-page-icon" alt="icon_{$info[1]}" src="frontend/assets/img/svg/{$info[1]}">
                    <p class="pageTitle">{$page}</p>
                </a>
            {/foreach}
        </div>
    </div>
    <div onclick='toggleSideBar(1)' id="collapse" class="collapse wrapper-border">
        <img class="icon" alt="chevron-double-left" src="assets/img/svg/chevron-double-left.svg">
        <p>Collapse sidebar</p>
    </div>
</div>

<div id="countdown-mask" class="background-theme"></div>
