<?php
/* Smarty version 4.5.4, created on 2024-10-22 20:41:19
  from 'D:\OSPanel\home\ar-time.loc\templates\index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_6718462f0517f7_56681079',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '38c0b6c13ce14697c82f3e73a1f5a46295e2464f' => 
    array (
      0 => 'D:\\OSPanel\\home\\ar-time.loc\\templates\\index.tpl',
      1 => 1729644077,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:bell-table.tpl' => 1,
    'file:no-table.tpl' => 1,
    'file:partnerships.tpl' => 1,
  ),
),false)) {
function content_6718462f0517f7_56681079 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../libs/bootstrap/css/bootstrap.css">

    <link rel="stylesheet" href="../css/universal/normalize.css">
    <link rel="stylesheet" href="../css/universal/fonts.css">

    <link rel="stylesheet" href="../css/universal/navbar.css">
    <link rel="stylesheet" href="../css/countdown-box.css">
    <link rel="stylesheet" href="../css/bell-table.css">
    <link rel="stylesheet" href="../css/partnerships.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/website-icons/ar-time-bar-icon.png">

    <link rel="stylesheet" href="../css/index.css">
    <title>ar-time</title>
    <?php echo '<script'; ?>
>
      let data = JSON.parse('<?php echo $_smarty_tpl->tpl_vars['schedule_JSON']->value;?>
');
    <?php echo '</script'; ?>
>
</head>
<body>
<?php $_smarty_tpl->_subTemplateRender(((string)$_smarty_tpl->tpl_vars['navbar']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
<div class="container">
    <div class="row">
        <div class="col-md-5">
            <?php $_smarty_tpl->_subTemplateRender(((string)$_smarty_tpl->tpl_vars['countdown']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?>
        </div>
        <div class="col-md-7">
            <?php if (is_array($_smarty_tpl->tpl_vars['table_data']->value)) {?>
                <?php $_smarty_tpl->_subTemplateRender("file:bell-table.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('tableData'=>$_smarty_tpl->tpl_vars['table_data']->value), 0, false);
?>
            <?php } else { ?>
                <?php $_smarty_tpl->_subTemplateRender("file:no-table.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>
            <?php }?>
        </div>
    </div>
</div>
<?php $_smarty_tpl->_subTemplateRender("file:partnerships.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php echo '<script'; ?>
 src="../libs/bootstrap/js/bootstrap.bundle.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="../js/countdown-box.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="../js/navbar.js"><?php echo '</script'; ?>
>
</body>
</html><?php }
}
