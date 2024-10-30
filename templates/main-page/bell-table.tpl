<div class="table-box">
    <div class="table-frame box-shadow">
        <table class="content-table">
            <thead>
            <tr>
                <th>Description / Period</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Length</th>
            </tr>
            </thead>
            <tbody>
            {foreach $tableData as $element}
                <tr class="table-row">
                    <td>{$element.name}</td>
                    <td>{$element.start_time}</td>
                    <td>{$element.end_time}</td>
                    <td>{$element.length}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>