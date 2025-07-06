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