<?php
/* Smarty version 4.5.4, created on 2024-10-21 17:35:19
  from 'D:\OSPanel\home\ar-time.loc\templates\bell-table.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_6716c9174e8519_50140776',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c27644d6da7de55a7b066db4fdaa19ba2210fed1' => 
    array (
      0 => 'D:\\OSPanel\\home\\ar-time.loc\\templates\\bell-table.tpl',
      1 => 1729546518,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6716c9174e8519_50140776 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="table-box">
    <div class="table-frame">
        <table class="content-table">
            <thead>
            <tr>
                <th>Description / Period</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Length</th>
            </tr>
            </thead>
            <tbody>
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['tableData']->value, 'element');
$_smarty_tpl->tpl_vars['element']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['element']->value) {
$_smarty_tpl->tpl_vars['element']->do_else = false;
?>
                <tr class="table-row">
                    <td><?php echo $_smarty_tpl->tpl_vars['element']->value['name'];?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['element']->value['start_time'];?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['element']->value['end_time'];?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['element']->value['length'];?>
</td>
                </tr>
            <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            </tbody>
        </table>
    </div>
</div><?php }
}
