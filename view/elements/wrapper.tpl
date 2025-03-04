{assign var=pages value=[
    [
        'title' => 'Home',
        'link' => '/',
        'icon' => 'house-fill.svg'
    ],
    [
        'title' => 'Calendar',
        'link' => '/calendar',
        'icon' => 'calendar-fill.svg'
    ],
    [
    'title' => 'Calendar',
    'link' => '/calendar',
    'icon' => 'calendar-fill.svg'
    ]
]}
{var_dump($pages)}


<div class="theme-light">
    <div class="top-bar wrapper-background wrapper-border">
        <div class="logo-section wrapper-border">
            <div class="logo wrapper-logo">
                <a class="icon-holder" href="https://www.archbishopryan.com/" target="_blank">
                    <img class="icon" alt="ar" src="assets/img/website-icons/archbishop-ryan.png">
                </a>
                <div class="divider wrapper-divider"></div>
                <div class="title">
                    <p class="website">ar-time</p>
                    <p class="school secondary-text">Archbishop Ryan High School</p>
                </div>
            </div>
        </div>
        <div class="top-section">
            <div class="countdown-block">
                test
            </div>
            <div class="account-block">
                <div id="account-box" class="account">
                </div>
            </div>
        </div>
    </div>
    <div class="side-bar wrapper-background wrapper-border">
        <div id="target" class="scroll-bar overlayscrollbars" data-overlayscrollbars-initialize data-overlayscrollbars="host">
            <div class="pages" data-overlayscrollbars-viewport="scrollbarHidden overflowXScroll overflowYScroll" tabindex="-1">
                {foreach $pages as $page}
                    <a href="{$page['link']}" target="_self" class="page wrapper-border">
                        <img class="icon wrapper-page-icon" alt="icon_{$page['icon']}" src="frontend/assets/img/svg/{$page['icon']}">
                        <p class="title">{$page['title']}</p>
                    </a>
                {/foreach}
            </div>
            <div class="os-scrollbar os-scrollbar-vertical os-theme-dark os-scrollbar-auto-hide os-scrollbar-handle-interactive os-scrollbar-visible" style="--os-viewport-percent: 0.4922; --os-scroll-direction: 0;">
                <div class="os-scrollbar-track">
                    <div class="os-scrollbar-handle">
                    </div>
                </div>
            </div>
        </div>
        <div class="footer wrapper-border">
            <img class="icon" alt="chevron-double-left" src="assets/img/svg/chevron-double-left.svg">
            <p>Collapse sidebar</p>
        </div>
    </div>
</div>