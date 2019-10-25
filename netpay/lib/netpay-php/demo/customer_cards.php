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

    $data = array(
        'username' => 'ecommerce@netpay.com.mx',
        'storeApiKey' => 'oe1206Pv!VvBEG73F3HVllLd7K_9F2!K'
    );

    $customer_cards = \NetPay\Api\CustomerCards::post($jwt, $data);
    print_r($customer_cards);
} catch (Exception $e) {
    $description = $e->getMessage();
    echo $description;
}
?>