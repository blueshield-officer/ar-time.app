{extends 'mail.tpl'}

{block title}Token{/block}

{block content}
    <p style="margin: 0; font-size: 12px">Use this link to change your password:</p>
    <a href="https://sandbox.ar-time.org/password-change?token={$token}"
       style="
    margin: 0;
    font-size: 25px;
    font-weight: bold;">Change</a>

    <p style="margin: 0; font-size: 12px">It expires in 5 minutes.</p>
{/block}