<?php
require_once ('../init.php');

use \NetPay\Config;

try {
    $data = array(
        'userName' => Config::USER_NAME,
        'password' => Config::PASS,
    );

    $login = \NetPay\Api\Login::post($data);
    $jwt = $login['result']['token'];

    if ($jwt === false) {
        print_r($login);
        return false;
    }

    $mdds[] = array(
        "id" => 0,
        "value" => 'dummy',
    );
    $shipping_method = 'flatrate_flatrate'; //flatrate_flatrate, free_shipping, local_pickup
    $billing = array(
        'billing_city' => 'Pánuco',
        'billing_country' => 'MX',
        'billing_first_name' => 'Jhon',
        'billing_last_name' => 'Doe',
        'billing_email' => 'accept@netpay.com.mx',
        'billing_phone' => '8461234567',
        'billing_postcode' => '93994',
        'billing_state' => 'Veracruz',
        'billing_address_1' => 'Zona Centro 123',
        'billing_address_2' => 'Col Centro',
        'customer_ip_address' => '127.0.0.1',
    );
    $shipping = array(//optional, for virtual products it must be empty
        'shipping_city' => '',
        'shipping_country' => '',
        'shipping_first_name' => '',
        'shipping_last_name' => '',
        'shipping_phone' => '',
        'shipping_postcode' => '',
        'shipping_state' => '',
        'shipping_address_1' => '',
        'shipping_address_2' => '',
        'shipping_method' => '',
    );
    $itemList[] = array(
        'product_id' => '1',
        'sku' => 'CELAZU128GB',
        'price' => '7500.00',
        'name' => 'Celular android color azul 128 GB',
        'qty' => '1',
        'code' => 'CEL128',
    );

    $fields = array(
        "store_customer" => Config::STORE_ID_ACQ,
        "promotion" => '000000',
        "order_id" => '12345',
        "bill" => \NetPay\Billing::format($billing),
        "ship" => \NetPay\Shipping::format($shipping),
        "itemList" => \NetPay\ItemList::format($itemList),
        "total" => '7500.00',
        "currency" => 'MXN',
    );
    $transType = 'Auth'; //Auth, PreAuth
    $cardType = '001'; //001, 002, 003 //optional
    $result = \NetPay\Api\Checkout::post($jwt, $fields, $mdds, $transType, $cardType );
    print_r($result);
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
?>