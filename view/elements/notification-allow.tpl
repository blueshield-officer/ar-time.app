{include 'html/alert.tpl' id='notificationsPermissionIssue' display='none' content='Please allow Notification permission in website settings in order to use it. <br>Later on will add guide to android devices<br>(School Chromebooks are NOT supported)'}
<div class="section-card border-color-theme margin-16">
    <div class="first-part part border-color-theme">
        <h3>Website Notifications</h3>
        <p>Saves your time, really.</p>
    </div>
    <div class="second-part part">
        {include 'html/toggle.tpl' id='notifications' onchange='switchNotificationToken("notifications")' checked=$sessionParams['notification']}
    </div>
</div>

<div id="notification-info" class="section-card border-color-theme margin-16" {if $sessionParams['notification'] !== 0}hidden{/if}>
    <div class="part">
        <p>Notification allows to:</p>
        <ul class="notification-info-list">
            <li>get notification once chosen <span class="success">teacher is out</span></li>
            <li>get information about the <span class="warning">next school day</span> at the chosen time</li>
        </ul>
    </div>
</div>