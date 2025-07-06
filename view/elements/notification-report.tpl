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