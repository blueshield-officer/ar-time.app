<div class="section-card border-color-theme margin-16">
    <div class="first-part part border-color-theme">
        <h3>Page Title Countdown</h3>
        <p>Turn the counter in the page title once the tab is hidden.</p>
    </div>
    <div class="second-part part">
        {include 'html/toggle.tpl' id='tabCounter' checked=$smarty.cookies['tabCounter']}
    </div>
</div>