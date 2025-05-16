{extends 'elements/wrapper.tpl'}

{block name=page}Schedules{/block}

{block name=content}
<div id="screen" class="padding-32">
    <div id="schedules-page" class="container-fluid">
        <h1 class="page-title">Schedules</h1>
        <p class="page-description-h1">List of all official and non-official (special) schedules.</p>

    {foreach $bells as $official => $bellsList}
        <div class="object">
            <h3>{if $official === 1}Official schedules:{else}Non-official schedules:{/if}</h3>
            <div class="table-list">
                {foreach $bellsList as $scheduleName => $schedule}
                    <div id="schedule-{$scheduleName}" class="section-card-v border-color-theme">
                        <div class="first-part border-color-theme background-theme-darker-softer">
                            <p>
                                <span class="table-title"> {capitalize str=$scheduleName}</span>
                                {if isset($bellsDescriptions[$scheduleName])}
                                    <span class="table-title-divider"> - </span>
                                    <span class="table-description">{$bellsDescriptions[$scheduleName]}</span>
                                {/if}
                            </p>
                        </div>
                        <div class="second-part">
                            <table class="table-object">
                                <thead>
                                <tr>
                                    <th>Description / Period</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Length</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $schedule as $position => $data}
                                    <tr>
                                        <td>{$data['name']}</td>
                                        <td>{$data['start_time']}</td>
                                        <td>{$data['end_time']}</td>
                                        <td>{$data['length']}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/foreach}
</div>
{/block}