{assign var=uniqueID value="{uniqueId}"}

<div class="info-button" style="margin-left: 3.5px">
    <div id="popout-text-{$uniqueID}" class=" text-field golden-5">
        <p>{$content}</p>
    </div>
    <img onmouseover="popOutTextVisible('popout-text-{$uniqueID}')" onmouseleave="popOutTextInVisible('popout-text-{$uniqueID}')" id="button-{$uniqueID}" style="cursor: pointer;" src="/frontend/assets/img/svg/question-circle.svg" alt="question-mark">
</div>