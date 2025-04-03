{extends 'elements/wrapper.tpl'}

{block name=page}Settings{/block}

{block name=content}
<div id="screen" class="padding-32">
    <div id="settings" class="container">
        <h1>Settings</h1>
        <p class="page-description-h1">Make yours experience even better by setting up website only for yours needs.</p>

        <div class="section-card border-color-theme margin-16">
            <div class="first-part part border-color-theme">
                <h3>Browser settings</h3>
                <p>Turn counter in the page title once tab is hidden.</p>
            </div>
            <div class="second-part part">
                {include 'html/toggle.tpl' id='tabCounter' checked=$smarty.cookies['tabCounter']}
            </div>
        </div>

        <div class="section-card border-color-theme margin-16">
            <div class="first-part part border-color-theme w-50">
                <h3>Website theme</h3>
                <p>Welcome to burned out eyes gang.</p>
            </div>
            <div class="second-part part gap-5 w-50">
                    <div id="light-theme-switcher" onclick="setTheme('light')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $theme === 'light'}selected-theme{/if}"><img id="light-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/sun{if $theme === 'light'}-fill{/if}.svg" alt="light-theme-icon">Light</div>
                    <div id="dark-theme-switcher" onclick="setTheme('dark')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $theme === 'dark'}selected-theme{/if}" ><img id="dark-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/moon{if $theme === 'dark'}-fill{/if}.svg" alt="dark-theme-icon">Dark</div>
                    <div id="system-theme-switcher" onclick="setTheme('system')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $theme === 'system'}selected-theme{/if}"><img id="system-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/laptop{if $theme === 'system'}-fill{/if}.svg" alt="system-theme-icon">System</div>
            </div>
        </div>

    </div>
</div>


{/block}