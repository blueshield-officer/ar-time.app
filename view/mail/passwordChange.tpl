{extends 'mail.tpl'}

{block title}Token{/block}

{block content}
    <p>Use this link to change your password:</p>
    <a href="https://sandbox.ar-time.org/password-change?token={$token}">Change</a>
    <p>It expires in 5 minutes.</p>
{/block}