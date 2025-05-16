{extends file="main-simple.tpl"}

{block name=page}Schedule Uploader{/block}

{assign var=pageId value="schedule-upload"}

{block name=content}
    <button onclick="addRow()">add row</button>

    <form id="schedule-upload-form" action="/services/upload_schedule" method="post" enctype='multipart/form-data'>
        <div id="schedule-upload-form-main">
            <div>
                <label for="date">Date:</label>
                <input type="date" id="date" name="date">
            </div>

            <div>
                <label for="schedule_id">schedule_id:</label>
                <input type="text" id="schedule_id" name="schedule_id">
            </div>

            <div>
                <label for="schedule_name">schedule_name:</label>
                <input type="text" id="schedule_name" name="schedule_name">
            </div>

            <div>
                <label for="schedule_description">schedule_description:</label>
                <input type="text" id="schedule_description" name="schedule_description">
            </div>
        </div>
        <div id="schedule-upload-form-rows"></div>

        <input type="submit">
    </form>

    <style>
        #schedule-upload-form-main {
            display: flex;
            flex-direction: column;
        }
        #schedule-upload-form-rows {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
        }
        .row-title {
            margin: 0 10px;
        }
    </style>

    <script>
        let position = 1
        function addRow(){
            const rowDiv = document.createElement('div');
            const title = document.createElement('span');
            const removeButton = document.createElement('button');

            rowDiv.id = 'rowDiv' + position

            title.classList.add('row-title');
            title.innerText = position;

            removeButton.textContent  = 'remove';
            removeButton.addEventListener('click', () => {
                position -= 1;
                rowDiv.remove();
            })

            rowDiv.appendChild(title);

            const rowData = ['name', 'length', 'start-time', 'end-time']
            rowData.forEach(function (element) {
                const label = document.createElement('label');
                const input = document.createElement('input');


                label.name = element + position;
                input.name = element + position;

                if (element === 'start-time' || element === 'end-time') {
                    input.type = 'time';
                }
                else {
                    input.type = 'text';
                }

                rowDiv.appendChild(label);
                rowDiv.appendChild(input);
            })
            rowDiv.appendChild(removeButton);
            document.getElementById('schedule-upload-form-rows').appendChild(rowDiv);
            position++
        }
        function removeRow(){

        }
    </script>
{/block}