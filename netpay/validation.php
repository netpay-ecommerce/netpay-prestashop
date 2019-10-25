<?php
/**
 * netpay.mx
 *
 * @author    NetPay <ecommerce@netpay.com.mx>
 * @copyright 2019 NetPay
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/netpay.php');

$context = Context::getContext();
$cart = $context->cart;
$netpay = Module::getInstanceByName('netpay');

if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$netpay->active) {
    Tools::redirect('index.php?controller=order&step=1');
}

// Check that this payment option is still available in case the customer changed his address just 
// before the end of the checkout process
$authorized = false;
foreach (Module::getPaymentModules() as $module) {
    if ($module['name'] == 'netpay') {
        $authorized = true;
        break;
    }
}

if (!$authorized) {
    die($netpay->getTranslator()->trans('This payment method is not available.', array(), 'Modules.NetPay.Shop'));
}

$customer = new Customer((int) $cart->id_customer);
if (!Validate::isLoadedObject($customer)) {
    Tools::redirect('index.php?controller=order&step=1');
}

if (Tools::getIsset('create-pending-order')) {
    createPendingOrder();
    exit("order created");
}

if (Tools::getIsset('update-order')) {
    updateOrder(Tools::getValue('update-order'));
    exit("order updated");
}


$urlBase = 'index.php?controller=order-confirmation&id_cart=';
$arguments = $cart->id.'&id_module='.$netpay->id.'&id_order='.$netpay->currentOrder.'&key='.$customer->secure_key;
Tools::redirect($urlBase.$arguments);


function createPendingOrder()
{
    $context = Context::getContext();
    $netpay = new NetPay();
    $netpay->validateOrder(
        (int)$context->cart->id,
        (int)Configuration::get('NETPAY_ORDER_PENDING'),
        (float)$context->cart->getOrderTotal(),
        $netpay->displayName,
        null,
        array(),
        null,
        false,
        $context->cart->secure_key
    );
}

function updateOrder($cartID)
{
    $orderObject = new Order();
    $Order = $orderObject->getByCartId($cartID);
    $Order->setCurrentState((int)Configuration::get('NETPAY_ORDER_PROCESSING'));
}
