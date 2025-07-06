{extends 'elements/wrapper.tpl'}

{block name=page}Development{/block}

{block name=content}
<div id="roadmap" class="padding-32">
    <div class="container">
        <h1 class="page-title">Website Development</h1>
        <p class="page-description-h1">Here you can see all that I have done, doing or planning.</p>

        <!--
        {if $loggedIn === true}
        <div class="section-card border-color-theme margin-16 d-grid">
            <div class="part ideas block primary-background">
                <h3>Feedback</h3>
                <p>Got an idea of making this website better? You can write it lower and share with me.</p>
                <div class="feedback">
                    <input class="golden-5 default-border border-color-theme golden-padding"></input><button class="golden-5 default-border border-color-theme background-theme-darker">Send</button>
                </div>
            </div>
        </div>
        {/if}
        -->

        {if $changelog !== null}
        <div class="section-card border-color-theme margin-16 d-grid">
            <div class="part">
                <h3>Changelog</h3>
                <div class="changelogs golden-5 border-color-theme default-border golden-padding">
                    {foreach $changelog as $date => $objects}
                        <div class="changelog">
                            <p class="log-date border-color-theme">{formatDate date=$date format='F j, Y'}</p>
                            {foreach $objects as $action => $changeLevels}
                                <p class="action-type">
                                    {if $action === -1}⛔ Removed:
                                    {elseif $action === 0}🐛 Fixed:
                                    {else}✅ Added:
                                    {/if}
                                </p>
                                <ul class="change-list">
                                {foreach $changeLevels as $changeLevel => $changes}
                                    {foreach $changes as $change}
                                        <li class="{$changeLevel}-change">{$change} {if $changeLevel === 'b'}<span class="big-update badge">major</span>{/if}</li>
                                    {/foreach}
                                {/foreach}
                                </ul>
                            {/foreach}
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
        {/if}

        <div class="section-card border-color-theme margin-16 d-grid">
            <div class="part">
                <h3 style="display: flex; justify-content: space-between">
                    <span>Plans</span>
                    <a href="https://wakatime.com/badge/github/SpaceBureaucrat/ar-time.app"><img src="https://wakatime.com/badge/github/SpaceBureaucrat/ar-time.app.svg" alt="wakatime"></a>
                </h3>
                <div class="year-list">
                    {foreach $roadmap as $year => $plansArray}
                        <div class="yearBlock ">
                            <p class="title-1 year border-color-theme">{$year}</p>
                            <div class="plan-list">
                                {foreach $plansArray as $plan}
                                    {assign var=tempUniqueId value="{uniqueId}"}
                                    <div class="plan secondary-background border-color-theme">
                                        <div class="planContent content">
                                            <p class="planTitle">{$plan['name']}</p>
                                            <div class="planInfo">
                                                {if $plan['date_finish'] !== null}
                                                    <p>Finished on <span class="highlighted-text">{$plan['date_finish']}</span>.</p>
                                                {else}
                                                    <p><span class="highlighted-text">{$plan['date_finish_planned']}</span> is planned to finish.</p>
                                                    {if $plan['deferment_days'] !== null}
                                                        <div style="display: flex; align-items: end">
                                                            <div style="margin-right: 5px" class="fire"></div>
                                                            <div style="display: flex; flex-direction:column">
                                                                <p style="font-style: italic">Overtime:</p>
                                                                <p class="alarm-text">{$plan['deferment_days']}   {if $plan['deferment_days'] === 1}day{else}days{/if}</p>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                {/if}

                                            </div>
                                            <p class="click-for-more clickForMore secondary-text no-copy" type="button" data-bs-toggle="collapse" data-bs-target="#{$tempUniqueId}" aria-expanded="false" aria-controls="{$tempUniqueId}">
                                                Click for details
                                            </p>
                                            <span class="planDescription collapse" id="{$tempUniqueId}">
                                        {$plan['description']}
                                                </span>

                                        </div>
                                        <div style="color: {$plan['color']}" class="status">
                                            <div class="statusText">
                                                {$plan['status']}
                                            </div>
                                            <div style="background-color: {$plan['color']}" class="statusBar">

                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
{/block}