<html>
<head>
    {include 'elements/header.tpl'}
    <title>Roadmap</title>
</head>
<body>

{assign var=currentPage value="Roadmap"}
{include 'elements/wrapper.tpl'}
<div id="screen">
    <div id="roadmap" class="container">
        <h1 class="page-title">Website Roadmap</h1>
        <p class="page-description">Here you can see all that we have done, doing or planning.</p>
        <div class="year-list block primary-background">
            {foreach $info_roadmap as $year => $plansArray}
                <div class="yearBlock">
                    <p class="title-1 year simple-border">{$year}</p>
                    <div class="plan-list">
                        {foreach $plansArray as $plan}
                            {assign var=tempUniqueId value="{uniqueId}"}
                            <div class="plan secondary-background">
                                <div class="planContent content">
                                    <p class="planTitle">{$plan['name']}</p>
                                    <div class="planInfo">
                                        {if $plan['date_finish'] !== null}
                                            <p>Finished on <span class="highlighted-text">{$plan['date_finish']}</span>.</p>
                                        {else}
                                            <p><span class="highlighted-text">{$plan['date_finish_planned']}</span> is planned to finish.</p>
                                            {if $plan['deferment_unix'] !== null}
                                                <div style="display: flex; align-items: end">
                                                    <div style="margin-right: 5px" class="fire"></div>
                                                    <div style="display: flex; flex-direction:column">
                                                        <p style="font-style: italic">Deferment:</p>
                                                        <p class="alarm-text">{$plan['deferment_unix']}</p>
                                                    </div>
                                                </div>
                                            {/if}

                                        {/if}

                                    </div>
                                    <p class="clickForMore secondary-text" type="button" data-bs-toggle="collapse" data-bs-target="#{$tempUniqueId}" aria-expanded="false" aria-controls="{$tempUniqueId}">
                                        Click for details
                                    </p>
                                    <p class="collapse planDescription" id="{$tempUniqueId}">
                                        {$plan['description']}
                                    </p>
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

        <div class="ideas block primary-background">

        </div>
    </div>
</div>
</body>
</html>