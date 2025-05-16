{extends 'mail.tpl'}

{block title}Code{/block}

{block content}
    <p style="margin: 0; font-size: 12px">Enter this code on the identity verification screen:</p>
    <span style="
    margin: 0;
    font-size: 25px;
    font-weight: bold;">
        {$code}
    </span>
    <p style="margin: 0; font-size: 12px">It expires in 5 minutes.</p>
{/block}