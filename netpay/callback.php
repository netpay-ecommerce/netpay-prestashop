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

require_once(dirname(__FILE__)  . '/lib/netpay-php/init.php');


class NeypaylValidationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
    }

    function move_to($id_cart, $id_module, $id_order, $key)
    {
        $url = Context::getContext()->link->getPageLink(
            'order-confirmation',
            true,
            null,
            array(
                'id_cart' => $id_cart,
                'id_module' => $id_module,
                'id_order' => $id_order,
                'key' => $key
            )
        );
        /* Ajax redirection Order Confirmation */
        $chargeResult = array(
            'code' => '1',
            'url' => $url
        );

        echo Tools::jsonEncode($chargeResult);
        exit;
    }
}

$config = getConfigVars();

// Properties username and password get their values from the database.
if (isset($config['sandbox'])) {
    $sandbox = $config['sandbox'];
}

$mode = $sandbox ? 'SANDBOX_' : '';

if (isset($config[$mode . 'USERNAME'])) {
    $username = $config[$mode  . 'USERNAME'];
}
if (isset($config[$mode . 'PASSWORD'])) {
    $password = $config[$mode . 'PASSWORD'];
}
if (isset($config[$mode  . 'STOREIDACQ'])) {
    $storeIdAcq = $config[$mode . 'STOREIDACQ'];
}
if (isset($config[$mode . 'STOREAPIKEY'])) {
    $storeApiKey = $config[$mode . 'STOREAPIKEY'];
}
if (isset($config[$mode . 'MID'])) {
    $mid = $config[$mode . 'MID'];
}
if (isset($config[$mode . 'TRANSTYPE'])) {
    $transType = $config[$mode . 'TRANSTYPE'];
}
if (isset($config['NETPAY_IPN'])) {
    $ipn['webhook'] = $config['NETPAY_IPN'];
}

$jwt = login($sandbox, $username, $password);


$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data['event'] == 'cep.paid' && $data['type'] == 'cep') {
    $transactionTokenId = $data['data']['transactionId'];
    $order_id = get_orderid($transactionTokenId);
    $card_id = get_cartid($transactionTokenId);

    $status = \NetPay\Api\Transaction::get($sandbox, $jwt, $transactionTokenId, $storeIdAcq);
    if($status['result']['transaction']['status'] == 'DONE') {
        updateOrder((int) $card_id, 'NETPAY_ORDER_PROCESSING');
    }
} else {
    $context = Context::getContext();
    $cart = $context->cart;
    $netpay = Module::getInstanceByName('netpay');

    if (
        $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0
        || !$netpay->active
    ) {
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

    $context = Context::getContext();
    $cart = $context->cart;
    $orderObject = new Order();
    $orderID = $orderObject->getOrderByCartId((int) $cart->id);
    unset($orderObject);

    $orderObject = new Order($orderID);
    $address_delivery = new Address($cart->id_address_delivery);
    $address_invoice = new Address($cart->id_address_invoice);

    $state_invoice = getIsoCodeStateById($address_invoice->id_state);
    $state_delivery = getIsoCodeStateById($address_delivery->id_state);

    $customer_fields = Context::getContext()->customer->getFields();

    $billing = set_billing_params($address_invoice, $customer_fields, $state_invoice);
    $carrier = new Carrier($cart->id_carrier);
    $shipping = set_shipping_params($address_delivery, $carrier, $state_delivery);

    $products = Context::getContext()->cart->getProducts();
    foreach ($products as $product) {
        $id_product = (int) $product['id_product'];
        $cart_quantity = $product['cart_quantity'];
        $name = $product['name'];
        $price = $product['price'];

        $itemList[] = array(
            'product_id' => $id_product,
            'sku' => $id_product . ' - ' . $name,
            'price' => $price,
            'name' => $name,
            'qty' => $cart_quantity,
            'code' => $name,
        );

        $input['sku'] = $name . '#' . $id_product;
        $input['name'] = $name;
        $category = new Category($product['id_category_default'], Context::getContext()->language->id);
        if (!empty($category->name)) {
            $input['category'] = $category->name;
        } else {
            $input['category'] = 'Sin categoria';
        }
    }

    $input['storeIdAcq'] = $storeIdAcq;
    $input['phoneNumber'] = $address_invoice->phone;
    $input['method_of_delivery'] = $carrier->name;
    $input['firstName'] = $address_invoice->firstname;
    $input['lastName'] = $address_invoice->lastname;
    $input['site'] = Configuration::get('PS_SHOP_NAME');
    $input['fecha_registro'] = date_joined($customer_fields['email']);
    $input['client_id'] = $customer_fields['id_customer'];

    $amount_paid = get_sum_paid($customer_fields['id_customer'], (int) Configuration::get('NETPAY_ORDER_SUCCESS'));
    $count_paid = get_count_paid($customer_fields['id_customer'], (int) Configuration::get('NETPAY_ORDER_SUCCESS'));
    $input['avg_value'] = ($amount_paid > 0) ? number_format($amount_paid / $count_paid, 2, '.', '') : 0;
    $input['count_orders_completed'] = $count_paid;
    $input['total_sum'] = number_format($amount_paid, 2, '.', '');

    $mdds = get_mdds($mid, $input);

    $grandTotalAmount = number_format((float) ($cart->getOrderTotal(true, Cart::BOTH)), 2, '.', '');

    $currency_order = new Currency($cart->id_currency);
    $currency_name = $currency_order->name;
    $currency_iso_code = $currency_order->iso_code;


    $method = filter_var($_GET['method'], FILTER_SANITIZE_STRING);
    switch ($method) {
        case 'cash':
            make_payment_in_cash(
                $sandbox,
                $jwt,
                $cart,
                $billing,
                $shipping,
                $itemList,
                $grandTotalAmount,
                $currency_iso_code,
                $mdds,
                $ipn,
                $storeIdAcq,
                $netpay,
                $customer
            );
            break;
        case 'card':
        default:
            make_payment_in_card(
                $sandbox,
                $jwt,
                $netpay,
                $customer_fields,
                $storeApiKey,
                $cart,
                $billing,
                $shipping,
                $itemList,
                $grandTotalAmount,
                $currency_iso_code,
                $mdds,
                $transType,
                $customer
            );
    }
}

function make_payment_in_cash(
    $sandbox,
    $jwt,
    $cart,
    $billing,
    $shipping,
    $itemList,
    $grandTotalAmount,
    $currency_iso_code,
    $mdds,
    $ipn,
    $storeIdAcq,
    $netpay,
    $customer
) {
    NetPay\Api\Webhook::post($sandbox, $jwt, $ipn);

    $createOrder = createPendingOrder();

    $orderObject = new Order();
    $orderID = $orderObject->getOrderByCartId((int) $cart->id);

    $input = array(
        "store_customer" => $storeIdAcq,
        "promotion" => '000000',
        "order_id" => $orderID,
        "bill" => \NetPay\Billing::format($billing),
        "ship" => \NetPay\Shipping::format($shipping),
        "itemList" => \NetPay\ItemList::format($itemList),
        "total" => $grandTotalAmount,
        "currency" => $currency_iso_code,
    );
    $transType = 'Auth';

    $checkout = NetPay\Api\Checkout::post($sandbox, $jwt, $input, $mdds, $transType, '');
    if ($checkout['result']['response']['status'] == 'OK') {
        $checkoutTokenId = $checkout['result']['response']['checkoutTokenId'];
        $reference = NetPay\Api\Reference::post($sandbox, null, $checkoutTokenId);
        if (!empty($reference['result']['response']['transactionId'])) {
            $transactionTokenId = $reference['result']['response']['transactionId'];
            $ref = $reference['result']['response']['reference'];
            Db::getInstance()->Execute('
			    INSERT IGNORE INTO ' . _DB_PREFIX_ . 'netpay_order (id_order, id_cart, transaction_token_id, preauth, is_reference, register_date, reference)
                VALUES (\'' . pSQL($orderID) . '\','.(int) $cart->id.' , "' . $transactionTokenId . '", 0, 1, now(), "'.$ref.'"); ');

            /*$ref = $reference['result']['response']['reference'];
            echo Tools::jsonEncode(
                array(
                    'result' => 'success',
                    'reference' => $ref
                )
            );
            exit;*/
            $urlBase = 'confirmacion-pedido?id_cart=';
            $arguments = $cart->id . '&id_module=' . $netpay->id . '&id_order=' . $orderID . '&key=' . $customer->secure_key;

            echo Tools::jsonEncode(
                $chargeResult = array(
                    'result' => 'success',
                    'url' => $urlBase . $arguments
                )
            );
            exit;
        }
    }

    echo Tools::jsonEncode(
        array(
            'result' => 'error',
            'error' => 'Error al crear la referencia.'
        )
    );
    exit;
}

function make_payment_in_card(
    $sandbox,
    $jwt,
    $netpay,
    $customer_fields,
    $storeApiKey,
    $cart,
    $billing,
    $shipping,
    $itemList,
    $grandTotalAmount,
    $currency_iso_code,
    $mdds,
    $transType,
    $customer
) {
    $customerCard = set_customer_card();
    $params = set_card_params($customer_fields, $storeApiKey, $customerCard);
    $tokenCard = createTokenCard($sandbox, $jwt, $params);
    if (!empty($tokenCard['result']['responseCode']) && $tokenCard['result']['responseCode'] == '200') {
        $publicToken = $tokenCard['result']['response']['customerToken']['token']['publicToken'];

        $createOrder = createPendingOrder();

        $orderObject = new Order();
        $orderID = $orderObject->getOrderByCartId((int) $cart->id);

        $fields = set_params($storeApiKey, $customerCard, $orderID, $publicToken, $billing, $shipping, $itemList, $grandTotalAmount, $currency_iso_code);

        $riskManager = riskManager($sandbox, $jwt, $fields, $mdds);
        if ($riskManager['result']['status'] == 'CHARGEABLE') {
            $transactionType = $transType; //Auth, PreAuth, PostAuth
            $transactionTokenId = $riskManager['result']['transactionTokenId'];

            $charge = \NetPay\Api\Charge::post($sandbox, $jwt, $transactionTokenId, '', $transactionType);
            if ($charge['result']['response']['responseCode'] == '00') {

                if ($transType === 'Auth') {
                    updateOrder((int) $cart->id, 'NETPAY_ORDER_PROCESSING');

                    Db::getInstance()->Execute('
			    INSERT IGNORE INTO ' . _DB_PREFIX_ . 'netpay_order (id_order, id_cart, transaction_token_id, preauth, is_reference,  register_date)
				VALUES (\'' . pSQL($orderID) . '\', '.(int) $cart->id.', "' . $transactionTokenId . '", 0, 0, now());');
                } else {
                    updateOrder((int) $cart->id, 'NETPAY_ORDER_PREAUTH');

                    Db::getInstance()->Execute('
			    INSERT IGNORE INTO ' . _DB_PREFIX_ . 'netpay_order (id_order, id_cart, transaction_token_id, preauth, is_reference, register_date)
				VALUES (\'' . pSQL($orderID) . '\', '.(int) $cart->id.', "' . $transactionTokenId . '", 1, 0, now());');
                }

                $urlBase = 'confirmacion-pedido?id_cart=';
                $arguments = $cart->id . '&id_module=' . $netpay->id . '&id_order=' . $orderID . '&key=' . $customer->secure_key;

                echo Tools::jsonEncode(
                    $chargeResult = array(
                        'result' => 'success',
                        'url' => $urlBase . $arguments
                    )
                );
                exit;
            }
        }

        /*$urlBase = 'order-confirmation?id_cart=';
    $arguments = $cart->id.'&id_module='.$netpay->id.'&id_order='.$orderID.'&key='.$customer->secure_key;
    Tools::redirect($urlBase.$arguments);*/

        echo Tools::jsonEncode(
            $chargeResult = array(
                'result' => 'error',
                'error' => 'Error al procesar el pago.'
            )
        );
        exit;
    }
}

function set_params(
    $storeApiKey,
    $customerCard,
    $orderID,
    $publicToken,
    $billing,
    $shipping,
    $itemList,
    $grandTotalAmount,
    $currency_iso_code
) {
    return array(
        "storeApiKey" => $storeApiKey,
        "promotion" => $customerCard['msi'],
        "order_id" => $orderID,
        "deviceFingerprintID" => $customerCard['deviceFingerprintID'],
        "cardToken" => $publicToken,
        "bill" => \NetPay\Billing::format($billing),
        "ship" => \NetPay\Shipping::format($shipping),
        "itemList" => \NetPay\ItemList::format($itemList),
        "total" => number_format($grandTotalAmount, 2, '.', ''),
        "currency" => $currency_iso_code,
    );
}

function set_customer_card()
{
    $cardNumber = filter_var($_POST['cardNumber'], FILTER_SANITIZE_NUMBER_INT);
    $cardType = filter_var($_POST['cardType'], FILTER_SANITIZE_NUMBER_INT);
    $expirationMonth = filter_var($_POST['expirationMonth'], FILTER_SANITIZE_NUMBER_INT);
    $expirationYear = filter_var($_POST['expirationYear'], FILTER_SANITIZE_NUMBER_INT);
    $cvv = filter_var($_POST['cvv'], FILTER_SANITIZE_NUMBER_INT);
    $cardHolderName = filter_var($_POST['cardHolderName'], FILTER_SANITIZE_STRING);
    $deviceFingerprintID = filter_var($_POST['deviceFingerprintID'], FILTER_SANITIZE_STRING);
    $msi = filter_var($_POST['msi'], FILTER_SANITIZE_STRING);

    $customerCard['cardNumber'] = $cardNumber;
    $customerCard['expirationMonth'] = $expirationMonth;
    $customerCard['expirationYear'] = $expirationYear;
    $customerCard['cvv'] = $cvv;
    $customerCard['cardType'] = $cardType;
    $customerCard['cardHolderName'] = $cardHolderName;
    $customerCard['deviceFingerprintID'] = $deviceFingerprintID;
    $customerCard['msi'] = $msi;

    return $customerCard;
}

function set_card_params($customer_fields, $storeApiKey, $customerCard)
{
    return array(
        'username' => $customer_fields['email'],
        'storeApiKey' => $storeApiKey,
        'customerCard' => array(
            'cardNumber' => $customerCard['cardNumber'],
            'expirationMonth' => $customerCard['expirationMonth'],
            'expirationYear' => $customerCard['expirationYear'],
            'cvv' => $customerCard['cvv'],
            'cardType' => $customerCard['cardType'],
            'cardHolderName' => $customerCard['cardHolderName']
        )
    );
}

function set_billing_params($address_invoice, $customer_fields, $state_invoice)
{
    return array(
        'billing_city' => $address_invoice->city,
        'billing_country' => $address_invoice->country,
        'billing_first_name' => $address_invoice->firstname,
        'billing_last_name' => $address_invoice->lastname,
        'billing_email' => $customer_fields['email'],
        'billing_phone' => $address_invoice->phone,
        'billing_postcode' => $address_invoice->postcode,
        'billing_state' => $state_invoice,
        'billing_address_1' => $address_invoice->address1,
        'billing_address_2' => $address_invoice->address2,
        'customer_ip_address' => $_SERVER['REMOTE_ADDR'],
    );
}

function set_shipping_params($address_delivery, $carrier, $state_delivery)
{
    return array( //optional, for virtual products it must be empty
        'shipping_city' => $address_delivery->city,
        'shipping_country' => $address_delivery->country,
        'shipping_first_name' => $address_delivery->firstname,
        'shipping_last_name' => $address_delivery->lastname,
        'shipping_phone' => $address_delivery->phone,
        'shipping_postcode' => $address_delivery->postcode,
        'shipping_state' => $state_delivery,
        'shipping_address_1' => $address_delivery->address1,
        'shipping_address_2' => $address_delivery->address2,
        'shipping_method' => $carrier->name,
    );
}

function getConfigVars()
{
    return                         Configuration::getMultiple(array(
        'USERNAME',
        'PASSWORD',
        'STOREIDACQ',
        'STOREAPIKEY',
        'ORGID',
        'MSI_000303',
        'MSI_000603',
        'MSI_000903',
        'MSI_001203',
        'MSI_001803',
        'MID',
        'TRANSTYPE',

        'SANDBOX_USERNAME',
        'SANDBOX_PASSWORD',
        'SANDBOX_STOREIDACQ',
        'SANDBOX_STOREAPIKEY',
        'SANDBOX_ORGID',
        'SANDBOX_MSI_000303',
        'SANDBOX_MSI_000603',
        'SANDBOX_MSI_000903',
        'SANDBOX_MSI_001203',
        'SANDBOX_MSI_001803',
        'SANDBOX_MID',
        'SANDBOX_TRANSTYPE',

        'NETPAY_IPN',

        'sandbox'
    ));
}

function login($sandbox, $username, $password)
{
    $data = array(
        'userName' => $username,
        'password' => $password,
    );
    $login = \NetPay\Api\Login::post($sandbox, $data);
    return $login['result']['token'];
}

function createTokenCard($sandbox, $jwt, $params)
{
    return \NetPay\Api\CreateTokenCard::post($sandbox, $jwt, $params);
}

function riskManager($sandbox, $jwt, $fields, $mdds)
{
    return \NetPay\Api\RiskManager::post($sandbox, $jwt, $fields, $mdds);
}

function getIsoCodeStateById($id_state)
{
    $result = Db::getInstance()->getRow('
        SELECT s.`iso_code` AS iso_code
        FROM `' . _DB_PREFIX_ . 'state` s
        WHERE s.`id_state` = ' . (int) $id_state);
    return isset($result['iso_code']) ? $result['iso_code'] : false;
}

function createPendingOrder()
{
    try {
        $context = Context::getContext();
        $netpay = new NetPay();
        $netpay->validateOrder(
            (int) $context->cart->id,
            (int) Configuration::get('NETPAY_ORDER_PENDING'),
            (float) $context->cart->getOrderTotal(),
            $netpay->displayName,
            null,
            array(),
            null,
            false,
            $context->cart->secure_key
        );
    } catch (PrestaShopException $e) {
        $this->_error[] = (string) $e->getMessage();
        return false;
    }
    return true;
}

function updateOrder($cartID, $status)
{
    $orderObject = new Order();
    $Order = $orderObject->getByCartId($cartID);
    $Order->setCurrentState((int) Configuration::get($status));
}

function get_mdds($mid, $input)
{
    $mdds = array();
    switch ($mid) {
        case 'netpaymx_retail':
            $mdds = \NetPay\MID\Retail::get_mdds($input);
            break;
        case 'netpaymx_donativos':
            $mdds = \NetPay\MID\Donaciones::get_mdds($input);
            break;
        case 'netpaymx_schools':
            $mdds = \NetPay\MID\Escuelas::get_mdds($input);
            break;
        case 'netpaymx_tickets':
            $mdds = \NetPay\MID\Tickets::get_mdds($input);
            break;
        case 'netpaymx_food':
            $mdds = \NetPay\MID\Restaurant::get_mdds($input);
            break;
        case 'netpaymx_services':
            $mdds = \NetPay\MID\Generales::get_mdds($input);
            break;
        default:
            $mdds = \NetPay\MID\Retail::get_mdds($input);
    }

    return $mdds;
}

function date_joined($email)
{
    $date_add = Db::getInstance()->getRow('SELECT date_add FROM `' . _DB_PREFIX_ . 'customer` WHERE `email` = "' . $email . '" ');
    return \NetPay\Functions::days_to_today($date_add['date_add']);
}

function get_total_amount_paid($id_customer, $current_state)
{
    $response = Db::getInstance()->getRow('SELECT total_paid as total_paid FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_customer` = ' . $id_customer . ' and current_state = ' . $current_state . ' ');
    return $response['total_paid'];
}

function get_count_paid($id_customer, $current_state)
{
    $response = Db::getInstance()->getRow('SELECT count(total_paid) as total_paid FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_customer` = ' . $id_customer . ' and current_state = ' . $current_state . ' ');
    return $response['total_paid'];
}

function get_sum_paid($id_customer, $current_state)
{
    $response = Db::getInstance()->getRow('SELECT sum(total_paid) as total_paid FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_customer` = ' . $id_customer . ' and current_state = ' . $current_state . ' ');
    return $response['total_paid'];
}

function get_orderid($transactionTokenId)
{
    $response = Db::getInstance()->getRow('SELECT id_orden as id_orden FROM `' . _DB_PREFIX_ . 'netpay_order` WHERE `transaction_token_id` = "' . $transactionTokenId . '" ;');
    return $response['id_orden'];
}

function get_cartid($transactionTokenId)
{
    $response = Db::getInstance()->getRow('SELECT id_cart as id_cart FROM `' . _DB_PREFIX_ . 'netpay_order` WHERE `transaction_token_id` = "' . $transactionTokenId . '" ;');
    return $response['id_cart'];
}
