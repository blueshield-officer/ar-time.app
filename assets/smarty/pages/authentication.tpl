<h1>{$method}</h1>
<form method="post" action="https://ar-time.loc/services/auth">
    <!-- Info -->
    <input type="text" name="email" placeholder="Email">
    <input type="password" name="password" placeholder="Password">
    {if $method === 'register'}
        <input type="password" name="password_repeat" placeholder="Repeat Password">
    {/if}

    <!-- Hidden -->
    <input type="hidden" name="method" value="{$method}">
    <button type="submit">{$method}</button>
</form>