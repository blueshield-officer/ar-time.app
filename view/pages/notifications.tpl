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
            {include 'html/alert.tpl' id='notificationsPermissionIssue' display='none' content='Please allow Notification permission in website settings in order to use it. <br>(School Chromebooks are NOT supported)'}
            <div class="section-card border-color-theme margin-16">
                <div class="first-part part border-color-theme">
                    <h3>Website Notifications</h3>
                    <p>Saves your time, really.</p>
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='notifications' onchange='switchNotificationToken("notifications")' checked=$sessionParams['notification']}
                </div>
            </div>

            <div class="mt-4 notification-setting{if $sessionParams['notification'] === 0}-hidden{/if} section-card border-color-theme margin-16">
                {if $additionalAccountInfo['type'] === 'staff'}
                    <p class="p-5">There is nothing here, for now.</p>
                {else}
                <div class="first-part part border-color-theme">
                    <h3>Absences</h3>
                    <p>Notify me if my teacher or staff member is absent.</p>
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='notificationAbsence' onchange='switchAccountParam("notificationAbsence")' checked=$sessionParams['notification_absence']}
                </div>
                {/if}
            </div>

            {if $sessionParams['notification'] === 0}
            <div id="apple-notification-instruction" class="mt-4 section-card border-color-theme margin-16" hidden>
                <!--
                    Thanks for an idea => https://webpushtest.com/
                -->
                <div class="part apple-instructions">
                    <h3>IPhone / IPad Notification Guide (PWA)</h3>
                    {for $i = 1; $i <= 4; $i++}
                        <div class="apple-instructions-step border-color-theme">
                            <p class="instructions-text">
                                <span class="step-highlight">Step {$i}:</span>
                                <span class="secondary-text">
                                    {if $i === 1}
                                        In Safari, click the 'share' button
                                    {elseif $i === 2}
                                        Click 'Add to Home Screen'
                                    {elseif $i === 3}
                                        Review settings and click 'Add'
                                    {else}
                                        Launch app from home screen
                                    {/if}
                                </span>
                            </p>
                            <img src="/frontend/assets/img/dump/notification_instructions/step{$i}.jpg" class="instructions-image golden-5">
                        </div>
                    {/for}
                </div>
            </div>
            {/if}

            <!--
            <div class="notification-setting{if $sessionParams['notification'] === 0}-hidden{/if} section-card border-color-theme margin-16">
                <div class="first-part part border-color-theme">
                    <h3>Report</h3>
                    <p>Get short but useful information about tomorrow's school day at the chosen time.</p>
                    <label for="appointment">
                    </label><input
                            id="toggle-notificationReportTime"
                            class="mt-2 time-selector border-color-theme"
                            type="time"
                            name="appointment"
                            value="{$sessionParams['notification_report_time']}"
                            onchange='switchAccountParam("notificationReportTime")'
                    />
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='notificationReport' onchange='switchAccountParam("notificationReport")' checked=$sessionParams['notification_report']}
                </div>
            </div>
            -->

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