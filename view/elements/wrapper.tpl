{extends file='main.tpl'}

{block name=body}

    {assign var=sideBarStatus value="{if $smarty.cookies['sideBar'] === '1'}r{else}s{/if}"}

    {include 'elements/script.tpl'}

    <header id="top-bar" class="top-bar base-transition border-color-theme background-theme">

        <div id="countdown-mask" class="background-theme">
            <div class="mask-holder">
                <p id="title-mask" class="mask-secondary-data"></p>
                <p id="countdown-time-mask" class="time"></p>
                <div style="display: flex; justify-content: space-between" class="mask-secondary-data">
                    <p id="percentage-mask" class="time"></p>
                    <p id="end-time-mask"></p>
                </div>
            </div>
            <div id="cross-button" onclick="closeCountdownMask()" class="cross-button">
                <span id="cross-button" class="cross-symbol">&Cross;</span>
            </div>
        </div>

        <div id="website-logo-section" class="{$sideBarStatus}-logo-section base-transition  logo-section border-color-theme">
            <div class="logo">
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

        <div class="top-bar-panel background-theme">
            <div id="nav-bar-toggle" class="nav-bar-toggle top-bar-block border-color-theme background-theme selectable" onclick="turnBar(this)">
                <div class="bar1"></div>
                <div class="bar2"></div>
                <div class="bar3"></div>
            </div>
            {if $currentTable !== null}
                <div class="top-bar-block">
                    <div onclick="openCountdownMask()" id="countdown-border" class="border-color-theme">
                        <div id="countdown-div">
                            <p id="countdown-time" class="time"></p>
                            <div id="countdown-bar"></div>
                        </div>
                    </div>
                </div>
                <script>
                    const currentTable = {$currentTable}
                </script>
            {else}
                <div></div>
            {/if}
            <div class="account-block">
                {if $loggedIn === true}
                <a href="/settings" class="account-phone border-color-theme top-bar-block"><img id="replacer-account-icon" src="/frontend/assets/img/svg/person-circle.svg" alt="auth-icon"></a>
                    <div id="account-info-box" class="account-box selectable background-theme golden-5">
                        <a href="/settings" class="name">{$userName|escape:"html"}</a>
                    </div>
                {else}
                    <a href="/login" class="account-phone border-color-theme top-bar-block"><img id="replacer-account-icon" src="/frontend/assets/img/svg/person-fill.svg" alt="auth-icon"></a>
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
    </header>

    <div class="main">
            <aside id="side-bar" class="side-bar base-transition {$sideBarStatus}-side-bar background-theme border-color-theme">
                    <div id="target" class="scroll-bar" data-overlayscrollbars-initialize>
                        <div class="pages">
                            {foreach $pages as $page => $info}
                                <a {if $currentPage !== $page} href="{$info[0]}" {/if} target="_self" class="{if $currentPage === $page} selected-theme current {/if} page background-theme selectable">
                                    {if $currentPage === $page}
                                        <div class="currentBar"></div>
                                    {/if}
                                    <img class="page-icon size{$info[2]} wrapper-page-icon" alt="icon_{$info[1]}" src="/frontend/assets/img/svg/{$info[1]}">
                                    <p class="pageTitle base-transition">{$page}</p>
                                </a>
                                {if $info[3] === true}
                                    <div class="page-divider border-background-color-theme"></div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>

                <div onclick='toggleSideBar({if $sideBarStatus === 'r'}0{else}1{/if})' id="collapse-button" class="collapse-button base-transition {$sideBarStatus}-collapse-button border-color-theme background-theme selectable">
                    <img class="icon base-transition" alt="chevron-double-left" src="/frontend/assets/img/svg/chevron-double-left.svg">
                    <p class="base-transition">Collapse sidebar</p>
                </div>
            </aside>
        <div id="screen" class="page-scroll-bar-{if in_array($currentPage, ['Home', 'Calendar', 'Absences'])}never{elseif in_array($currentPage, ['Notifications', 'Settings'])}always{else}auto{/if} page-scroll-bar">
            {block name="content"}
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            {/block}
        </div>
    </div>


{/block}

