{extends 'elements/wrapper.tpl'}

{block name=page}Settings{/block}
{block name=content}
<div id="screen" class="padding-32">
    <div id="settings-page" class="container">
        <h1>Settings</h1>
        <p class="page-description-h1">Make your experience even better by setting up a website only for your needs.</p>

        {include file='elements/account-info.tpl'}


        {include file='elements/setting-title-countdown.tpl'}


        {include file='elements/setting-sleeping-mode.tpl'}


        {include file='elements/setting-theme.tpl'}


        {include file='elements/setting-design.tpl'}


        {include file='elements/setting-teachers.tpl'}

    </div>
</div>


{/block}