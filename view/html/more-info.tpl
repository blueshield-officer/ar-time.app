{assign var=uniqueID value="{uniqueId}"}

<div class="info-button" style="margin-left: 3.5px">
    <div id="popout-text-{$uniqueID}" class="text-field golden-5">
        <p>{$content}</p>
    </div>
    <img onclick="popOutTextVisible('popout-text-{$uniqueID}')" id="button-{$uniqueID}" style="cursor: pointer;" src="/frontend/assets/img/svg/question-circle.svg" alt="question-mark">
    <script>


        document.addEventListener('click', function (e) {
            const triggerButton = document.getElementById('button-{$uniqueID}');
            const popoutText = document.getElementById('popout-text-{$uniqueID}')
            if (!triggerButton.contains(e.target) && !popoutText.contains(e.target)) {
                popOutTextInVisible('popout-text-{$uniqueID}');
            }
        });
    </script>
</div>
