{assign var=uniqueID value="{uniqueId}"}

<div class="table-span">
    <div id="popout-table-{$uniqueID}" class="popout-table golden-5">
        <table>
            <thead>
                <tr>
                    <th>Description / Period</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Length</th>
                </tr>
            </thead>
            <tbody>
                {foreach $schedulerTable as $schedulerTableRow}
                    <tr class="">
                        <td>{$schedulerTableRow['name']}</td>
                        <td>{$schedulerTableRow['start_time']}</td>
                        <td>{$schedulerTableRow['end_time']}</td>
                        <td>{$schedulerTableRow['length']}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <span onclick="popOutTextVisible('popout-table-{$uniqueID}')" id="schedule-{$uniqueID}" class="schedule-name make-text-more-visible">{$scheduleName}</span>

    <script type="text/javascript">
        document.addEventListener('click', function (e) {
            const triggerText = document.getElementById('schedule-{$uniqueID}');
            const table = document.getElementById('popout-table-{$uniqueID}')
            if (!triggerText.contains(e.target) && !table.contains(e.target)) {
                popOutTextInVisible('popout-table-{$uniqueID}');
            }
        });
    </script>
</div>