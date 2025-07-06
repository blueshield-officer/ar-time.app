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