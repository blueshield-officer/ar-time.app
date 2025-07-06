<div class="section-card border-color-theme margin-16">
    <div class="first-part part border-color-theme w-50">
        <h3>Design</h3>
        <p>Choose a design which fits you the most</p>
    </div>
    <div class="second-part part gap-5 w-50">
        <div onclick="setDesign('regular')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $design === 'regular'}selected-theme{/if}">
            <img id="system-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/r-square{if $design === 'regular'}-fill{/if}.svg" alt="regular-design-icon">Regular
        </div>
        <div onclick="setDesign('legacy')" class="p-3 d-flex golden-5 border-color-theme default-border selectable no-copy  background-theme {if $design === 'legacy'}selected-theme{/if}">
            <img id="system-theme-switcher-icon" class="icon me-2" src="/frontend/assets/img/svg/backpack{if $design === 'legacy'}-fill{/if}.svg" alt="backpack-design-icon">Legacy
        </div>
    </div>
</div>