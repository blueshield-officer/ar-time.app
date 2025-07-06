<div class="section-card border-color-theme margin-16">
    <div class="first-part part border-color-theme">
        <h3>Sleeping Mode</h3>
        <p>Once you are inactive for some time, a countdown overlay would appear by itself.</p>
        <input
                style="width: 60px; margin-right: 7px"
                id="switchInactiveTimeMinutes"
                class="mt-2 time-selector border-color-theme"
                type="number"
                min="1"
                max="30"
                name="switchInactiveTimeMinutes"
                value="{$smarty.cookies['overlayOnSleepTime']}"
                oninput="switchInactiveTimeMinutes('switchInactiveTimeMinutes', 1, 30)"
        />
        <label for="switchInactiveTimeMinutes">Duration (minutes)
        </label>
    </div>
    <div class="second-part part">
        {include 'html/toggle.tpl' id='overlayOnSleep' checked=$smarty.cookies['overlayOnSleep']}
    </div>
</div>