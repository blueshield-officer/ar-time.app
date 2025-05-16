<div id="date-bar" class="light-shadow background-theme">
    <div class="container date-bar">
        <a id="date-bar-previous" class="chevron selectable background-theme golden-5"><img src="/frontend/assets/img/svg/chevron-left.svg" alt="chevron-left" class="img-stretch"></a>
        <div id="date-local" class="date background-theme selectable golden-5"></div>
        <a id="date-bar-next" class="chevron selectable background-theme golden-5"><img src="/frontend/assets/img/svg/chevron-right.svg" alt="chevron-right" class="img-stretch"></a>
    </div>
    <div id="calendar-object" class="background-theme calendar-mini border-color-theme light-shadow golden-5" hidden="hidden">
        <div class="calendar-title background-theme-darker-softer">
            <div class="selectable background-theme-darker-softer golden-5">
                <img id="prev-calendar" src="/frontend/assets/img/svg/chevron-left.svg" alt="chevron-left" class="img-stretch chevron">
            </div>
            <p id="calendar-month-year"></p>
            <div class="selectable background-theme-darker-softer golden-5">
                <img id="next-calendar" src="/frontend/assets/img/svg/chevron-right.svg" alt="chevron-right" class="img-stretch chevron">
            </div>
        </div>
        <ul id="week-column" class="weeks column dates-column secondary-text" hidden>
            <li>Mon</li>
            <li>Tue</li>
            <li>Wed</li>
            <li>Thu</li>
            <li>Fri</li>
            <li>Sat</li>
            <li>Sun</li>
        </ul>
        <ul id="calendar-months" class="months column months-column" hidden>

        </ul>
        <ul id="calendar-days" class="days column dates-column">

        </ul>
        <div class="mt-2"> <a id="comeback-button" class="text-link"></a></div>
    </div>
</div>