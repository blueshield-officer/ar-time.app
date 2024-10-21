<?php
/* Smarty version 4.5.4, created on 2024-10-20 20:09:57
  from 'D:\OSPanel\home\ar-time.loc\templates\navbar.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67159bd58893b2_25349704',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '6c943de9c3d71d4dce93044c86b261aa327f3b15' => 
    array (
      0 => 'D:\\OSPanel\\home\\ar-time.loc\\templates\\navbar.tpl',
      1 => 1729469397,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67159bd58893b2_25349704 (Smarty_Internal_Template $_smarty_tpl) {
?><link rel="stylesheet" href="../css/navbar.css">

<div class="top-bar">
    <div class="container">
        <div class="content">
            <div class="title-box">
                <img class="titleIcon" src="../img/website-icons/ar-time-circle.png" alt="website_logo">
                <p class="titleHeader">AR-TIME.COM</p>
            </div>
            <div class="watch-box">
                <div class="watch" id="watch"></div>
            </div>
            <div class="social-media-box">
                <div class="linkLogo">
                    <a target="_blank" href="https://mail.google.com/mail/u/0/?tab=rm&ogbl#inbox?compose=new"><img class="svgIcon" src="../img/svg/envelope.svg" alt="discord_logo"></a>
                </div>
                <div class="linkLogo">
                    <a target="_blank" href="https://www.instagram.com/ar_time_arhs/"><img class="svgIcon" src="../img/svg/instagram.svg" alt="instagram_logo"></a>
                </div>
                <div class="linkLogo">
                    <a target="_blank" href="https://discord.gg/3aX2WpMrtC"><img class="svgIcon" src="../img/svg/discord.svg" alt="discord_logo"></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo '<script'; ?>
 src="../js/navbar.js"><?php echo '</script'; ?>
><?php }
}
