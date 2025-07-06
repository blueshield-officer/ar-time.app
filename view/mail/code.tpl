{extends 'mail.tpl'}

{block title}Code{/block}

{block content}
    <p>Enter this code on the identity verification screen:</p>
    <p style="font-weight: bold; font-size: 24px">{$code}</p>
    <p style="font-size: 12px">It expires in 5 minutes.</p>
{/block}