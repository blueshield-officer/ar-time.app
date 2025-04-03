{extends 'elements/wrapper.tpl'}

{block name=page}Notifications{/block}

{block name=content}
    <script>
        navigator.serviceWorker.register("sw-min.js")
    </script>

    <div id="screen" class="padding-32">
        <div id="notifications" class="container">
            <h1>Notifications</h1>
            <p class="page-description-h1">Be always in the body of events.</p>
            {include 'html/alert.tpl' id='notificationsPermissionIssue' display='none' content='Please allow Notification permission in website settings in order to use it.'}
            <div class="section-card border-color-theme margin-16">
                <div class="first-part part border-color-theme">
                    <h3>Website Notifications</h3>
                    <p>Saves your time, really.</p>
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='notifications' onchange='enableNotifications("notifications")'}
                </div>
            </div>


            <div class="mt-4 section-card border-color-theme margin-16">
                <div class="first-part part border-color-theme">
                    <h3>Absences</h3>
                    <p>Notify me if my teacher or staff member is absent.</p>
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='absentNotifications' onchange='sendNewAccountParams(currentScheduleTable)'}
                </div>
            </div>

            <div class="section-card border-color-theme margin-16">
                <div class="first-part part border-color-theme">
                    <h3>Report</h3>
                    <p>Get short but useful information about tomorrow's school day at the chosen time.</p>
                    <label for="appointment">
                    </label><input
                            class="mt-2 time-selector border-color-theme"
                            type="time"
                            id="appointment"
                            name="appointment"
                            value="18:30"
                            required />
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='reportNotifications' onchange=''}
                </div>
            </div>

            <div class="section-card border-color-theme margin-16">
                <div class="first-part part border-color-theme">
                    <h3>Guider</h3>
                    <p>Notify a few minutes before the next period, if I have a substitute teacher, where do I go. No more waiting for Google Docs being a load after remembering about substitute teacher. The first report as non-homeroom one is included.</p>
                </div>
                <div class="second-part part">
                    {include 'html/toggle.tpl' id='guiderNotifications' onchange=''}
                </div>
            </div>


        </div>
    </div>
{/block}