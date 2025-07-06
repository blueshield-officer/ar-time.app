{extends 'elements/wrapper.tpl'}

{block name=page}Notifications{/block}

{block name=content}
    <script>
        navigator.serviceWorker.register("sw-min.js")
    </script>

    <div id="screen" class="padding-32">
        <div id="notifications-page" class="container">
            <h1>Notifications</h1>
            <p class="page-description-h1">Be always in the body of events.</p>


            {include file='elements/notification-allow.tpl'}


            {include file='elements/notification-absences.tpl'}


            {include file='elements/notification-report.tpl'}


            {include file='elements/notification-apple-instruction.tpl'}


            <!--
            <div class="notification-setting{if $sessionParams['notification'] === 0}-hidden{/if} section-card border-color-theme margin-16">
                <div class="first-part part border-color-theme">
                    <h3>Guider</h3>
                    <p>Notify a few minutes before the next period, if I have a substitute teacher, where do I go. No more waiting for Google Docs being a load after remembering about substitute teacher. The first report as non-homeroom one is included.</p>
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='notificationGuider' onchange='switchAccountParam("notificationGuider")' checked=$sessionParams['notification_guider']}
                </div>
            </div>
            -->


        </div>
    </div>
{/block}