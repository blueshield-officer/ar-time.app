{extends file="main.tpl"}

{block name=body}
    <div id="{$pageId}">
        <!-- header -->
        <header class="auth-header">
            <a href="/" style="display: flex;"><img class="website-logo" src="/frontend/assets/img/website-icons/ar-time-bar-icon.png" alt="ar-time"><p class="auth-header-text">ar-time</p></a>
        </header>

        <!-- block -->
        {block name="content"}
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        {/block}

        <!-- footer -->
        <footer class="auth-footer">

        </footer>
    </div>
{/block}