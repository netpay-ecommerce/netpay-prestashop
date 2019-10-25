<?php
/* Smarty version 3.1.33, created on 2019-06-06 19:25:41
  from '/opt/lampp/htdocs/netpay-php/demo/form/templates/payment_form.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5cf94c9593ff97_72846350',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '0973d381d2521d6d7cc49353964f6b1293b0cb5f' => 
    array (
      0 => '/opt/lampp/htdocs/netpay-php/demo/form/templates/payment_form.tpl',
      1 => 1559841935,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:header.tpl' => 1,
    'file:footer.tpl' => 1,
  ),
),false)) {
function content_5cf94c9593ff97_72846350 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->smarty->ext->configLoad->_loadConfigFile($_smarty_tpl, "test.conf", "setup", 0);
?>

<?php $_smarty_tpl->_subTemplateRender("file:header.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('title'=>"NetPay - Pagos con tarjetas"), 0, false);
?>

<div class="container-fluid">
        <div class="creditCardForm">
            <div class="payment">
                <form action="./callback.php" method="post">
                    <div class="form-group" id="card-number-field">
                        <label for="cardNumber">Número de la tarjeta</label>
                        <input type="text" class="form-control" id="cardNumber" name='cardNumber'>
                    </div>
                    <div class="form-group" id="expiration-date">
                        <label>Fecha de expiración</label>
                        <select name='expirationMonth'>
                            <option value="01">Enero</option>
                            <option value="02">Febrero </option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                        <select name='expirationYear'>
                            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['expiration_years']->value, 'expiration_year');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['expiration_year']->value) {
?>
                                <option value="<?php echo $_smarty_tpl->tpl_vars['expiration_year']->value['year'];?>
"> <?php echo $_smarty_tpl->tpl_vars['expiration_year']->value['description'];?>
</option>
                            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                        </select>
                    </div>
                    <div class="form-group CVV">
                        <label for="cvv">CVV</label>
                        <input type="text" class="form-control" id="cvv" name='cvv'>
                    </div>
                    <div class="form-group owner">
                        <label for="owner">Nombre del titular</label>
                        <input type="text" class="form-control" id="owner" name='cardHolderName'>
                    </div>
                    <div class="form-group card_type" id="card-type">
                        <label>Tipo</label>
                        <select name='cardType'>
                            <option value="001">Visa</option>
                            <option value="002">MasterCard </option>
                            <option value="003">American Express</option>
                        </select>

                        <img src="images/visa.jpg" id="visa" height="40" width="40">
                        <img src="images/mastercard.jpg" id="mastercard" height="40" width="40">
                        <img src="images/amex.jpg" id="amex" height="40" width="40">
                    </div>
                    <div class="form-group" id="pay-now">
                        <button type="submit" class="btn btn-default">Pagar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php $_smarty_tpl->_subTemplateRender("file:footer.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
