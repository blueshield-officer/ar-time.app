{if isset($content)}
    <div {if isset($display)}style="display: none"{/if} id="alert-{$id}" class="alert margin-16">
        <p>{$content}</p>
    </div>
{/if}
