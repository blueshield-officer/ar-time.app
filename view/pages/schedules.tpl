<html>
<head class="data-overlayscrollbars-initialize">
    {include 'elements/header.tpl'}
    <title>Roadmap</title>
</head>
<body class="data-overlayscrollbars-initialize">
{assign var=currentPage value="Schedules"}
{include 'elements/wrapper.tpl'}
<div id="screen">
    <div id="schedules" class="container">
        <h1 class="page-title">Schedules</h1>
        <p class="page-description">List of all official and non-official (special) schedules.</p>

        <div class="object">
            <h3>Official schedule:</h3>
            <div class="table-list">
                {foreach $schedules_official as $scheduleName => $schedule}
                    <div class="table-object simple-border">
                        <div class="table-title">Schedule {capitalize str=$scheduleName}</div>
                        <table class="table">
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
                {/foreach}
            </div>
        </div>

        <div class="object">
            <h3>Non-official schedule:</h3>
            <div class="table-list">
                {foreach $schedules_nonofficial as $scheduleName => $schedule}
                    <div class="table-object simple-border">
                        <div class="table-title">Schedule {capitalize str=$scheduleName}</div>
                        <table class="table">
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
                {/foreach}
            </div>
        </div>
    </div>
</div>
</body>
</html>